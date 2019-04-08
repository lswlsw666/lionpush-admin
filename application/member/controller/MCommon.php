<?php
namespace app\member\controller;
use think\Db;
use think\Controller;

class MCommon extends Controller{
    public function getUser($user_token){
        $user = Db::name('user_token')->where(array('token'=>$user_token))->field("user_id")->find();
        return $user;
    }

}