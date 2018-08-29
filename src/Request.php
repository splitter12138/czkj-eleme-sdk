<?php
// +----------------------------------------------------------------------
// | 饿了么红包请求类
// +----------------------------------------------------------------------
// | Date: 2018年8月29日22:06:03
// +----------------------------------------------------------------------
// | Author: 杨程智 <714112029@qq.com>
// +----------------------------------------------------------------------
namespace Czkj\Eleme;

class Request {
    const ORIGIN = 'https://h5.ele.me';

    public $errMsg;

    private $header;
    private $origin;
    private $user_agent;
    private $referer;
    private $timeout;
    private $sn;
    
    /**
     * 构造方法
     * @param string $sn
     */
    function __construct($sn){
        $this->sn = $sn;
        $this->origin = self::ORIGIN;
        $this->referer = $this->origin.'/hongbao/';
        $this->timeout = 30;
        $this->user_agent = 'Mozilla/5.0 (Linux; Android 7.0; MIX Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044004 Mobile Safari/537.36 V1_AND_SQ_7.5.0_794_YYB_D QQ/7.5.0.3430 NetType/WIFI WebP/0.3.0 Pixel/1080';
        $this->header = [
            'origin' => $this->origin,
            'content-type' => 'text/plain;charset=UTF-8'
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
     * 绑定手机号
     * @param string $phone
     * @param string $openid
     * @param string $sign  eleme_key
     * @return void
     */
    private function bindMobilePhone($phone,$openid,$sign){
        $result = $this->put("/restapi/v1/weixin/{$openid}/phone",['sign'=>$sign,'phone'=>$phone]);
        if($result){
            if($result['code'] != 204){ // 注意此处成功返回204而不是200，且data为空
                $this->errMsg = '绑定手机号失败！';
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
    public function getRedPacket($phone,$openid,$sign,$platform){
        // 绑定手机号
        if(!$this->bindMobilePhone($phone,$openid,$sign)) return false;
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
        $result = $this->post("/restapi/marketing/promotion/weixin/{$openid}",$data);
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
        return $res;
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

    private function post($url,$par){
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
        curl_setopt($cur, CURLOPT_HEADER, 0);
        curl_setopt($cur, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($cur, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($cur, CURLOPT_RETURNTRANSFER, 1);
        $httpContents = curl_exec($cur);
        $httpCode = curl_getinfo($cur, CURLINFO_HTTP_CODE);
        curl_close($cur);
		return ['code'=>$httpCode,'data'=>$httpContents];
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
}