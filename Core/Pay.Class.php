<?php
/*
 * 支付核心类库
 * Author：烟雨寒云
 * Mail：admin@yyhy.me
 * Date:2019/10/13
 */

//禁止文件直接被访问
if (!defined('ROOT')) {
    header('HTTP/1.1 404 Not Found', true, 404);
    die();
}

class Pay
{
    private $pid;
    private $key;
    private $api;

    public function __construct($pid = null, $key = null, $api = null)
    {
        $this->pid = $pid;
        $this->key = $key;
        $this->api = $api;
    }

    /**
     * @Note  支付发起
     * @param $type   支付方式
     * @param $out_trade_no     订单号
     * @param $notify_url     异步通知地址
     * @param $return_url     回调通知地址
     * @param $name     商品名称
     * @param $money     金额
     * @param $sitename     站点名称
     * @return string
     */
    public function submit($type, $out_trade_no, $notify_url, $return_url, $name, $money, $sitename)
    {
        $data = [
            'pid' => $this->pid,
            'type' => $type,
            'out_trade_no' => $out_trade_no,
            'notify_url' => $notify_url,
            'return_url' => $return_url,
            'name' => $name,
            'money' => $money,
            'sitename' => $sitename
        ];
        $string = http_build_query($data);
        $sign = $this->getsign($data);
        return 'http://' . $this->api . '/submit.php?' . $string . '&sign=' . $sign . '&sign_type=MD5';
    }

    /**
     * @Note   验证支付
     * @param $data  待验证参数
     * @return bool
     */
    public function verify($data)
    {
        if (!isset($data['sign']) || !$data['sign']) {
            return false;
        }
        $sign = $data['sign'];
        unset($data['sign']);
        unset($data['sign_type']);
        $sign2 = $this->getSign($data);
        if ($sign != $sign2) {
            //兼容傻逼彩虹易支付
            unset($data['_input_charset']);
            $sign2 = $this->getSign($data);
            if ($sign == $sign2) {
                if ($_REQUEST['trade_status'] == 'TRADE_SUCCESS') return true;
                return false;
            }
            return false;
        }
        if ($_REQUEST['trade_status'] == 'TRADE_SUCCESS') return true;
        return false;
    }

    /**
     * @Note  生成签名
     * @param $data   参与签名的参数
     * @return string
     */
    public function getSign($data)
    {
        $data = array_filter($data);
        ksort($data);
        $str1 = '';
        foreach ($data as $k => $v) {
            $str1 .= '&' . $k . "=" . $v;
        }
        $str = $str1 . $this->key;
        $str = trim($str, '&');
        $sign = md5($str);
        return $sign;
    }
}