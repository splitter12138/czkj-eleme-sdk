<?php
// +----------------------------------------------------------------------
// | 饿了么红包请求类
// +----------------------------------------------------------------------
// | Date: 2018年8月29日22:06:03
// +----------------------------------------------------------------------
// | Author: 杨程智 <714112029@qq.com>
// +----------------------------------------------------------------------
namespace czkj\eleme;
use czkj\eleme\Tools;

class Request extends Tools{
    const ORIGIN = 'https://h5.ele.me';

    public $errMsg;

    private $header;
    private $origin;
    private $user_agent;
    private $referer;
    private $timeout;
    private $sn;
    private $cookie;
    private $cookies;
    
    /**
     * 构造方法
     * @param string $sn
     * @param string $cookie
     */
    function __construct($sn,$cookie){
        $this->sn = $sn;
        $this->cookie = $cookie;
        $this->origin = self::ORIGIN;
        $this->referer = $this->origin.'/hongbao/';
        $this->timeout = 30;
        $this->user_agent = 'Mozilla/5.0 (Linux; Android 7.0; MIX Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044004 Mobile Safari/537.36 V1_AND_SQ_7.5.0_794_YYB_D QQ/7.5.0.3430 NetType/WIFI WebP/0.3.0 Pixel/1080';
        $this->header = [
            'origin:'.$this->origin,
            'content-type:text/plain;charset=UTF-8',
            'x-shard:eosid=3028732173121603600'
        ];
    }

    /**
     * 获取红包信息
     * @param integer $theme_id
     * @return void
     */
    public function getRedPacketInfo($theme_id=0){
        $result = $this->get("/restapi/marketing/themes/{$theme_id}/group_sns/{$this->sn}");
        if($result){
            $json = json_decode($result['data'],true);
            if(empty($json)){
                $this->errMsg = '无法解析返回值！';
                return false;
            }
            if($result['code'] != 200){
                $this->errMsg = $json['message'];
                return false;
            }
            return $json;
        }
        $this->errMsg = '无法连接服务器！';
        return false;
    }

    public function sendMobileCode($phone){
        $url = '/restapi/eus/login/mobile_send_code';
        $data = [
            'mobile'=>$phone,
            'captcha_value'=>'',
            'captcha_hash'=>''
        ];
        $result = $this->post($url,json_encode($data));
        if($result){
            $json = json_decode($result['data'],true);
            if(empty($json)){
                $this->errMsg = '无法解析返回值！';
                return false;
            }
            if($result['code'] != 200){
                $this->errMsg = $json['message'];
                return false;
            }
            return $json;
        }
        return false;
    }
    
    public function loginByMobile($phone,$code,$token){
        $url = '/restapi/eus/login/login_by_mobile';
        $data = [
            'mobile' => $phone,
            'validate_code' => $code,
            'validate_token' => $token
        ];
        $result = $this->post($url,json_encode($data),'save',true);
        if($result){
            $this->setLog('loginByMobile',$result['data']);
            $json = json_decode($result['data'],true);
            if(empty($json)){
                $this->errMsg = '无法解析返回值！';
                return false;
            }
            if($result['code'] != 200){
                $this->errMsg = $json['message'];
                return false;
            }
            return isset($json['user_id']) ? $result['header'] : false;
        }
        return false;
    }

    public function changeMobile($phone,$openid,$sign){
        $data = [
            'sign' => $sign,
            'phone' => $phone
        ];
        $result = $this->post("/restapi/marketing/hongbao/weixin/{$openid}/change",json_encode($data),true);
        if($result){
            if($result['code'] != 200){
                $json = json_decode($result['data'],true);
                if(empty($json)){
                    $this->errMsg = '无法解析返回值！';
                    return false;
                }
                $this->errMsg = $json['message'] ? $json['message'] : '手机号绑定发送未知错误！';
                return false;
            }
            return true;
        }
        $this->errMsg = '无法连接服务器';
        return false;
    }

