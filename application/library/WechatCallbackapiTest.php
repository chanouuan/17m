<?php
/**
 * wechat php test
 */

class WechatCallbackapiTest {

    public $postStr = '';

    public function valid ()
    {
        $echoStr = $_GET["echostr"];
        
        // valid signature , option
        if (!$this->checkSignature()) {
            echo $echoStr;
            exit();
        }
        
        $this->responseMsg();
    }

    public function responseMsg ()
    {
        // get post data, May be due to the different environments
        // $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents('php://input');
        
        $this->postStr = $postStr;
        
        // extract post data
        if (!empty($postStr)) {
            
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            
            switch ($RX_TYPE) {
                case 'text':
                    $resultStr = $this->handleText($postObj);
                    break;
                case 'event':
                    $resultStr = $this->handleEvent($postObj);
                    break;
                case 'image':
                    $resultStr = $this->handleImage($postObj);
                    break;
                default:
                    $resultStr = $this->responseText($postObj, '您的消息已收到！如需预约请按照下列菜单进行预约操作。');
                    break;
            }
            echo $resultStr;
        } else {
            echo '';
            exit();
        }
    }

    public function handleImage ($postObj)
    {
        $picUrl = $postObj->PicUrl;
        return $this->responseText($postObj, '您的消息已收到！');
    }

    public function handleText ($postObj)
    {
        $keyword = trim($postObj->Content);
        if (!empty($keyword)) {
            return $resultStr;
        } else {
            return 'content is empty';
        }
    }

    public function handleEvent ($object)
    {
        $contentStr = '';
        switch ($object->Event) {
            case 'subscribe':
                $contentStr = "感谢关注十七平创意摄影工作室微信公众号！";
                break;
            default:
                $contentStr = "Unknow Event: " . $object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }

    public function responseText ($object, $content)
    {
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>%d</FuncFlag>
					</xml>";
        $msgType = 'text';
        $flag = 0;
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $content, $flag);
        return $resultStr;
    }

    public function responseImage ($object, $mediaid)
    {
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Image>
					<MediaId><![CDATA[%s]]></MediaId>
					</Image>
					</xml>";
        $msgType = 'image';
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $mediaid);
        return $resultStr;
    }

    public function responseNews ($object, $item)
    {
        $itemTpl = "<item>
		            <Title><![CDATA[%s]]></Title>
		            <Description><![CDATA[%s]]></Description>
		            <PicUrl><![CDATA[%s]]></PicUrl> 
		            <Url><![CDATA[%s]]></Url>
			        </item>";
        $nr = count($item);
        $item_str = '';
        foreach ($item as $k => $v) {
            $item_str .= sprintf($itemTpl, $v['title'], $v['description'], $v['picurl'], $v['url']);
        }
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>%d</ArticleCount>
					<Articles>%s</Articles>
					</xml>";
        $msgType = 'news';
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $msgType, $nr, $item_str);
        return $resultStr;
    }

    public function checkSignature ()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {throw new Exception('TOKEN is not defined!');}
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = TOKEN;
        $tmpArr = array(
                $token, 
                $timestamp, 
                $nonce
        );
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

}
