<?php
// +----------------------------------------------------------------------
// | 饿了么红包请求类
// +----------------------------------------------------------------------
// | 本SDK基于其他开源项目制作，也将免费开源
// | 请各位作者遵守开源许可
// +----------------------------------------------------------------------
// | Date: 2018年9月15日
// +----------------------------------------------------------------------
// | Author: Splitter <714112029@qq.com>
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
            'content-type:text/plain;charset=UTF-8'
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

    /**
     * 发送短信验证码
     * @param string $phone  手机号
     * @return void
     */
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
            return $json['validate_token'];
        }
        return false;
    }
    
    /**
     * 手机号登录
     * @param string $phone     手机号
     * @param string $code      验证码
     * @param string $token     短信验证token
     * @return void
     */
    private function loginByMobile($phone,$code,$token){
        $url = '/restapi/eus/login/login_by_mobile';
        $data = [
            'mobile' => $phone,
            'validate_code' => $code,
            'validate_token' => $token
        ];
        $result = $this->post($url,json_encode($data),'save',true);
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
            return isset($json['user_id']) ? $result['header'] : false;
        }
        return false;
    }

    /**
     * 改变绑定的手机号
     * @param string $phone     手机号
     * @param string $openid    openid
     * @param string $sign      签名
     * @return void
     */
    private function changeMobile($phone,$openid,$sign){
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
     * cookie绑定手机号
     * @param string $phone         手机号
     * @param string $sms_code      短信验证码
     * @param string $sms_token     短信验证token
     * @param string $cookie_type   cookie类型
     * @return void
     */
    public function cookieBindPhone($phone,$sms_code,$sms_token,$cookie_type='qq'){
        if($cookie_type == 'qq'){
            $cookie_info = $this->qqCookie($this->cookie);
        }else{
            $cookie_info = $this->wxCookie($this->cookie);
        }
        if(!$cookie_info || !isset($cookie_info['openid']) || !isset($cookie_info['eleme_key'])){
            $this->errMsg = 'Cookie解析失败！';
            return false;
        }
        $openid = $cookie_info['openid'];
        $sign = $cookie_info['eleme_key'];
        $header = $this->loginByMobile($phone,$sms_code,$sms_token);
        if(!$header) return false;
        // 解析response的header
        $set_cookie = $this->getSetCookie($header);
        if(!$set_cookie){
            $this->errMsg = 'ResponseHeader解析失败！';
            return false;
        }
        $this->cookie = implode('; ',$set_cookie);
        // 绑定手机号
        if(!$this->changeMobile($phone,$openid,$sign)) return false;
        return $this->cookie;
    }

    /**
     * 领取红包
     * @param string $phone         手机号
     * @param string $platform      来源值
     * @param string $sms_code      短信验证码【可选】
     * @param string $sms_token     短信验证token【可选】
     * @param string $cookie_type   cookie类型：qq或wx
     * @param string $cookie        cookie参数【可选】在使用已绑定手机的cookie时，需传入此参数
     * @return void
     */
    public function getRedPacket($phone,$platform,$sms_code='',$sms_token='',$cookie_type='qq',$cookie=''){
        // 解析cookie
        if($cookie_type == 'qq'){
            $cookie_info = $this->qqCookie($this->cookie);
        }else{
            $cookie_info = $this->wxCookie($this->cookie);
        }
        if(!$cookie_info || !isset($cookie_info['openid']) || !isset($cookie_info['eleme_key'])){
            $this->errMsg = 'Cookie解析失败！';
            return false;
        }
        $openid = $cookie_info['openid'];
        $sign = $cookie_info['eleme_key'];

        if($sms_code && $sms_token){
            // 使用新号
            $this->cookie = $this->cookieBindPhone($phone,$sms_code,$sms_token,$cookie_type);
        }elseif($cookie){
            $this->cookie = $cookie;
        }else{
            $this->errMsg = '缺少必要参数！';
            return false;
        }
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

    /**
     * get方法
     * @param string $url  地址
     * @return void
     */
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

    /**
     * post方法
     * @param string    $url            请求地址
     * @param string    $par            请求参数
     * @param boolean   $use_cookie     是否使用cookie
     * @param boolean   $return_header  是否返回头部
     * @return void
     */
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
        if($use_cookie){
            curl_setopt($cur, CURLOPT_COOKIE, $this->cookie);
        }
        curl_setopt($cur, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
        $httpContents = curl_exec($cur);
        $httpCode = curl_getinfo($cur, CURLINFO_HTTP_CODE);
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

    /**
     * put方法（老版本put方法已弃用）
     * @param string $url  请求地址
     * @param string $par  请求参数
     * @return void
     */
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

    /**
     * 日志输出（调试用）
     * @param string $name      日志名称
     * @param string $content   日志内容
     * @param string $type      日志级别：INFO、DEBUG、WARONG、ERROR
     * @return void
     */
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