<?php
// +----------------------------------------------------------------------
// | 饿了么红包工具类
// +----------------------------------------------------------------------
// | Date: 2018年8月29日22:06:03
// +----------------------------------------------------------------------
// | Author: 杨程智 <714112029@qq.com>
// +----------------------------------------------------------------------

namespace Czkj\Eleme;

class Tools {
    const QQ_COOKIE_CODE = 'snsInfo[101204453]=';
    const WX_COOKIE_CODE = 'snsInfo[wx2a416286e96100ed]=';
    
    /**
     * 链接地址格式化
     * @param string $url
     * @return void
     */
    public function urlFormat($url){
        return preg_replace("/&amp;/",'',$url);
    }

    /**
     * 获取URL中的参数
     * @param string $url
     * @return void
     */
    public function getUrlQueryData($url){
        $url = $this->urlFormat($url);
        parse_str(str_replace('https://h5.ele.me/hongbao/#','',$url),$query_data);
        return $query_data;
    }

    /**
     * 解析QQcookie
     * @param string $cookie
     * @return void
     */
    public function qqCookie($cookie){
        return $this->cookieAnalysis($cookie,'qq');
    }

    /**
     * 解析微信cookie
     * @param string $cookie
     * @return void
     */
    public function wxCookie($cookie){
        return $this->cookieAnalysis($cookie,'wx');        
    }

    private function cookieAnalysis($cookie,$type){
        $code = $type == 'wx' ? self::WX_COOKIE_CODE : self::QQ_COOKIE_CODE;
        $cookie = urldecode(trim($cookie));
        $cookie_arr = explode('; ',$cookie);
        if(!$cookie_arr || count($cookie_arr) != 4){
            return false;
        }
        $playload = $cookie_arr[3];
        if(strpos($playload,$code) === false){
            return false;
        }
        $playload = json_decode(str_replace($code,'',$playload),true);
        if(empty($playload) || !isset($playload['eleme_key'],$playload['openid'])){
            return false;
        }
        return ['eleme_key'=>$playload['eleme_key'],'openid'=>$playload['openid']];
    }
}