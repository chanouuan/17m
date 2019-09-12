<?php

class CardsModel {

    private $db = null;

    public function __construct ()
    {
        $this->db = Db::getInstance();
    }

    public function getOrder ($id = null, $where = '', $limit = '', $field = '*', $order = 'id desc')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs = $this->db->table('~category~')->field('*')->where('id=' . $id)->find();
        } else {
            $rs = $this->db->table('~category~')->field($field)->where($where)->select();
        }
        return $rs;
    }

    public function getOrderlist ($sql)
    {
        $rs = $this->db->select($sql);
        return $rs;
    }

    public function getOrderinfo ($sql)
    {
        $rs = $this->db->find($sql);
        return $rs;
    }

    public function getsmscode ($telephone, $code)
    {
        $rs = $this->db->table('~smscode~')->field('*')->where('tel = "' . $telephone . '" and code="' . $code . '"')->find();
        return $rs;
    }

    public function insertCards ($cardsarray)
    {
        return $this->db->insert("pro_cards", $cardsarray);

    }

    public function getlastid ()
    {
        $rs = $this->db->getlastid();
        return $rs;
    }

    /**
     * 获取退款金额
     */
    public function getRefundPercent ($orderid)
    {
        $orderinfo = $this->db->table('~order~')->field('ordertime')->where('id = ' . $orderid)->find();
        return floatval(getRefundMoney($orderinfo['ordertime']));
    }

    /**
     * 创建交易单
     */
    public function createCard ($user, $params)
    {
        $params['citycode']    = intval($params['citycode']);
        $params['poolid']      = intval($params['poolid']);
        $params['store_id']    = intval($params['store_id']);
        $params['coupon']      = strlen($params['coupon']) == 8 ? strtoupper($params['coupon']) : null;
        $params['category_id'] = intval($params['category_id']);
        $params['fullpay']     = intval($params['fullpay']); // 是否全款
        $params['price']       = intval($params['price']); // 订单金额
        if (!$params['citycode'] || !$params['poolid'] || !$params['category_id'] || !$params['store_id'] || $params['price'] <= 0) {
            return error('参数错误');
        }

        // 预约姓名
        $params['buyer'] = safe_subject(msubstr($params['buyer'], 0, 20));
        $params['buyer'] = $params['buyer'] ? $params['buyer'] : $user['nickname'];

        // 校验号源
        $poolinfo = $this->db->table('pro_pool')->field('starttime,amount')->where('id = ' . $params['poolid'] . ' and storeid = ' . $params['store_id'] . ' and amount > 0')->find();
        if (!$poolinfo) {
            return error('抱歉！本时段已预约满，请选择其他时段。');
        }

        $params['ordertime'] = $poolinfo['starttime'];
        if (strtotime($params['ordertime']) < TIMESTAMP) {
            return error('预约日期无效');
        }

        // 减去占号数
        $orderPool = $this->db->table('pro_order')->field('count(*) as count')->where('storeid = ' . $params['store_id'] . ' and status = 0 and poolid = ' . $params['poolid'] . ' and createtime > "' . date('Y-m-d H:i:s', TIMESTAMP - 1200) . '"')->find();
        if ($orderPool) {
            if ($poolinfo['amount'] - $orderPool['count'] <= 0) {
                return error('本时段预约已被其他人占用，请选择其他时段！');
            }
        }

        // 计算定金
        $params['downpay'] = $params['price'];
        $downpay_percent   = intval(getConfig('downpay_percent'));
        if ($downpay_percent > 0 && !$params['fullpay']) {
            $params['downpay'] = $downpay_percent;
        }

        // 计算优惠卷
        $params['couponcost'] = 0;
        if ($params['coupon']) {
            $coupon_info = $this->db->table('pro_coupon')->field('id,cost')->where('storeid = ' . $params['store_id'] . ' and partner = "' . $params['coupon'] . '" and status <> 1 and expire > "' . date('Y-m-d H:i:s', TIMESTAMP) . '"')->find();
            if (!$coupon_info) {
                return error('优惠码无效或已过期！');
            }
            if ($coupon_info['cost'] >= $params['downpay']) {
                return error('优惠码当前不可用'); // 优惠金额不能大于等于订单金额
            }
            $params['downpay']    = $params['downpay'] - $coupon_info['cost'];
            $params['couponcost'] = $coupon_info['cost'];
            $params['coupon_id'] = $coupon_info['id'];
        }

        // 支付方式
        $params['payway'] = 'wxpayjs';
        // 订单号
        $params['ordercode'] = $this->generateOrderCode();

        if (!$cardid = $this->db->transaction(function ($db) use($user, $params) {
            // 添加未支付订单
            if (!$db->insert('pro_order', array(
                'uid'        => $user['id'],
                'pay'        => $params['price'],
                'downpay'    => $params['downpay'],
                'coupon'     => $params['couponcost'],
                'poolid'     => $params['poolid'],
                'storeid'    => $params['store_id'],
                'citycode'   => $params['citycode'],
                'ordertime'  => $params['ordertime'],
                'delay'      => $params['delay'],
                'item'       => $params['category_name'] . ':1',
                'buyersex'   => $user['gender'],
                'buyerphone' => $user['telephone'],
                'buyer'      => $params['buyer'],
                'createtime' => date('Y-m-d H:i:s', TIMESTAMP)
            ))) {
                return false;
            }
            if (!$orderid = $db->getlastid()) {
                return false;
            }
            // 添加交易单
            if (!$db->insert('pro_cards', array(
                'uid'        => $user['id'],
                'orderid'    => $orderid,
                'pay'        => $params['downpay'],
                'coupon'     => $params['coupon'],
                'payway'     => $params['payway'],
                'ordercode'  => $params['ordercode'],
                'createtime' => date('Y-m-d H:i:s', TIMESTAMP)
            ))) {
                return false;
            }
            if (!$cardid = $db->getlastid()) {
                return false;
            }
            // 更新优惠卷状态
            if ($params['coupon_id']) {
                if (!$db->update('pro_coupon', array('status' => 1), 'status <> 1 and id = ' . $params['coupon_id'])) {
                    return false;
                }
            }
            return $cardid;
        })) {
            return error('下单失败');
        }

        return success($cardid);
    }

    /**
     * 获取交易单
     */
    public function getCard ($id, $where = '', $limit = '', $field = '*')
    {
        if (isset($id)) {
            $id = intval($id);
            $rs = $this->db->table('~cards~')->field($field)->where('id = ' . $id . ($where ? ' and ' . $where : ''))->find();
        } else {
            $rs = $this->db->table('~cards~')->field($field)->where($where)->limit($limit)->select();
        }
        return $rs;
    }

    public function getCardInfo ($where, $field = '*')
    {
        return $this->db->table('~cards~')->field($field)->where($where)->limit(1)->find();
    }

    /**
     * 更新交易单
     */
    public function updateCard ($id, $update)
    {
        return false !== $this->db->update('~cards~', $update, 'id = ' . $id);
    }

    /**
     * 获取第三方平台openid
     */
    public function getOpenid ($uid)
    {
        return $this->db->field('openid')->table('~loginbinding~')->where('type = "wx" and uid = ' . $uid)->find(null, true);
    }

    /**
     * 退款申请
     */
    public function createRefund ($uid, $orderid)
    {
        $_cards = $this->db->field('id,payway')->table('~cards~')->where('status = 1 and orderid = ' . $orderid . ' and uid = ' . $uid)->find();
        if (!$_cards) {
            return error('订单已退款或不存在');
        }
        $refund_percent = $this->getRefundPercent($orderid);
        if ($refund_percent > 0) {
            try {
                $refund_result = https_request(APPLICATION_URL . '/index.php?ajax=1&c=' . $_cards['payway'] . '&a=refund&cardid=' . $_cards['id'] . '&formhash=' . encode_formhash(), null, 5);
            } catch (Exception $e) {
                return error($e->getMessage());
            }
            if ($refund_result['errorcode'] !== 0) {
                return error($refund_result['data']);
            }
        } else {
            // 不退钱记录时间
            $this->db->update('~cards~', array(
                    'refundtime' => date('Y-m-d H:i:s')
            ), 'id = ' . $_cards['id']);
        }
        if (!$this->db->update('~order~', array(
                'status' => -1
        ), 'status = 1 and id = ' . $orderid)) {
            return error('退款失败');
        }
        // 更新档期已预约数
        $orderinfo = $this->db->table('~order~')->field('id,poolid')->where('id = ' . $orderid)->find();
        $this->db->update('~pool~', array(
            'ordercount' => '~ordercount-1',
            'amount' => '~amount+1'
        ), 'id = ' . $orderinfo['poolid']);
        // 删除提醒
        $this->db->delete('~alert~', 'orderid = ' . $orderid);
        $cardinfo = $this->db->field('ordercode,refundpay')->table('~cards~')->where('id = ' . $_cards['id'])->find();
        // 推送消息
        $info = [];
        $info['orderid'] = $orderinfo['id'];
        $info['template_id'] = '__7kE5wMdS-xJlueCYhO9lDmdgV8DlgTSNUVz1_ayGA';
        $info['data'] = [
                'first' => [
                        'value' => '退款申请接收成功！',
                        'color' => '#7CDFA8'
                ],
                'keyword1' => [
                        'value' => $cardinfo['ordercode'],
                        'color' => '#1e82d0'
                ],
                'keyword2' => [
                        'value' => round_dollar($cardinfo['refundpay']) . '元',
                        'color' => '#1e82d0'
                ],
                'remark' => [
                        'value' => '受理时间：1~7个工作日。'
                ]
        ];
        if ($refund_percent > 0) {
            $this->send_template_message($uid, $info);
        }
        return success('操作成功');
    }

    /**
     * 退款成功
     */
    public function refundSuccess ($cardid)
    {
        if (!$card = $this->db->field('id,payway,refundpay')->table('~cards~')->where('id = ' . $cardid . ' and status = 1')->find()) {return error('参数错误');}
        if (!$this->db->update('~cards~', array(
                'status' => $card['payway'] == 'alipayapp' ? -2 : -1,
                'refundtime' => date('Y-m-d H:i:s', TIMESTAMP)
        ), 'id = ' . $cardid . ' and status = 1')) return error('退款失败');
        return success($card['payway'] . '已退款' . $card['refundpay'] . '分');
    }

    /**
     * 微信支付成功
     */
    public function wxpaySuccess ($out_trade_no, $trade_no, $mchid, $trade_type)
    {
        return $this->paySuccess($out_trade_no, $trade_no, $mchid, $trade_type);
    }

    /**
     * 支付成功统一接口
     * @param $out_trade_no 商户订单号
     * @param $trade_no 第三方支付订单号
     * @param $mchid 商户ID
     * @param $trade_type 支付类型
     * @param $trade_status 支付状态
     * @return array
     */
    public function paySuccess ($out_trade_no, $trade_no, $mchid, $trade_type, $trade_status = '')
    {
        if (!$card = $this->db->table('~cards~')->field('id,uid,pay,orderid,ordercode,status')->where('ordercode = "' . $out_trade_no . '"')->find()) {return error($out_trade_no . '未找到');}
        if ($card['status'] != 0) {return success($out_trade_no . '已交易完成');}
        $_cards = array(
                'status' => 1,
                'trade_no' => $trade_no,
                'paytime' => date('Y-m-d H:i:s', TIMESTAMP),
                'mchid' => $mchid,
                'trade_type' => $trade_type,
                'trade_status' => $trade_status
        );
        if ($trade_type == 'JSAPI' || $trade_type == 'NATIVE') $_cards['payway'] = 'wxpayjs';
        if ($trade_type == 'APP') $_cards['payway'] = 'wxpayapp';
        if (!$this->db->transaction(function  ($db) use( $_cards, $card) {
            if (!$db->update('~order~', array(
                    'status' => 1
            ), 'status = 0 and id = ' . $card['orderid'])) {return false;}
            if (!$db->update('~cards~', $_cards, 'status = 0 and id = ' . $card['id'])) {return false;}
            return true;
        })) {return error('操作失败');}
        // 更新档期已预约数
        $orderinfo = $this->db->table('~order~')->field('id,downpay,uid,poolid,ordertime,buyer,buyerphone,storeid,item')->where('id = ' . $card['orderid'])->find();
        $this->db->update('~pool~', array(
            'ordercount' => '~ordercount+1',
            'amount' => '~amount-1'
        ), 'id = ' . $orderinfo['poolid']);
        $storeinfo = $this->db->table('~store~')->field('name,address,tel')->where('id = ' . $orderinfo['storeid'])->find();
        // 添加提醒
        $this->db->insert('~alert~', array(
                'orderid' => $orderinfo['id'],
                'telephone' => $orderinfo['buyerphone'],
                'sendtime' => date('Y-m-d 20:00:00', strtotime($orderinfo['ordertime']) - 86400),
                'expiretime' => $orderinfo['ordertime'],
                'content' => json_unicode_encode(array(
                        'ordertime' => $orderinfo['ordertime'],
                        'storename' => $storeinfo['name'],
                        'item' => str_replace(';', ' ', str_replace(':1', '', $orderinfo['item'])),
                        'address' => $storeinfo['address'],
                        'telephone' => $storeinfo['tel']
                ))
        ));
        // 推送消息
        $info = [];
        $info['orderid'] = $orderinfo['id'];
        $info['template_id'] = 'RmKZ3ZwPlH8sH-FNMFQ9thEA2hf7p0v3lXegzAwzY2g';
        $info['data'] = [
                'first' => [
                        'value' => '你的订单已 支付成功，感谢你的预约！',
                        'color' => '#7CDFA8'
                ],
                'keyword1' => [
                        'value' => $orderinfo['buyer'],
                        'color' => '#1e82d0'
                ],
                'keyword2' => [
                        'value' => $storeinfo['name'] . '(' . $storeinfo['address'] . ')',
                        'color' => '#1e82d0'
                ],
                'keyword3' => [
                        'value' => showWeekDate($orderinfo['ordertime']),
                        'color' => '#1e82d0'
                ],
                'keyword4' => [
                        'value' => str_replace(';', ' ', str_replace(':1', '', $orderinfo['item'])),
                        'color' => '#1e82d0'
                ],
                'keyword5' => [
                        'value' => round_dollar($orderinfo['downpay']) . '元',
                        'color' => '#1e82d0'
                ],
                'remark' => [
                        'value' => '请您提早15分钟到店。'
                ]
        ];
        $this->send_template_message($orderinfo['uid'], $info);
        return success('交易成功');
    }

    /**
     * 支付确认
     */
    public function payQuery ($uid, $cardid)
    {
        $cardid = intval($cardid);
        if (!$card = $this->db->field('id,pay,payway,mchid,status')->table('~cards~')->where('uid = ' . $uid . ' and id = ' . $cardid)->find()) {return error('参数错误');}
        if ($card['status'] == 1) {
            return success('支付成功');
        } elseif ($card['status'] != 0) {return error('不是待支付订单');}
        // 查询订单
        try {
            $result = https_request(APPLICATION_URL . '?ajax=1&c=' . $card['payway'] . '&a=query&cardid=' . $card['id'], null, 5);
        } catch (Exception $e) {
            return error($e->getMessage());
        }
        if ($result['errorcode'] !== 0) {return error($result['data']);}
        $orderQueryResult = json_decode($result['data'], true);
        if ($orderQueryResult['pay_success'] !== 'SUCCESS') {return error($orderQueryResult['trade_status']);}
        if ($orderQueryResult['mchid'] != $card['mchid'] || $orderQueryResult['total_fee'] != $card['pay']) {return error('支付验证失败');}
        // 支付成功
        $this->paySuccess($orderQueryResult['out_trade_no'], $orderQueryResult['trade_no'], $orderQueryResult['mchid'], $orderQueryResult['trade_type'], $orderQueryResult['trade_status']);
        return success('支付成功');
    }

    /**
     * 检查订单超时
     */
    public function checkOrderPay ($uid, $orderid)
    {
        $orderid = intval($orderid);
        return $this->db->table('~order~')->field('count(*)')->where('id = ' . $orderid . ' and uid = ' . $uid . ' and status = 0 and createtime > "' . date('Y-m-d H:i:s', TIMESTAMP - 1200) . '"')->find(null, true) ? true : false;
    }

    /**
     * 记录支付错误日志
     */
    public function logPay ($type, $title, $info)
    {
        $this->db->insert('~log_pay~', array(
                'type' => $type,
                'title' => $title,
                'info' => $info,
                'createtime' => date('Y-m-d H:i:s', TIMESTAMP)
        ));
    }

    /**
     * 生成单号(28位)
     * @return string
     */
    public function generateOrderCode ()
    {
        return strval(date('YmdHis', TIMESTAMP) . (rand() % 10) . (rand() % 10) . (rand() % 10) . (rand() % 10));
    }

    /**
     * 发送模板消息
     */
    public function send_template_message ($uid, $info)
    {
        $logininfo = $this->db->field('openid,subscribe')->table('~loginbinding~')->where('type = "wx" and uid = ' . $uid)->find();
        if (!$logininfo || !$logininfo['openid'] || !$logininfo['subscribe']) {return error('参数无效');}
        $wxcontent = [];
        $wxcontent['touser'] = $logininfo['openid'];
        $wxcontent['url'] = APPLICATION_URL . '/?c=order&a=myorder&step=detail&id=' . $info['orderid'];
        $wxcontent['template_id'] = $info['template_id'];
        $wxcontent['topcolor'] = '#5dc331';
        $wxcontent['data'] = $info['data'];
        $jssdk = new JSSDK('wxf3b8281f2a822121', 'a8de747eeb0c0554bb518a7f5cb5b21d');
        $_access_token = $jssdk->getAccessToken();
        if ($_access_token['errorcode'] !== 0) {return error($_access_token['data']);}
        $access_token = $_access_token['data']['access_token'];
        try {
            $result = https_request('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token, json_encode($wxcontent), 10);
        } catch (Exception $e) {
            return error($e->getMessage());
        }
        if ($result['errcode']) {return error(json_encode($result));}
        return success('操作成功');
    }

    /**
     * 订单短信提醒
     */
    public function orderalert ()
    {
        // 删除前天的排班信息
        $this->db->delete('~pool~', 'today < "' . date('Y-m-d', TIMESTAMP - 3600 * 24) . '"');
        // 短信提醒
        $datetime = date('Y-m-d H:i:s', TIMESTAMP);
        $rs = $this->db->table('~alert~')->field('*')->where('sendtime < "' . $datetime . '" and expiretime > "' . $datetime . '"')->limit(100)->order('result')->select();
        if (!$rs) return true;
        foreach ($rs as $k => $v) {
            // 这里发短信
            $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
            $content = json_decode($v['content']);
            $post_data = "account=cf_17pcy&password=6ba91fe2c9daf43cfa8ee0b7d100d846&mobile=" . $v['telephone'] . "&content=" . rawurlencode("你预约了（" . $content->ordertime . "）到" . $content->storename . "拍摄" . $content->item . "。地址：" . $content->address . "，联系电话：" . $content->telephone . "。 来之前请保持头发清洁，我们期待您的光临。");
            $gets = $this->xml_to_array($this->Post($post_data, $target));
            // 发送成功
            if ($gets['SubmitResult']['code'] == 2) {
                // 删除提醒
                $this->db->delete('~alert~', 'orderid = ' . $v['orderid']);
            } else {
                $this->db->update('~alert~', array(
                        'result' => json_unicode_encode($gets)
                ), 'orderid = ' . $v['orderid']);
            }
        }

        return true;
    }

    public function Post ($curlPost, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
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

}
