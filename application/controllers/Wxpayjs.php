<?php
/**
 * 微信JS支付
 */
class Wxpayjs_Action extends ActionPDO {

    public function __init ()
    {
        include APPLICATION_PATH . '/application/library/WxPayJs/WxPayPubHelper.php';
    }

    /** 
     * 获取支付参数
     */
    public function api ()
    {
        // 交易单id
        $cardid = intval(getgpc('cardid'));
        $apimodel = new CardsModel();
        if (!$card = $apimodel->getCard($cardid, 'status = 0 and payway = "wxpayjs"', null, 'uid,pay,ordercode')) {
            $this->error('交易单不存在');
        }
        if (!$card['pay']) {
            $this->error('交易金额错误');
        }
        // 获取openid
        if (!$openid = $apimodel->getOpenid($card['uid'])) {
            $this->error('openid为空');
        }
        // 使用统一支付接口，获取prepay_id
        $unifiedOrder = new UnifiedOrder_pub();
        $unifiedOrder->setParameter('openid', $openid); // 商品描述
        $unifiedOrder->setParameter('body', '预约定金'); // 商品描述
        $unifiedOrder->setParameter('out_trade_no', "{$card['ordercode']}"); // 商户订单号
        $unifiedOrder->setParameter('total_fee', "{$card['pay']}"); // 总金额 单位分 不能有小数点
        $unifiedOrder->setParameter('notify_url', NOTIFY_URL); // 通知地址
        $unifiedOrder->setParameter('trade_type', 'JSAPI'); // 交易类型
        $prepay_id = $unifiedOrder->getPrepayId();
        // 校验接口返回
        if ($unifiedOrder->result['return_code'] == 'FAIL') {
            $this->error($unifiedOrder->result['return_msg']);
        } else if ($unifiedOrder->result['result_code'] == 'FAIL') {
            $this->error($unifiedOrder->result['err_code'] . ':' . $unifiedOrder->result['err_code_des']);
        } else if ($unifiedOrder->result['result_code'] == 'SUCCESS') {
            $jsApi = new JsApi_pub();
            $jsApi->setPrepayId($prepay_id);
            $jsApiParameters = json_decode($jsApi->getParameters(), true);
            // jssdk中的timestamp为小写
            $jsApiParameters['timestamp'] = $jsApiParameters['timeStamp'];
            unset($jsApiParameters['timeStamp']);
            $this->success(json_encode($jsApiParameters));
        } else {
            $this->error('API ERROR');
        }
        return FALSE;
    }

