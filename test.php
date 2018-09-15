<?php
require_once __DIR__ . '/vendor/autoload.php';

use czkj\eleme\Request;
use czkj\eleme\Tools;

function dump($info){
    var_dump($info);
    echo '<br>';
}

$url = ''; // 饿了么红包地址
$cookie = ''; // cookie
$phone = ''; // 手机号

$tools = new Tools();
// 1、链接解析
$data = $tools->getUrlQueryData($url);

// 2、绑定手机号
// 已绑定过的cookie可以跳过此步骤；除非你打算每次都用一个新的手机号
$request = new Request($data['sn'],$cookie);
$res = $request->sendMobileCode($phone);
dump($request->errMsg);
dump($res);exit; // 此处需要使用异步进行

$your_cookie_param = $request->cookieBindPhone($phone,'632833','14414f1d3b9558799b4818ca7c4335121d971988a60e2ffc96f0ada92dd77cb1');
dump($request->errMsg);
dump($res);

// 3、领取红包
$res = $request->getRedPacket($phone,$data['platform'],'632833','14414f1d3b9558799b4818ca7c4335121d971988a60e2ffc96f0ada92dd77cb1');
// 如果你要使用已绑定过的cookie，应该这样传值
// $res = $request->getRedPacket($phone,$data['platform'],'','','qq',$your_cookie_param);

// 4、返回值解析
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
dump($res);exit;