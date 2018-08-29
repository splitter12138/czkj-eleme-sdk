# 饿了么领红包PHP-SDK

基于[mtdhb/get](https://github.com/mtdhb/get)开源代码的PHP简单封装。

感谢[zhuweiyou](https://github.com/zhuweiyou)的无私贡献。

该项目主要是练习composer的使用，不详尽之处欢迎补充。

## 环境

PHP >= 5.6

## composer

```
composer require czkj/eleme
```

## 使用方法

> 详细用法可参考https://github.com/mtdhb/get源码

~~~php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Czkj\Eleme\Request;
use Czkj\Eleme\Tools;

$url = ''; // 饿了么红包地址
$cookie = ''; // cookie
$phone = ''; // 手机号

$tools = new Tools();
// 1、链接解析
$data = $tools->getUrlQueryData($url);
// 2、解析cookie
$cookie = $tools->qqCookie($cookie);
// 3、获取红包信息
$request = new Request($data['sn']);
$redPacketInfo = $request->getRedPacketInfo($data['theme_id']);
// 4、领取红包
$res = $request->getRedPacket($phone,$cookie['openid'],$cookie['eleme_key'],$data['platform']);

// ret_code = 2 已经领过了
// ret_code = 1 红包已领完
// ret_code = 5 没有次数了
// ret_code = 3 领取成功
var_dump($request->errMsg);
var_dump($res);exit;
~~~

## getRedPacket返回值

~~~json
{
    "account": "185****9730", // 领取的手机
    "is_lucky": false, // 是否是最佳手气
    "promotion_items": [ // 红包列表
        {
            "amount": 3,
            "expire_date": "2018-08-30",
            "hongbao_variety": [
                "全品类"
            ],
            "is_new_user": false,
            "item_type": 1,
            "name": "品质联盟专享红包",
            "phone": "18587399730",
            "source": "weixin_share_hongbao",
            "sum_condition": 25,
            "validity_periods": "2018-08-30到期"
        }
        // 此处省略其他红包
    ],
    "promotion_records": [ // 被领取了多少个了
       {
            "amount": 3,
            "created_at": 1535549289,
            "is_doubling_issued": false,
            "is_lucky": false,
            "sns_avatar": "",
            "sns_username": "185****9730"
       }
    ],
    "ret_code": 4,  // 领取成功
    "theme_id": 2953
}
~~~
