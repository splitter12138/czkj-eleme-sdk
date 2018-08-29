<?php
require_once __DIR__ . '/vendor/autoload.php';

use Czkj\Eleme\Request;
use Czkj\Eleme\Tools;

$url = 'https://h5.ele.me/hongbao/#hardware_id=&is_lucky_group=True&lucky_number=9&track_id=&platform=4&sn=2a05b755dc3438b9&theme_id=2953&device_id=&refer_user_id=14729332';
$cookie = 'perf_ssid=du727vr00liqpzrzl4qz0yuwyq5giinz_2018-08-25; ubt_ssid=vx6qwcecfh9l9qg9ysfeughlwz2o3s0h_2018-08-25; _utrace=67702533f9af1b818e589caad50d0f4c_2018-08-25; snsInfo[101204453]=%7B%22city%22%3A%22%E6%98%86%E6%98%8E%22%2C%22constellation%22%3A%22%22%2C%22eleme_key%22%3A%22302234e87cf0fb38ccc853a540945ced%22%2C%22figureurl%22%3A%22http%3A%2F%2Fqzapp.qlogo.cn%2Fqzapp%2F101204453%2F4430EE53A997E304AC85EA0C7FD94A20%2F30%22%2C%22figureurl_1%22%3A%22http%3A%2F%2Fqzapp.qlogo.cn%2Fqzapp%2F101204453%2F4430EE53A997E304AC85EA0C7FD94A20%2F50%22%2C%22figureurl_2%22%3A%22http%3A%2F%2Fqzapp.qlogo.cn%2Fqzapp%2F101204453%2F4430EE53A997E304AC85EA0C7FD94A20%2F100%22%2C%22figureurl_qq_1%22%3A%22http%3A%2F%2Fthirdqq.qlogo.cn%2Fqqapp%2F101204453%2F4430EE53A997E304AC85EA0C7FD94A20%2F40%22%2C%22figureurl_qq_2%22%3A%22http%3A%2F%2Fthirdqq.qlogo.cn%2Fqqapp%2F101204453%2F4430EE53A997E304AC85EA0C7FD94A20%2F100%22%2C%22gender%22%3A%22%E5%A5%B3%22%2C%22is_lost%22%3A0%2C%22is_yellow_vip%22%3A%220%22%2C%22is_yellow_year_vip%22%3A%220%22%2C%22level%22%3A%220%22%2C%22msg%22%3A%22%22%2C%22nickname%22%3A%22Doraemon%22%2C%22openid%22%3A%224430EE53A997E304AC85EA0C7FD94A20%22%2C%22province%22%3A%22%E4%BA%91%E5%8D%97%22%2C%22ret%22%3A0%2C%22vip%22%3A%220%22%2C%22year%22%3A%221995%22%2C%22yellow_vip_level%22%3A%220%22%2C%22name%22%3A%22Doraemon%22%2C%22avatar%22%3A%22http%3A%2F%2Fthirdqq.qlogo.cn%2Fqqapp%2F101204453%2F4430EE53A997E304AC85EA0C7FD94A20%2F40%22%7D';
$phone = '18587399730';

$tools = new Tools();
// 1、链接解析
$data = $tools->getUrlQueryData($url);
// 2、解析cookie
$cookie = $tools->qqCookie($cookie);
// 3、获取红包信息
$request = new Request($data['sn']);
$redPacketInfo = $request->getRedPacketInfo($data['theme_id']);
// 4、领取红包
// var_dump($cookie);exit;
$res = $request->getRedPacket($phone,$cookie['openid'],$cookie['eleme_key'],$data['platform']);
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
var_dump($request->errMsg);
var_dump($res);exit;