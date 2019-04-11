<?php
/**
 * created by lsw
 * 20190110
 */
namespace app\member\controller;
use think\Db;
use think\Model;
use think\Loader;

class User{
    public function index(){
        echo 'User index';
    }

    /**
     * 会员登录
     */
    public function login(){
        $data['user_name'] = htmlspecialchars($_POST['userName'],ENT_QUOTES);
        $user = Db::name('user')->where(array('user_name'=>$data['user_name']))->field('id,pwd,salt')->find();
        if ($user){
            $password = htmlspecialchars($_POST['password'],ENT_QUOTES);
            //随机数字加密
            $password = md5(md5($password) . $user['salt']);
            if ($user['pwd'] != $password) {
                echo json_encode(array('code'=>40001,'msg'=>'账号或密码错误!'));
            }else{
                
                $user_token = Db::name('user_token')->where(array('user_name'=>$data['user_name']))->field('token')->find();
                if ($user_token) {
                    $token = $user_token['token'];
                    $res = true;
                }else{
                    $data['user_id'] = $user['id'];
                    Loader::import('Function',EXTEND_PATH);
                    $lib_func = new \Lib_Function();
                    $token = $lib_func::randomString();
                    $token = md5($data['user_name'].$token);
                    $data['token'] = $token;
                    $data['login_time'] = time();
                    $res = Db::name('user_token')->insert($data);
                }
                if ($res){
                    session('user_id',$user['id']);
                    echo json_encode(array('code'=>40000,'msg'=>'登录成功!','token'=>$token));
                }else{
                    echo json_encode(array('code'=>40001,'msg'=>'登录失败，稍后再尝试登录!'));
                }
            }
            
        }else{
            echo json_encode(array('code'=>40001,'msg'=>'该账号不存在!'));
        }
    }
    /**
     * 会员注册
     */
    public function regist(){
        $data['user_name'] = htmlspecialchars($_POST['userName'],ENT_QUOTES);
        $user = Db::name('user')->where(array('user_name'=>$data['user_name']))->find();
        if ($user){
            echo json_encode(array('code'=>40001,'msg'=>'该账号已注册，请换个账号注册!'));
        }else{
            $password = htmlspecialchars($_POST['password'],ENT_QUOTES);
            //随机数字加密
            $data['salt'] = rand(10000,99999);
            $data['pwd'] = md5(md5($password) . $data['salt']);
            $res = Db::name('user')->insert($data);
            if ($res){
                echo json_encode(array('code'=>40000,'msg'=>'恭喜您注册成功!'));
            }else{
                echo json_encode(array('code'=>40001,'msg'=>'注册失败，稍后再尝试注册!'));
            }
        }
    }

    /**
     * 校验会员是否已注册
     */
    public function checkuser(){
        $data['user_name'] = htmlspecialchars($_POST['userName'],ENT_QUOTES);
        $user = Db::name('user')->where(array('user_name'=>$data['user_name']))->field("user_name")->select();
        if ($user){
            echo json_encode(array('code'=>40001,'msg'=>'该账号已注册，请换个账号注册!'));
        }else{
            echo json_encode(array('code'=>40000));
        }
    }

    /**
     * 校验输入的账号是否存在
     */
    public function checkloginuser(){
        $data['user_name'] = htmlspecialchars($_POST['userName'],ENT_QUOTES);
        $user = Db::name('user')->where(array('user_name'=>$data['user_name']))->field("user_name")->select();
        if ($user){
            echo json_encode(array('code'=>40000));
        }else{
            echo json_encode(array('code'=>40001,'msg'=>'该账号不存在，请换个账号登录!'));
        }
    }

    public function loginout(){
        $data['user_token'] = htmlspecialchars($_POST['user_token'],ENT_QUOTES);
        $user = Db::name('user_token')->where(array('token'=>$data['user_token']))->field("user_id")->select();
        if ($user){
            session(null);
            echo json_encode(array('code'=>40000));
        } else {
            echo json_encode(array('code'=>40001,'msg'=>'帐号状态异常!'));
        }
    }

    public function carFee(){
        $sum = 0;
        $money_orign = 10;
        for ($i=1;$i<=24;$i++){
            $money = $money_orign;
            if ($sum > 100 && $sum < 150){
                $money = $money_orign * 0.8;
            }
            if ($sum > 150){
                $money = $money_orign * 0.5;
            }
            $sum += $money;
        }
        echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
        echo '车费：'. $sum;die;
    }
}