    /**
     * 异步通知url
     */
    public function notify ()
    {
        // 使用通用通知接口
        $notify = new Notify_pub();
        
        // 存储微信的回调
        // $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml = file_get_contents('php://input');
        $notify->saveData($xml);
        
        // 验证签名，并回应微信。
        // 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        // 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        // 尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($notify->checkSign() == FALSE) {
            $notify->setReturnParameter('return_code', 'FAIL'); // 返回状态码
            $notify->setReturnParameter('return_msg', '签名失败'); // 返回信息
        } else {
            $notify->setReturnParameter('return_code', 'SUCCESS'); // 设置返回码
        }
        $returnXml = $notify->returnXml();
        
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        
        $apimodel = new CardsModel();
        $success = false;
        if ($notify->checkSign() == TRUE) {
            if ($notify->data['return_code'] == 'SUCCESS' && $notify->data['result_code'] == 'SUCCESS') {
                // 支付成功
                $result = $apimodel->wxpaySuccess($notify->data['out_trade_no'], $notify->data['transaction_id'], MCHID, $notify->data['trade_type']);
                if ($result['errorcode'] !== 0) {
                    $apimodel->logPay('wxpayjs', $result['data'], $xml);
                } else {
                    $success = true;
                }
            } else {
                $apimodel->logPay('wxpayjs', '支付接口业务出错', $xml);
            }
        } else {
            $apimodel->logPay('wxpayjs', '支付接口验证签名失败', $xml);
        }
        if ($success) {
            echo $returnXml;
        }
        return FALSE;
    }

    /**
     * 退款
     */
    public function refund ()
    {
        if (!submitcheck(null, false)) {
            $this->error('验证失败');
        }
        // 交易单id
        $cardid = intval(getgpc('cardid'));
        $apimodel = new CardsModel();
        if (!$card = $apimodel->getCard($cardid, 'status = 1', null, 'orderid,pay,refundcode,ordercode')) {
            $this->error('交易单无效');
        }
        // 更新退单号和实退金额
        $_update = array();
        // 已有退款单号就不重复生成，防止重复退款
        $_update['refundcode'] = $card['refundcode'] ? $card['refundcode'] : $apimodel->generateOrderCode();
        // 退款率
        $_update['refundpay'] = intval($card['pay'] * $apimodel->getRefundPercent($card['orderid']));
        if (!$apimodel->updateCard($cardid, $_update)) {
            $this->error('更新交易失败');
        }
        $out_trade_no = $card['ordercode'];
        $out_refund_no = $_update['refundcode'];
        $total_fee = $card['pay'];
        $refund_fee = $_update['refundpay'];
        if (!$refund_fee) {
            $this->error('退款金额无效');
        }
        // 使用退款接口
        $refund = new Refund_pub();
        $refund->setParameter('out_trade_no', "$out_trade_no"); // 商户订单号
        $refund->setParameter('out_refund_no', "$out_refund_no"); // 商户退款单号
        $refund->setParameter('total_fee', "$total_fee"); // 总金额
        $refund->setParameter('refund_fee', "$refund_fee"); // 退款金额
        $refund->setParameter('op_user_id', MCHID); // 操作员
        $refundResult = $refund->getResult();
        if (!$refundResult) {
            $this->error('退款接口调用失败');
        }
        if ($refundResult['return_code'] == 'FAIL') {
            $this->error('微信通信出错：' . $refundResult['return_msg']);
        } elseif ($refundResult['result_code'] == 'FAIL') {
            $this->error('微信错误：' . $refundResult['err_code'] . ' 描述：' . $refundResult['err_code_des']);
        } elseif ($refundResult['result_code'] == 'SUCCESS') {
            // 退款成功
            echo json_unicode_encode($apimodel->refundSuccess($cardid));
            exit();
        }
        $this->error('退款失败');
    }

    /**
     * 订单查询
     */
    public function query ()
    {
        // 交易单id
        $cardid = intval(getgpc('cardid'));
        $apimodel = new CardsModel();
        if (!$card = $apimodel->getCard($cardid, 'payway = "wxpayjs"', null, 'ordercode')) {
            $this->error('交易单无效');
        }
        // 使用订单查询接口
        $orderQuery = new OrderQuery_pub();
        // 设置必填参数
        $orderQuery->setParameter('out_trade_no', "{$card['ordercode']}");
        // 获取订单查询结果
        if (!$orderQueryResult = $orderQuery->getResult()) {
            $this->error('查询失败');
        }
        if ($orderQueryResult['return_code'] == 'FAIL') {
            $this->error('通信出错：' . $orderQueryResult['return_msg']);
        } elseif ($orderQueryResult['result_code'] == 'FAIL') {
            $this->error('错误描述：' . $orderQueryResult['err_code_des']);
        } elseif ($orderQueryResult['result_code'] == 'SUCCESS') {
            $result = array();
            $result['mchid'] = $orderQueryResult['mch_id'];
            $result['out_trade_no'] = $orderQueryResult['out_trade_no'];
            $result['trade_no'] = $orderQueryResult['transaction_id'];
            $result['trade_type'] = $orderQueryResult['trade_type'];
            $result['trade_status'] = $orderQueryResult['trade_state'];
            $result['total_fee'] = $orderQueryResult['total_fee'];
            $result['time'] = strtotime($orderQueryResult['time_end']);
            // 判断支付成功
            if ($result['trade_status'] === 'SUCCESS') {
                $result['pay_success'] = 'SUCCESS';
            }
            // 不加密
            $this->success(json_unicode_encode($result), '', 0, false);
        }
        $this->error('数据错误');
    }

}
