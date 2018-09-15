<?php
require_once __DIR__ . '/vendor/autoload.php';

use czkj\eleme\Request;
use czkj\eleme\Tools;
function dump($info){
    var_dump($info);
    echo '<br>';
}
$url = 'https://h5.ele.me/hongbao/#hardware_id=&is_lucky_group=True&lucky_number=9&track_id=&platform=0&sn=2a0837dd52b5c8dd&theme_id=3017&device_id=&refer_user_id=16652437'; // 饿了么红包地址
$cookie = 'perf_ssid=wolan7n29kdr2nk99mhds5bsjsc0xg57_2018-09-04; ubt_ssid=h9jv2tp47ilia9x5s22va1aixdsjq0om_2018-09-04; _utrace=7f5329d3bab7e593d75860d7a3c66ee1_2018-09-04; snsInfo[101204453]=%7B%22city%22%3A%22%22%2C%22constellation%22%3A%22%22%2C%22eleme_key%22%3A%22feedd7beb00083244247aa807c7a1513%22%2C%22figureurl%22%3A%22http%3A%2F%2Fqzapp.qlogo.cn%2Fqzapp%2F101204453%2FB94D617015107FA79F77AE26E3897B5E%2F30%22%2C%22figureurl_1%22%3A%22http%3A%2F%2Fqzapp.qlogo.cn%2Fqzapp%2F101204453%2FB94D617015107FA79F77AE26E3897B5E%2F50%22%2C%22figureurl_2%22%3A%22http%3A%2F%2Fqzapp.qlogo.cn%2Fqzapp%2F101204453%2FB94D617015107FA79F77AE26E3897B5E%2F100%22%2C%22figureurl_qq_1%22%3A%22http%3A%2F%2Fthirdqq.qlogo.cn%2Fqqapp%2F101204453%2FB94D617015107FA79F77AE26E3897B5E%2F40%22%2C%22figureurl_qq_2%22%3A%22http%3A%2F%2Fthirdqq.qlogo.cn%2Fqqapp%2F101204453%2FB94D617015107FA79F77AE26E3897B5E%2F100%22%2C%22gender%22%3A%22%E7%94%B7%22%2C%22is_lost%22%3A0%2C%22is_yellow_vip%22%3A%220%22%2C%22is_yellow_year_vip%22%3A%220%22%2C%22level%22%3A%220%22%2C%22msg%22%3A%22%22%2C%22nickname%22%3A%22bmvvfwnv%22%2C%22openid%22%3A%22B94D617015107FA79F77AE26E3897B5E%22%2C%22province%22%3A%22%22%2C%22ret%22%3A0%2C%22vip%22%3A%220%22%2C%22year%22%3A%220%22%2C%22yellow_vip_level%22%3A%220%22%2C%22name%22%3A%22bmvvfwnv%22%2C%22avatar%22%3A%22http%3A%2F%2Fthirdqq.qlogo.cn%2Fqqapp%2F101204453%2FB94D617015107FA79F77AE26E3897B5E%2F40%22%7D'; // cookie
$phone = '17195907695'; // 手机号

$tools = new Tools();
// 1、链接解析
$data = $tools->getUrlQueryData($url);
// 2、解析cookie
// $cookie = $tools->qqCookie($cookie);
// 3、获取红包信息
$request = new Request($data['sn'],$cookie);
// $redPacketInfo = $request->getRedPacketInfo($data['theme_id']);
// 4、领取红包
// var_dump($cookie);exit;
$res = $request->sendMobileCode($phone);
dump($request->errMsg);
dump($res);exit;
// $res = $request->loginByMobile($phone, '950138', 'e952747f98cdc1d20e29e2fb18711cb11670d4b8974bad61d1a01d3f3662f197');
// var_dump($request->errMsg);
// var_dump($res);exit;
// $res = $request->getRedPacket($phone,$data['platform'],'632833','14414f1d3b9558799b4818ca7c4335121d971988a60e2ffc96f0ada92dd77cb1');
// $res = $request->changeMobile($phone,$cookie['openid'],$cookie['eleme_key'],'07dwu3GPzNYVaq9r5V8aFSYW6UotZkckrQaQ');
// 5、返回值分析
// {
//     "account": "18587399730", // 领取的手机
//     "is_lucky": false, // 是否是最佳手气
//     "promotion_items": [ // 红包列表
//         {
//             "amount": 3,
//             "expire_date": "2018-08-30",
//             "hongbao_variety": [
//                 "全品类"
//             ],
//             "is_new_user": false,
//             "item_type": 1,
//             "name": "品质联盟专享红包",
//             "phone": "18587399730",
//             "source": "weixin_share_hongbao",
//             "sum_condition": 25,
//             "validity_periods": "2018-08-30到期"
//         }
//         // 此处省略其他红包
//     ],
//     "promotion_records": [ // 被领取了多少个了
//        {
    //         "amount": 3,
    //         "created_at": 1535549289,
    //         "is_doubling_issued": false,
    //         "is_lucky": false,
    //         "sns_avatar": "",
    //         "sns_username": "185****9730"
//        }
//     ],
//     "ret_code": 4,  // 领取成功
//     "theme_id": 2953
// }
// ret_code = 2 已经领过了
// ret_code = 1 红包已领完
// ret_code = 5 没有次数了
// ret_code = 3 领取成功


dump($request->errMsg);
dump(json_encode($res,JSON_UNESCAPED_UNICODE));
dump($res);exit;