<?php
namespace app\index\controller;

use think\Loader;

class Index
{
    public function index()
    {
//        $this->fetch();exit;
        return view();
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }

    /**
     * 开户接口
     */
    public function registerAccount(){
        Loader::import('BankService',EXTEND_PATH);
        $bank = new \BankService();
        $userid = 202;
        $isApp = '';
        $data['transcode'] = 830001;
        $data['meruserid'] = $userid;
        $data['username'] = '刘少威';
        $data['idtype'] = 1;//1为身份证
        $data['idnumber'] = '430822199411175693';
        $data['userrole'] = 1;//1为出借人 2为借款人 3为担保人
        $data['transtype'] = 1;//个人开户
        $data['userpriv'] = '';
        $data['orderid'] = 2;
        $backurl = "http://" . $_SERVER['HTTP_HOST']; //网站域名
        if ($isApp == 1) {
            $data['url'] = $backurl . url("home/main/registerback");//返回地址
        }elseif($isApp == 2){
            $data['url'] = $backurl . url("m/fund/registerback");//返回地址

        }else{
            $data['url'] = $backurl . url("/Index/registerback");//返回地址
        }
        $return_data = $bank->request_get($data,$userid);
        var_dump($return_data);die;
        $decode_data = json_decode($return_data, true);
        var_dump($decode_data);die;
        if ($decode_data['code'] == '0') {
            //去银行页面处理
            $url = $bank->redirect_url . '/xinweb/index.html?custId=' . $bank->plat_form_money_moremore . '&requestKey=' . $decode_data['requestkey'];
            if ($isApp == 1 || $isApp ==2) {
                return  $url;
            }
            header('Location:' . $url);
        } else {
            return $decode_data;
        }
        $SignInfo = $decode_data;
        $data['SignInfo'] = $SignInfo['SignInfo'];
        $data['SignInfo2'] = $SignInfo['SignInfo2'];
        //20151222
        $epayconfig = array (
            'type' => '2',
            'test' => array (
                'register' => 'http://218.4.234.150:88/main/loan/toloanregisterbind.action',
                'withdraw' => 'http://218.4.234.150:88/main/loan/toloanwithdraws.action',
                'charge' => 'http://218.4.234.150:88/main/loan/toloanrecharge.action',
                'transfer' => 'http://218.4.234.150:88/main/loan/loan.action',
                'authorize' => 'http://218.4.234.150:88/main/loan/loan.action',
                ),
            'formal' => array (
                'register' => 'https://register.moneymoremore.com/',
                'withdraw' => 'https://withdrawals.moneymoremore.com/',
                'charge' => 'https://recharge.moneymoremore.com/',
                'transfer' => 'https://transfer.moneymoremore.com/',
                'authorize' => 'https://auth.moneymoremore.com/',
                ),
            'register' => 'https://register.moneymoremore.com/',
            'withdraw' => 'https://withdrawals.moneymoremore.com/',
            'charge' => 'https://recharge.moneymoremore.com/',
            'transfer' => 'https://transfer.moneymoremore.com/',
            'authorize' => 'https://auth.moneymoremore.com/',
            );
        $data['url'] = $epayconfig['register'];
        $this->ajaxmsg($data);
//        return view('registerAccount');
    }

    public function registerback(){
        var_dump(121212);die;
    }


    function ajaxmsg($msg = "", $type = 1, $is_end = true, $mobile = 0)
    {
        $json['status'] = $type;
        if (is_array($msg)) {
            foreach ($msg as $key => $v) {
                if ($v === null) $v = '';
                $json[$key] = $v;
            }
        } elseif (!empty($msg)) {
            $json['message'] = $msg;
        }
        if ($is_end) {
            if ($mobile != 1) {
                echo json_encode($json);
                exit;
            } else {
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($json));
            }
        } else {
            echo json_encode($json);
            exit;
        }
    }
}
