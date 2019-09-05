<?php
class Marry_Action extends ActionPDO {

    public function __init ()
    {
        $_notAuth = [];
        if (!in_array($this->_action, $_notAuth)) {
            // 登录验证
            if (empty($this->_G['user'])) {
                $this->error('用户校验失败');
            }
        }
    }

    /**
     * 婚纱预约
     */
    public function create ()
    {
        if ($this->_jssdk) {
            $jssdk = $this->_jssdk->GetSignPackage();
            if ($jssdk['errorcode'] !== 0) {
                $jssdk = null;
            } else {
                $jssdk = $jssdk['data'];
            }
        }
        $this->render('marryFace.html', [
            'refund_rule' => json_decode(getConfig('refund_rule'), true),
            'jssdk' => $jssdk,
            'userinfo' => $this->_G['user']
        ]);
    }

}
