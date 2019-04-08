<?php
namespace app\member\controller;
use think\Db;

class Msg extends MCommon {
    protected $user = false;
    public function _initialize()
    {
        $user_token = htmlspecialchars($_POST['user_token'],ENT_QUOTES);
        $this->user = $this->getUser($user_token);
        if (!session('user_id') || $this->user['user_id'] != session('user_id')){
            echo json_encode(array('code'=>40001,'msg'=>'用户登录状态有误!'));die;
        }
    }
    public function index(){
        if ($this->user){
            $messages = Db::name('messages')->where(array('uid'=>$this->user['user_id']))->order('id desc')->select();
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
                            $item = 'http://www.lionpush.com/'.DS.'uploads/'.$pic[0];
                        }
                    }
                }
            }
        }
        echo json_encode(array('code'=>40000,'data'=>$messages));die;
    }

    /**
     * 上传图片
     */
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

    /**
     * 发布消息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
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

    /**
     * 编辑消息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editNews(){
        $news_id = htmlspecialchars($_POST['id'],ENT_QUOTES);
        $news_infos = Db::name('messages')->field('id,m_title,m_service,m_kind,m_area,m_invest_money,m_brand_name,m_company,m_description,m_pics,m_weixin,m_contactuser,m_tel')->where(array('id'=>$news_id,'uid'=>$this->user['user_id']))->find();
        if ($news_infos){
            foreach ($news_infos as $key => &$info){
                if ($key == 'm_pics'){
                    $info = explode(',',$info);
                    foreach ($info as &$pic){
                        $pic = 'http://www.lionpush.com'.DS.'uploads/'.$pic;
                    }
                }
            }
            echo json_encode(array('code'=>40000,'data'=>$news_infos));die;
        }
        echo json_encode(array('code'=>40001,'msg'=>'编辑失败!'));
    }
}