    /**
     * 领取红包
     * @param string $phone
     * @param string $openid
     * @param string $sign  eleme_key
     * @param string $platform  
     * @return void
     */
    public function getRedPacket($phone,$platform,$sms_code,$sms_token,$cookie_type='qq',$cookie=''){
        // 解析cookie
        $cookie_info = $this->qqCookie($cookie ? $cookie : $this->cookie);
        if(!$cookie_info || !isset($cookie_info['openid']) || !isset($cookie_info['eleme_key'])){
            $this->errMsg = 'Cookie解析失败！';
            return false;
        }
        $openid = $cookie_info['openid'];
        $sign = $cookie_info['eleme_key'];
        // 登录
        $header = $this->loginByMobile($phone,$sms_code,$sms_token);
        if(!$header) return false;
        // 解析response的header
        $set_cookie = $this->getSetCookie($header);
        if(!$set_cookie){
            $this->errMsg = 'ResponseHeader解析失败！';
            return false;
        }
        // 重新封装cookie
        // $this->cookie .= '; ' . implode('; ',$set_cookie);
        $this->cookie = implode('; ',$set_cookie);
        // 绑定手机号
        if(!$this->changeMobile($phone,$openid,$sign)) return false;
        $data = [
            'device_id' => '',
            'group_sn' => $this->sn,
            'hardware_id' => '',
            'method' => 'phone',
            'phone' => $phone,
            'platform' => $platform,
            'sign' => $sign,
            'track_id' => '',
            'unionid' => 'fuck',
            'weixin_avatar' => '',
            'weixin_username' => ''
        ];
        $result = $this->post("/restapi/marketing/promotion/weixin/{$openid}",json_encode($data),true);
        if($result){
            $json = json_decode($result['data'],true);
            if(empty($json)){
                $this->errMsg = '无法解析返回值！';
                return false;
            }
            if($result['code'] != 200){
                $this->errMsg = $json['message'];
                return false;
            }
            return $json;
        }
        $this->errMsg = '无法连接饿了么服务器！';
        return false;
    }

    private function get($url){
        $url = $this->origin . $url;
        $cur = curl_init($url);
        curl_setopt($cur, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($cur, CURLOPT_REFERER, $this->referer);
		curl_setopt($cur, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($cur, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($cur, CURLOPT_HEADER, 0);
        curl_setopt($cur, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
        $httpContents = curl_exec($cur);
        $httpCode = curl_getinfo($cur, CURLINFO_HTTP_CODE);
        curl_close($cur);
		return ['code'=>$httpCode,'data'=>$httpContents];
    }

    private function post($url,$par,$use_cookie=false,$return_header=false){
        $url = $this->origin . $url;
        $cur = curl_init($url);
        curl_setopt($cur, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($cur, CURLOPT_POST, 1);
        if(is_array($par)){
            curl_setopt($cur, CURLOPT_POSTFIELDS, http_build_query($par));
        }else{
            curl_setopt($cur, CURLOPT_POSTFIELDS, $par);
        }
        curl_setopt($cur, CURLOPT_REFERER, $this->referer);
		curl_setopt($cur, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($cur, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($cur, CURLOPT_HEADER, $return_header);
        // curl_setopt($cur, CURLINFO_HEADER_OUT, true);
        if($use_cookie){
            curl_setopt($cur, CURLOPT_COOKIE, $this->cookie);
        }
        curl_setopt($cur, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
        $httpContents = curl_exec($cur);
        $httpCode = curl_getinfo($cur, CURLINFO_HTTP_CODE);
        // var_dump(curl_getinfo($cur, CURLINFO_HEADER_OUT));
        $ret['code'] = $httpCode;
        if($return_header){
            $headerSize = curl_getinfo($cur, CURLINFO_HEADER_SIZE);
            list($httpHeader,$httpContents) = str_split($httpContents,$headerSize);
            $ret['header'] = $httpHeader;
        }
        $ret['data'] = $httpContents;
        curl_close($cur);
		return $ret;
    }

    private function put($url,$par){
        $url = $this->origin . $url;
        $cur = curl_init($url);
        curl_setopt($cur, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($cur, CURLOPT_CUSTOMREQUEST, "PUT");
        if(is_array($par)){
            curl_setopt($cur, CURLOPT_POSTFIELDS, json_encode($par));
        }else{
            curl_setopt($cur, CURLOPT_POSTFIELDS, $par);
        }
        curl_setopt($cur, CURLOPT_REFERER, $this->referer);
		curl_setopt($cur, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($cur, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($cur, CURLOPT_HEADER, 0);
        curl_setopt($cur, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
        $httpContents = curl_exec($cur);
        $httpCode = curl_getinfo($cur, CURLINFO_HTTP_CODE);
        curl_close($cur);
		return ['code'=>$httpCode,'data'=>$httpContents];
    }

    private function setLog($name,$content,$type='info'){
        if(is_array($content)){
            $content = json_encode($content,JSON_UNESCAPED_UNICODE);
        }
        $msg[] = date('Y-m-d H:i:s');
        $msg[] = strtoupper($type);
        $msg[] = $name;
        $msg[] = $content;
        $filename = './log/' . date('Ymd') . '.log';
        file_put_contents($filename,implode('|',$msg)."\r",FILE_APPEND);
    }
}