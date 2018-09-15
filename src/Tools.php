<?php
// +----------------------------------------------------------------------
// | 饿了么工具类
// +----------------------------------------------------------------------
// | 本SDK基于其他开源项目制作，也将免费开源
// | 请各位作者遵守开源许可
// +----------------------------------------------------------------------
// | Date: 2018年9月15日
// +----------------------------------------------------------------------
// | Author: Splitter <714112029@qq.com>
// +----------------------------------------------------------------------

namespace czkj\eleme;

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

    /**
     * 解析请求头中的set-cookie内容
     * @param string $header
     * @return void
     */
    public function getSetCookie($header){
        $list = array();        // 匹配后的结果
        $header_arr = explode(';',$header);
        $search_str = "Set-Cookie:";        // 搜索的字符串
        foreach($header_arr as $key=>$val ){
            if(strstr($val,$search_str)!==false){
                $ret = str_replace('Set-Cookie:','',strstr($val,$search_str));
                array_push($list, trim($ret));
            }
        }
        return $list;
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