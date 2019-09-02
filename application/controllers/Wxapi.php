<?php
class Wxapi_Action extends ActionPDO {

    public function __init ()
    {
        define('TOKEN', '17m');
    }

    public function wechat ()
    {
        $wechatObj = new WechatCallbackapiTest();
        try {
            if (!$wechatObj->checkSignature()) {
                echo $_GET['echostr'];
                return null;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
        $postStr = file_get_contents('php://input');
        if (empty($postStr)) {return null;}
        libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $RX_TYPE = trim($postObj->MsgType);
        switch ($RX_TYPE) {
            case 'text':
                $resultStr = $wechatObj->responseText($postObj, $this->handleText($postObj));
                break;
            case 'event':
                $resultStr = $wechatObj->handleEvent($postObj);
                break;
            default:
                $resultStr = $wechatObj->responseText($postObj, '您的消息已收到！如需预约请按照下列菜单进行预约操作。');
                break;
        }
        echo $resultStr;
        return null;
    }

    public function handleText ($postObj)
    {
        $keyword = trim($postObj->Content);
        if (empty($keyword)) {return 'content is empty';}
        if ($keyword == '统计') {
            $ordermodel = new OrderModel();
            $contentStr = $ordermodel->getWeChatText($postObj->FromUserName);
        } else {
            $contentStr = '您的消息已收到！';
        }
        return $resultStr;
    }

}