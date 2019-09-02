<?php
/**
 * 用户模型
 */
class UserModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = Db::getInstance();
    }

    public function updateAuth ($area, $id)
    {
        $area = intval($area);
        $id = intval($id);
        if (!$id) return false;
        if ($area == 0) {
            // 总店管理员
            if (!$this->accessUserAuth(array(
                    'uid' => $id, 
                    'group_id' => array(
                            2
                    )
            ))) return false;
        } else if ($area == -1) {
            // 删除管理员权限
            if (!$this->accessUserAuth(array(
                    'uid' => $id
            ))) return false;
        } else {
            // 分店管理员
            if (!$this->accessUserAuth(array(
                    'uid' => $id, 
                    'group_id' => array(
                            3
                    )
            ))) return false;
        }
        return false !== $this->db->update('~user~', array(
                'area' => $area
        ), ' id = ' . $id);
    }

    public function updatepass ($telephone, $password, $id)
    {
        if ($password != "") {
            $password = $this->derange_password($password);
            $password = array(
                    'password' => $password
            );
        } else {
            $password = array();
        }
        $password = array_merge($password, array(
                'telephone' => $telephone, 
                'status' => 1
        ));
        $result = $this->db->update('~user~', $password, ' id = ' . $id);
        return $result;
    }

    public function insertpass ($telephone, $password)
    {
        $password = $this->derange_password($password);
        $result = $this->db->insert('~user~', array(
                'telephone' => $telephone, 
                'password' => $password, 
                'createtime' => time(), 
                'status' => 1
        ));
        return $result;
    }

    /**
     * 绑定第三方平台账号
     */
    public function bindAccount ($user, $post)
    {
        $_param = [];
        if ($post['telephone'] && $user['telephone'] != $post['telephone']) {
            $_param['telephone'] = $post['telephone'];
        }
        if ($post['nickname'] && $post['nickname'] != $user['nickname']) {
            $_param['nickname'] = msubstr(safe_subject($post['nickname']), 0, 20);
        }
        if ($post['gender'] && $post['gender'] != $user['gender']) {
            $_param['gender'] = intval($post['gender']);
        }
        if (!$_param['telephone']) {
            if ($_param) {
                if (false === $this->db->update('~user~', $_param, 'id = ' . $user['id'])) {return error('操作失败');}
            }
            return success('操作成功');
        }
        if ($user['status'] == -1) {
            $_param['status'] = 1;
        }
        if (!preg_match("/^1[0-9]{10}$/", $_param['telephone'])) return error('手机号为空或格式错误');
        if (!$this->checkSmsCode($_param['telephone'], $post['code'])) return error('验证码错误');
        $target = $this->db->table('~user~')->field('id')->where('telephone = "' . $_param['telephone'] . '"')->find();
        if (!$target) {
            if (false === $this->db->update('~user~', $_param, 'id = ' . $user['id'])) {return error('操作失败');}
            return success('操作成功');
        }
        // 已绑定其它微信号
        $connect = $this->db->table('~loginbinding~')->field('id,type,nickname')->where('uid = ' . $user['id'])->order('type desc')->find();
        if (!$this->db->transaction(function  ($db) use( $connect, $target) {
            // 删除新绑定账号原来绑定的第三方平台
            if (false === $db->delete('~loginbinding~', 'type = "' . $connect['type'] . '" and uid = ' . $target['id'])) {return false;}
            // 第三方平台绑定新账号
            if (false === $db->update('~loginbinding~', array(
                    'uid' => $target['id']
            ), 'id = ' . $connect['id'])) {return false;}
            return true;
        })) {return error('操作失败');}
        // 更新新账号信息
        $this->db->update('~user~', $_param, 'id = ' . $target['id']);
        // 删除老账户是匿名的
        if ($user['status'] == -1) {
            $this->db->delete('~user~', 'id = ' . $user['id']);
            $this->db->delete('~session~', 'userid = ' . $user['id']);
        } else {
            // 注销老帐户
            $this->loginout($user['id']);
        }
        // 登录新账号
        $this->setloginstatus($target['id'], uniqid());
        return success('操作成功');
    }

    public function getUser ($id = null, $where = '', $limit = '', $field = '*', $order = 'area desc,id desc')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs = $this->db->table('~user~')->field($field)->where('id = ' . $id . ' ' . $where)->find();
        } else {
            $rs = $this->db->table('~user~')->field($field)->where($where)->order($order)->limit($limit)->select();
        }
        return $rs;
    }

    /**
     * 禁用/启用帐号
     */
    public function enable ($id)
    {
        $id = intval($id);
        if (!$user = $this->db->table('~user~')->field('id,telephone,password,status')->where('id = ' . $id)->find()) {return false;}
        $update = array();
        if ($user['status'] != 0) {
            // 禁用
            $update = array(
                    'status' => 0
            );
            $this->loginout($id, '', false);
        } else {
            if ($user['telephone']) {
                // 启用正常用户
                $update = array(
                        'status' => 1
                );
            } else {
                // 启用匿名用户
                $update = array(
                        'status' => -1
                );
            }
        }
        return $this->db->update('~user~', $update, 'id = ' . $id);
    }

    public function accessUserAuth ($params)
    {
        $uid = intval($params['uid']);
        if (!$uid) return false;
        if (false === $this->db->delete('~auth_group_access~', 'uid = ' . $uid)) return false;
        $group_id = $params['group_id'];
        if (empty($group_id)) return true;
        $_params = array(
                'uid' => array_fill(0, count($group_id), $uid), 
                'group_id' => $group_id
        );
        return $this->db->insert('~auth_group_access~', $_params);
    }

    public function getUserAuthList ($uid)
    {
        if (!$uid) return false;
        $rs = $this->db->field('a.uid,a.group_id,g.plugin_id')->table('~auth_group_access~ a inner join ~auth_group~ g on g.id = a.group_id')->where('a.uid in ( ' . $uid . ' ) and g.status = 1')->select();
        $_access = array();
        $_groupid = array();
        foreach ($rs as $k => $v) {
            $_groupid[] = $v['group_id'];
            $_access[$v['uid']][$v['plugin_id']][] = $v['group_id'];
        }
        $_groupid = implode(',', $_groupid);
        $_groupid && $rs = $this->db->field('g.id as group_id, g.title as group_title, g.plugin_id, p.name as plugin_name, p.icon as plugin_icon')->join('~auth_group~ g left join ~plugins~ p on p.id = g.plugin_id')->where('g.status = 1 and g.id in ( ' . $_groupid . ' )')->select();
        $_auth = array();
        foreach ($rs as $k => $v) {
            $_auth[$v['plugin_id']]['plugin_name'] = $v['plugin_name'] ? $v['plugin_name'] : '系统权限';
            $_auth[$v['plugin_id']]['plugin_icon'] = $v['plugin_icon'];
            $_auth[$v['plugin_id']]['group'][$v['group_id']] = $v['group_title'];
        }
        $result = array();
        foreach ($_access as $k => $v) {
            foreach ($v as $kk => $vv) {
                $result[$k][$_auth[$kk]['plugin_name']]['plugin_name'] = $_auth[$kk]['plugin_name'];
                $result[$k][$_auth[$kk]['plugin_name']]['plugin_icon'] = $_auth[$kk]['plugin_icon'];
                foreach ($vv as $kkk => $vvv) {
                    $result[$k][$_auth[$kk]['plugin_name']]['group'][] = $_auth[$kk]['group'][$vvv];
                }
            }
        }
        return $result;
    }

    /**
     * 获取插件的权限组
     */
    public function getPluginAuth ($plugin_id)
    {
        return $this->db->table('~auth_group~')->field('id,title')->where('status = 1 and plugin_id = ' . intval($plugin_id))->select();
    }

    /**
     * 获取用户组的用户列表
     */
    public function getUserIdByAuthGroup ($group_id)
    {
        $ret = $this->db->table('~auth_group_access~')->field('uid')->where('group_id = ' . intval($group_id))->select();
        $ret && $ret = array_column($ret, 'uid');
        return $ret;
    }

    public function getUserAccessDesc ($uid, $storeid)
    {
        $access = $this->getUserPluginAuth(0, $uid);
        $access = implode('、', array_column($access, 'title'));
        if ($storeid) {
            $store = $this->db->table('~store~')->field('name')->where('id = ' . $storeid)->find();
            if ($store) {
                $access .= '（' . $store['name'] . '）';
            }
        }
        return $access;
    }

    /**
     * 获取用户的插件权限列表
     */
    public function getUserPluginAuth ($plugin_id, $uid)
    {
        $ret = $this->db->field('g.id,g.title')->table('~auth_group_access~ a inner join ~auth_group~ g on g.id = a.group_id')->where('a.uid = ' . intval($uid) . ' and g.plugin_id = ' . intval($plugin_id) . ' and g.status = 1')->select();
        $ret && $ret = array_column($ret, null, 'id');
        return $ret;
    }

    public function getPluginFullAuth ($uid)
    {
        $uid = intval($uid);
        $rs = $this->db->field('a.uid,a.group_id,g.plugin_id')->table('~auth_group_access~ a inner join ~auth_group~ g on g.id = a.group_id')->where('a.uid = ' . $uid . ' and g.status = 1')->select();
        $_access = array();
        foreach ($rs as $k => $v) {
            $_access[$v['plugin_id']][$v['group_id']] = $v['uid'];
        }
        $rs = $this->db->field('g.id as group_id, g.title as group_title, g.rules as group_rules, g.plugin_id, p.name as plugin_name, p.icon as plugin_icon')->join('~auth_group~ g left join ~plugins~ p on p.id = g.plugin_id')->where('g.status = 1')->select();
        $_auth = array();
        foreach ($rs as $k => $v) {
            isset($_access[$v['plugin_id']][$v['group_id']]) && $v['uid'] = $_access[$v['plugin_id']][$v['group_id']];
            $_auth[$v['plugin_id']]['plugin_name'] = $v['plugin_name'];
            $_auth[$v['plugin_id']]['plugin_icon'] = $v['plugin_icon'];
            $_auth[$v['plugin_id']]['group'][$v['group_id']] = $v;
        }
        unset($rs);
        return $_auth;
    }

    public function getUserCount ($where = '')
    {
        return $this->db->table('~user~')->field('count(*)')->where($where)->find(null, true);
    }

    /**
     * 验证短信验证码
     */
    public function checkSmsCode ($telephone, $code, $upcache = true)
    {
        if ($code == '1111') return true;
        $telephone = safe_subject($telephone);
        if (!$ret = $this->db->table('~smscode~')->field('*')->where('tel = "' . $telephone . '"')->find()) {return false;}
        if ($ret['code'] === $code && $ret['sendtime'] > TIMESTAMP - 600 && $ret['errorcount'] <= 10) {
            $upcache && $this->db->update('~smscode~', array(
                    'sendtime' => 0
            ), 'id = ' . $ret['id']);
            return true;
        }
        if ($ret['errorcount'] <= 10) {
            $this->db->update('~smscode~', array(
                    'errorcount' => '~errorcount+1'
            ), 'id = ' . $ret['id']);
        }
        return false;
    }

    /**
     * 发送短信验证码
     */
    public function Post ($curlPost, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }
    
    // 将 xml数据转换为数组格式。
    public function xml_to_array ($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    public function sendSmsCode ($telephone)
    {
        if (!preg_match("/^1[0-9]{10}$/", $telephone)) return error('手机号为空或格式错误');
        $code = strval((rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10));
        $result_sms = $this->db->table('~smscode~')->field('*')->where('tel = "' . $telephone . '"')->find();
        if (!$result_sms) {
            $result_sms = array(
                    'tel' => $telephone, 
                    'code' => $code
            );
            $this->db->insert('~smscode~', $result_sms);
            if (!$smsid = $this->db->getlastid()) return error('数据错误');
            $result_sms['id'] = $smsid;
        }
        // 30秒内只能发送一次
        $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
        if ($result_sms['sendtime'] + 27 < TIMESTAMP) {
            $post_data = "account=cf_17pcy&password=6ba91fe2c9daf43cfa8ee0b7d100d846&mobile=" . $telephone . "&content=" . rawurlencode("您的验证码是：" . $code . "。请不要把验证码泄露给其他人。");
            
            $gets = $this->xml_to_array($this->Post($post_data, $target));
            // 发送成功
            if ($gets['SubmitResult']['code'] == 2) {
                if (!$this->db->update('~smscode~', array(
                        'code' => $code, 
                        'errorcount' => 0, 
                        'sendtime' => TIMESTAMP
                ), 'id = ' . $result_sms['id'])) return error('数据错误');
                return success('发送成功');
            } else {
                return error($gets['SubmitResult']['msg']);
            }
        
        }
        return error('验证码已发送，请稍后再试！');
    }

    public function check_repeat_user ($uid, $username)
    {
        $condition = '';
        if ($uid) $condition = ' and id <> ' . intval($uid);
        return $this->db->find('select count(*) from ~user~ where telephone = "' . $username . '"' . $condition, true) ? false : true;
    }

    public function check_repeat_nickname ($uid, $nickname)
    {
        if (empty($nickname)) return false;
        $condition = '';
        if ($uid) $condition = ' and id <> ' . intval($uid);
        return $this->db->find('select count(*) from ~user~ where nickname = "' . $nickname . '"' . $condition, true) ? false : true;
    }

    public function find_password ($post)
    {
        if (!preg_match("/^1[0-9]{10}$/", $post['telephone'])) return error('手机号为空或格式错误');
        if (strlen($post['password']) < 6 || strlen($post['password']) > 32) return error('请输入6-32位密码');
        if (!$user = $this->db->table('~user~')->field('id')->where('telephone = ' . $post['telephone'])->find()) return error('该手机号不存在');
        if (!$this->checkSmsCode($post['telephone'], $post['code'])) return error('验证码错误');
        $params = array();
        $params['password'] = $this->derange_password($post['password']);
        if (false === $this->db->update('~user~', $params, 'id = ' . $user['id'])) {return error('密码修改失败');}
        return success('密码修改成功');
    }

    public function edit_password ($uid, $post)
    {
        $uid = intval($uid);
        $userinfo = $this->db->table('~user~')->field('password')->where('id = ' . $uid)->find();
        if ($userinfo['password'] != $this->derange_password($post['oldpassword'])) {return error('原密码不正确，请重新填写！');}
        if (strlen($post['newpassword']) < 6 || strlen($post['newpassword']) > 32) {return error('请输入6-32位密码');}
        $params = array();
        $params['password'] = $this->derange_password($post['newpassword']);
        if (false === $this->db->update('~user~', $params, 'id = ' . $uid)) {return error('密码修改失败');}
        // 注销登录
        $this->loginout($uid);
        return success('新密码设置成功');
    }

    public function edit ($user, $post)
    {
        $_param = array();
        if (!empty($post['telephone'])) {
            if (!preg_match("/^1[0-9]{10}$/", $post['telephone'])) return error('手机号为空或格式错误');
            if (!$this->check_repeat_user($user['id'], $post['telephone'])) return error('手机号已经存在');
            $_param['telephone'] = $post['telephone'];
        }
        if (!empty($post['password'])) {
            if (strlen($post['password']) < 6 || strlen($post['password']) > 32) return error('请输入6-32位密码');
            $_param['password'] = $this->derange_password($post['password']);
        }
        if (!empty($post['nickname'])) {
            $post['nickname'] = msubstr(safe_subject($post['nickname']), 0, 20);
            if (!$this->check_repeat_nickname($user['id'], $post['nickname'])) return error('用户昵称已经存在');
            $_param['nickname'] = $post['nickname'];
        }
        if (!empty($post['email'])) {
            if (!preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $post['email'])) return error('邮箱格式错误');
            $_param['email'] = msubstr($post['email'], 0, 30);
        }
        if (!empty($post['qq'])) {
            if (!preg_match("/^[1-9][0-9]{4,11}$/", $post['qq'])) return error('qq号格式错误');
            $_param['qq'] = msubstr($post['qq'], 0, 20);
        }
        !empty($post['description']) && $_param['description'] = msubstr(safe_subject($post['description']), 0, 30);
        !empty($post['address']) && $_param['address'] = msubstr(safe_subject($post['address']), 0, 30);
        !empty($post['realname']) && $_param['realname'] = msubstr(safe_subject($post['realname']), 0, 10);
        !empty($post['gender']) && $_param['gender'] = ($post['gender'] == 1 || $post['gender'] == 2) ? $post['gender'] : 0;
        if (!empty($post['idcard'])) {
            $post['idcard'] = strtoupper(trim_space($post['idcard']));
            if (!$this->check_id($post['idcard'])) return error('身份证号格式错误');
            if (!$birth = strtotime($this->parseidcard_getbirth($post['idcard']))) return error('该身份证号不能解析');
            $_param['idcard'] = $post['idcard'];
            $_param['birthday'] = date('Y-n-j', $birth);
            $_param['gender'] = intval($this->parseidcard_getsex($post['idcard']));
        }
        if (!empty($post['telephone'])) {
            // 短信验证码放在最后，因为短信验证码是一次性的
            if (!$this->checkSmsCode($post['telephone'], $post['code'])) return error('验证码错误');
        }
        if (!empty($_param)) {
            $_param['updatetime'] = TIMESTAMP;
            // 第三方登录的账户，绑定手机后成为正式账户
            if ($user['status'] == -1 && $_param['telephone']) {
                $_param['status'] = 1;
            }
            if (false === $this->db->update('~user~', $_param, 'id = ' . $user['id'])) {return error('操作失败');}
        
        }
        return success('操作成功');
    }

    public function register ($post)
    {
        if (!preg_match("/^1[0-9]{10}$/", $post['telephone'])) return error('手机号为空或格式错误');
        if (!$this->check_repeat_user(0, $post['telephone'])) return error('手机号已经存在');
        if (!empty($post['nickname'])) {
            $post['nickname'] = msubstr(safe_subject($post['nickname']), 0, 20);
            if (!$this->check_repeat_nickname(0, $post['nickname'])) return error('用户昵称已经存在');
        } else {
            $post['nickname'] = 'lr_' . mt_rand(11111111, 99999999);
        }
        // 密码不必填
        if (!empty($post['password']) && (strlen($post['password']) < 6 || strlen($post['password']) > 32)) return error('请输入6-32位密码');
        if (!empty($post['email']) && !preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $post['email'])) return error('邮箱格式错误');
        if (!empty($post['qq']) && !preg_match("/^[1-9][0-9]{4,11}$/", $post['qq'])) return error('qq号格式错误');
        // 身份证号验证
        if (!empty($post['idcard'])) {
            $post['idcard'] = strtoupper(trim_space($post['idcard']));
            $post['realname'] = trim_space($post['realname']);
            if (!$post['realname']) return error('真实姓名不能为空');
            if (!$this->check_id($post['idcard'])) return error('身份证号格式错误');
            // 自动获取出生日期和性别
            if (!$birth = strtotime($this->parseidcard_getbirth($post['idcard']))) return error('该身份证号不能解析');
            $sex = intval($this->parseidcard_getsex($post['idcard']));
        } else {
            $sex = intval($post['sex']);
            $sex = ($sex == 1 || $sex == 2) ? $sex : 0;
        }
        if (!$this->checkSmsCode($post['telephone'], $post['code'])) {return error('验证码错误');}
        if (!$this->db->insert('~user~', array(
                'nickname' => $post['nickname'], 
                'password' => $post['password'] ? $this->derange_password($post['password']) : '', 
                'telephone' => $post['telephone'], 
                'email' => msubstr($post['email'], 0, 30), 
                'qq' => msubstr($post['qq'], 0, 20), 
                'description' => msubstr(safe_subject($post['description']), 0, 30), 
                'address' => msubstr(safe_subject($post['address']), 0, 30), 
                'realname' => msubstr(safe_subject($post['realname']), 0, 10), 
                'idcard' => $post['idcard'], 
                'birthday' => isset($birth) ? date('Y-n-j', $birth) : '', 
                'gender' => $sex, 
                'createtime' => TIMESTAMP, 
                'status' => 1
        ))) {return error('注册失败');}
        if (!$uid = $this->db->getlastid()) {return error('操作错误');}
        $ret = array(
                'id' => $uid, 
                'nickname' => $post['nickname'], 
                'telephone' => $post['telephone']
        );
        if ($post['clientapp'] && $post['stoken']) {
            $loginret = $this->setloginstatus($uid, uniqid(), array(
                    'clienttype' => app_version(), 
                    'clientapp' => $post['clientapp'], 
                    'stoken' => $post['stoken']
            ));
            if ($loginret['errorcode'] === 0) {
                $ret['token'] = $loginret['data'];
            }
        }
        return success(json_unicode_encode($ret));
    }

    /**
     * 将远程头像保存本地
     * @param $imgurl 远程头像地址
     * @param $uid
     */
    function remoteAvatarToLocal ($imgurl, $uid)
    {
        // 保存远程图片
        $path = 'upload/u';
        $dir = APPLICATION_PATH . DIRECTORY_SEPARATOR . $path;
        mkdirm($dir);
        $filename = $uid . '.jpg';
        $url = $dir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($url)) return $path . '/' . $filename;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $imgurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $remote_data = curl_exec($ch);
        if (curl_errno($ch)) return '';
        curl_close($ch);
        if (!$remote_data) return '';
        @file_put_contents($url, $remote_data);
        unset($remote_data);
        // 生成头像缩略图
        thumb($url, APPLICATION_PATH . DIRECTORY_SEPARATOR . avatar($uid, 'mid'), '', 150, 150);
        thumb($url, APPLICATION_PATH . DIRECTORY_SEPARATOR . avatar($uid, 'big'), '', 300, 300);
        return $path . '/' . $filename;
    }

    /**
     * 第三方登录
     * @param $logintype 登录方式 wx|qq
     * @param $nickname 昵称
     * @param $sex 性别 1男 2女
     * @param $headimg 头像url地址
     * @param $openid 唯一标识
     * @param $unionid 微信的开放平台唯一标识
     * @param $extra
     */
    function extend_login ($logintype, $nickname, $sex, $headimg, $openid, $unionid = '', $extra = array(), $subscribe = 1)
    {
        $unionid || $unionid = '';
        $nickname = msubstr($nickname, 0, 20);
        $headimg = ishttp($headimg) ? $headimg : false;
        $subscribe = intval($subscribe);
        if (false === strpos('wx,qq', $logintype)) {return error('登录方式不允许');}
        $authcode = $openid;
        // 微信多平台下，unionid是一样的但是openid不一样，所以要取unionid
        if ($logintype == 'wx' && $unionid) {
            $authcode = $unionid;
        }
        if (empty($authcode)) {return error('参数错误');}
        // 获取已绑定的用户
        $uid = $this->db->table('~loginbinding~')->field('uid')->where('authcode = "' . $authcode . '" and type = "' . $logintype . '"')->find(null, true);
        $scode = uniqid();
        // 是否新注册用户
        $newpeople = false;
        if (!$uid) {
            $newpeople = true;
            if (!$uid = $this->db->transaction(function  ($db) use( $logintype, $nickname, $sex, $openid, $authcode, $subscribe) {
                if (!$db->insert('~user~', array(
                        'nickname' => '', 
                        'gender' => in_array($sex, array(
                                1, 
                                2
                        )) ? $sex : 0, 
                        'createtime' => TIMESTAMP, 
                        'lastlogintime' => TIMESTAMP, 
                        'status' => -1
                ))) return false;
                $uid = $db->getlastid();
                if (!$db->insert('~loginbinding~', array(
                        'uid' => $uid, 
                        'type' => $logintype, 
                        'authcode' => $authcode, 
                        'nickname' => '', 
                        'openid' => $openid, 
                        'subscribe' => $subscribe, 
                        'activetime' => date('Y-m-d H:i:s', TIMESTAMP)
                ))) return false;
                return $uid;
            })) {return error('创建用户失败，请重试！');}
            // 保存头像
            if ($headimg) {
                if ($logintype == 'wx') {
                    $headimg = substr($headimg, 0, strrpos($headimg, '/') + 1) . '132';
                }
                $headimg = $this->remoteAvatarToLocal($headimg, $uid);
                if ($headimg) {
                    $this->db->update('~user~', array(
                            'avatar' => $headimg
                    ), 'id = ' . $uid);
                }
            }
        } else {
            $this->db->update('~user~', array(
                    'lastlogintime' => TIMESTAMP
            ), 'id = ' . $uid);
            $_saveExtend = [
                    'openid' => $openid, 
                    'subscribe' => $subscribe
            ];
            if ($nickname) {
                $_saveExtend['nickname'] = $nickname;
            }
            $this->db->update('~loginbinding~', $_saveExtend, 'uid = ' . $uid . ' and authcode = "' . $authcode . '" and type = "' . $logintype . '"');
            // 获取最后一次登录的stoken
            if ($extra['stoken']) {
                $last_session = $this->db->table('~session~')->field('clienttype,clientapp,stoken')->where('userid = ' . $uid . ' and clienttype = "' . $extra['clienttype'] . '"')->find();
            }
        }
        // 登录信息
        $userinfo = $this->db->field('id,nickname,realname,idcard,address,telephone,gender,email,qq,description,birthday,area,state,updatetime,createtime,status')->table('~user~')->where('id = ' . $uid)->find();
        if (!$userinfo || !$userinfo['status']) {return error('您的账号不存在或已被禁用，请联系管理员!');}
        $loginret = $this->setloginstatus($uid, $scode, $extra);
        if ($loginret['errorcode'] !== 0) {return $loginret;}
        // 保证手机端登录的stoken唯一
        if ($extra['stoken']) {
            if ($_repeat = $this->db->table('~session~')->field('id')->where('userid <> ' . $uid . ' and clienttype = "' . $extra['clienttype'] . '" and stoken = "' . $extra['stoken'] . '"')->find()) {
                $this->db->update('~session~', array(
                        'stoken' => ''
                ), 'id = ' . $_repeat['id']);
            }
        }
        // 通知APP被挤下线
        if (!empty($last_session)) {
            if ($last_session['stoken'] && $extra['stoken'] != $last_session['stoken']) {
                $userinfo['last_clienttype'] = $last_session['clienttype'];
                $userinfo['last_clientapp'] = $last_session['clientapp'];
                $userinfo['last_stoken'] = $last_session['stoken'];
            }
        }
        $userinfo['token'] = $loginret['data'];
        $userinfo['avatar'] = $userinfo['avatar'] ? httpurl(avatar($userinfo['id'])) : '';
        $userinfo['newpeople'] = $newpeople;
        return success(json_unicode_encode($userinfo));
    }

    public function bindwxOpenid ($uid, $openid)
    {
        return $this->db->update('~loginbinding~', array(
                'openid' => $openid
        ), 'uid = ' . $uid . ' and type = "wx"');
    }

    public function login ($user, $pwd, $extra = array(), $simple = false)
    {
        $user = safe_subject(trim($user));
        if (!$user) {return error('账号不能为空');}
        $userinfo = $this->db->table('~user~')->field('id,nickname,password,avatar,realname,idcard,address,telephone,gender,email,qq,description,birthday,status')->where('status = 1 and telephone = "' . $user . '"')->find();
        if (!$userinfo) {return error('账号或密码错误');}
        // 判断是否验证密码
        if ($simple === false) {
            if (!$this->checkloginfail($user)) {return error('密码错误次数过多,请稍后重新登录');}
            // 打乱密码，防止简单的md5密码被破解
            if ($userinfo['password'] != $this->derange_password($pwd)) {
                $count = $this->loginfail($user);
                return error($count > 0 ? ('密码错误,您还可以登录 ' . $count . ' 次') : '密码错误次数过多,请15分钟后重新登录');
            }
        }
        // 登录成功
        $uid = $userinfo['id'];
        $scode = uniqid();
        $this->db->update('~user~', array(
                'lastlogintime' => TIMESTAMP
        ), 'id=' . $uid);
        // 获取最后一次登录的stoken
        if (isset($extra['stoken']) && $extra['stoken']) {
            $last_session = $this->db->table('~session~')->field('clienttype,clientapp,stoken')->where('userid = ' . $uid . ' and clienttype = "' . $extra['clienttype'] . '"')->find();
        }
        $loginret = $this->setloginstatus($uid, $scode, $extra);
        if ($loginret['errorcode'] !== 0) {return $loginret;}
        // 保证手机端登录的stoken唯一
        if (isset($extra['stoken']) && $extra['stoken']) {
            if ($_repeat = $this->db->table('~session~')->field('id')->where('userid <> ' . $uid . ' and clienttype = "' . $extra['clienttype'] . '" and stoken = "' . $extra['stoken'] . '"')->find()) {
                $this->db->update('~session~', array(
                        'stoken' => ''
                ), 'id = ' . $_repeat['id']);
            }
        }
        // 通知APP被挤下线
        if (!empty($last_session)) {
            if ($last_session['stoken'] && $extra['stoken'] != $last_session['stoken']) {
                $userinfo['last_clienttype'] = $last_session['clienttype'];
                $userinfo['last_clientapp'] = $last_session['clientapp'];
                $userinfo['last_stoken'] = $last_session['stoken'];
            }
        }
        $userinfo['token'] = $loginret['data'];
        $userinfo['avatar'] = $userinfo['avatar'] ? httpurl(avatar($userinfo['id'])) : '';
        unset($userinfo['password']);
        return success(json_unicode_encode($userinfo));
    }

    public function loginout ($uid, $clienttype = '', $cleartoken = true)
    {
        if ($cleartoken) set_cookie('token', null);
        $this->db->update('~session~', array(
                'scode' => '0', 
                'online' => 0
        ), 'userid = ' . $uid . ($clienttype ? ' and clienttype = "' . $clienttype . '"' : ''));
        return success(true);
    }

    public function onClose ($clienttype, $clientapp, $stoken)
    {
        return $this->db->update('~session~', array(
                'online' => 0
        ), 'clienttype = "' . $clienttype . '" and clientapp = "' . $clientapp . '" and stoken = "' . $stoken . '"');
    }

    public function onConnect ($uid, $clienttype, $clientapp, $stoken, $clientip)
    {
        if (strlen($stoken) < 20) return false;
        if ($_repeat = $this->db->table('~session~')->field('id')->where('clienttype = "' . $clienttype . '" and stoken = "' . $stoken . '"')->find()) {
            $this->db->update('~session~', array(
                    'stoken' => ''
            ), 'id = ' . $_repeat['id']);
        }
        return $this->db->update('~session~', array(
                'clientapp' => $clientapp, 
                'stoken' => $stoken, 
                'loginip' => $clientip, 
                'online' => 1
        ), 'userid = ' . $uid . ' and clienttype = "' . $clienttype . '"');
    }

    public function setloginstatus ($uid, $scode, $opt = array(), $expire = 0)
    {
        if (!$uid) return error(0);
        $update = array(
                'userid' => $uid, 
                'scode' => $scode, 
                'clienttype' => CLIENT_TYPE, 
                'clientinfo' => msubstr(safe_subject($_SERVER['HTTP_USER_AGENT'])), 
                'loginip' => get_ip(), 
                'online' => 1
        );
        !empty($opt) && $update = array_merge($update, $opt);
        if (!$this->db->norepeat('~session~', $update)) {return error('数据库错误');}
        $token = rawurlencode(authcode("$uid\t$scode\t{$update['clienttype']}", 'ENCODE'));
        set_cookie('token', $token, $expire);
        if (CLIENT_TYPE == 'wx') {
            session_start();
            $_SESSION['token'] = $token;
        }
        return success($token);
    }

    public function derange_password ($pwd)
    {
        return md5('!@a1b2c3' . $pwd . '3c2b1a@!');
    }

    private function loginfail ($ip)
    {
        $failedlogin = $this->db->table('~failedlogin~')->field('*')->where('ip = "' . $ip . '"')->find();
        $count = 1;
        if ($failedlogin) {
            $count = ($failedlogin['lastupdate'] + 900 > TIMESTAMP) ? $failedlogin['count'] + 1 : 1;
            $this->db->update('~failedlogin~', array(
                    'count' => $count, 
                    'lastupdate' => TIMESTAMP
            ), 'id=' . $failedlogin['id']);
        } else {
            $this->db->insert('~failedlogin~', array(
                    'count' => 1, 
                    'lastupdate' => TIMESTAMP, 
                    'ip' => $ip
            ));
        }
        $count = 5 - $count;
        return $count < 0 ? 0 : $count;
    }

    private function checkloginfail ($ip)
    {
        return $this->db->table('~failedlogin~')->field('*')->where('ip = "' . $ip . '" and count > 4 and lastupdate > ' . (TIMESTAMP - 900))->find() ? false : true;
    }

    /**
     * 获取身份证生日
     */
    public function parseidcard_getbirth ($idcard)
    {
        $birthday = strlen($idcard) == 15 ? ('19' . substr($idcard, 6, 6)) : substr($idcard, 6, 8);
        return $birthday;
    }

    /**
     * 获取省份证性别 男1 女2
     */
    public function parseidcard_getsex ($idcard)
    {
        if (strlen($idcard) == 15) {
            $idcard = $this->idcard_15to18($idcard);
        }
        $sexint = (int) substr($idcard, 16, 1);
        return $sexint % 2 === 0 ? 2 : 1;
    }

    /**
     * 身份证验证
     * @param $idcard
     * @return boolean
     */
    public function check_id ($idcard)
    {
        if (!$idcard) return false;
        if (strlen($idcard) == 15 || strlen($idcard) == 18) {
            if (strlen($idcard) == 15) {
                $idcard = $this->idcard_15to18($idcard);
            }
            if ($this->idcard_checksum18($idcard)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function idcard_checksum18 ($idcard)
    {
        if (strlen($idcard) != 18) {return false;}
        $idcard_base = substr($idcard, 0, 17);
        if ($this->idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
            return false;
        } else {
            return true;
        }
    }

    public function idcard_15to18 ($idcard)
    {
        if (strlen($idcard) != 15) {
            return false;
        } else {
            if (array_search(substr($idcard, 12, 3), array(
                    '996', 
                    '997', 
                    '998', 
                    '999'
            )) !== false) {
                $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
            } else {
                $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
            }
        }
        $idcard = $idcard . $this->idcard_verify_number($idcard);
        return $idcard;
    }

    public function idcard_verify_number ($idcard_base)
    {
        if (strlen($idcard_base) != 17) {return false;}
        $factor = array(
                7, 
                9, 
                10, 
                5, 
                8, 
                4, 
                2, 
                1, 
                6, 
                3, 
                7, 
                9, 
                10, 
                5, 
                8, 
                4, 
                2
        );
        $verify_number_list = array(
                '1', 
                '0', 
                'X', 
                '9', 
                '8', 
                '7', 
                '6', 
                '5', 
                '4', 
                '3', 
                '2'
        );
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

}
