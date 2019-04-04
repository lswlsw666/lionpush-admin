<?php
namespace app\member\controller;
use think\Controller;
use think\Db;

class Msg extends Controller {
    public function index(){
        $user_token = htmlspecialchars($_POST['user_token'],ENT_QUOTES);
        $user = Db::name('user_token')->where(array('token'=>$user_token))->field("user_id")->find();
        if (!session('user_id') || $user['user_id'] != session('user_id')){
            echo json_encode(array('code'=>40001,'msg'=>'用户登录状态有误!'));die;
        }
        if ($user){
            $messages = Db::name('messages')->where(array('uid'=>$user['user_id']))->order('id desc')->select();
            if ($messages){
                foreach ($messages as &$message){
                    foreach ($message as $key => &$item){
                        if ($key == 'm_invest_money'){
                            switch ($item){
                                case 'a' : $item = '1万以下';break;
                                case 'b' : $item = '1~5万';break;
                                case 'c' : $item = '5~10万';break;
                                case 'd' : $item = '10~20万';break;
                                case 'e' : $item = '20~50万';break;
                                case 'f' : $item = '50万以上';break;
                            }
                        }
                        if ($key == 'add_time'){
                            $item = date('Y-m-d H:i:s',$item);
                        }
                        if ($key == 'm_pics'){
                            $pic = explode(',',$item);
//                            $item = 'http://localhost:8080/public'.DS.'uploads/'.$pic[0];
                            $item = 'http://www.lionpush.com/'.DS.'uploads/'.$pic[0];
//                            $item = ROOT_PATH.'public'.DS.'uploads'.$pic[0];
                        }
                    }
                }
            }
        }
        echo json_encode(array('code'=>40000,'data'=>$messages));die;
    }
    public function uploadPic(){
        $file =request()->file("files");
        $info = $file->validate(['size'=>5242880,'ext'=>'jpg,png,gif'])->move(ROOT_PATH.'public'.DS.'uploads');
        if ($info) {
            $image = $info->getSaveName();
            $images = str_replace("\\","/",$image);
            echo  json_encode(array('msg'=>'上传成功','path'=>$images));die;
            $this->success('上传成功！');
        }else{
            $this->error('上传失败！');
        }
    }
    public function pushNews(){
        $params = input('post.');
        if ($params){
            $insertData = array();
            foreach ($params as $key => &$param){
                $param = htmlspecialchars($param,ENT_COMPAT);
                if ($key == 'u_token'){
                    $user = Db::name('user_token')->field('user_id')->where(array('token'=>$param))->find();
                    if (!$user || $user['user_id'] != session('user_id')){
                        session(null);
                        echo json_encode(array('code'=>40001,'msg'=>'用户登录状态有误!'));die;
                    }
                } else {
                    $insertData['m_'.$key] = $param;
                }
            }
            if ($insertData){
                $insertData['add_time'] = time();
                $insertData['uid'] = session('user_id');
                $res = Db::name('messages')->insert($insertData);
                if ($res !== false){
                    echo json_encode(array('code'=>40000,'msg'=>'发布成功!'));die;
                }
            }
        }
        echo json_encode(array('code'=>40001,'msg'=>'发布失败!'));
    }
}