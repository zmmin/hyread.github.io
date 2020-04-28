<?php
namespace app\index\controller;

use think\Request;
use app\index\model\Article as ArticleModel;
use think\Session;
use think\Exception;
use think\controller;

class Uploader extends Base
{
    //渲染界面
    public function fileUpload()
    {
        return $this -> view -> fetch();
    }

    /**
     * 1、获取图片并且保存到服务器指定的目录下
     * 2、生成图片的URL访问路径，并返回
     * */
    /*public function fileUpload(Request $request){
        try{
            //获取图片对象
            $filetemp = $request->file('file');

            //存放服务器上地址
            $serverFile = $filetemp->move(ROOT_PATH.'/public/upload/image/single');

            //访问地址
            $imageUrl = 'http://'.$_SERVER['HTTP_HOST'].str_replace(ROOT_PATH.'/public', '', $serverFile->getPathname());

            $ajaxJson['success'] = true;
            $ajaxJson['msg'] = $imageUrl;
        }catch(Exception $e){
            $ajaxJson['success'] = false;
        }


        return json_encode($ajaxJson);
    }*/

    public function upload(Request $request){
        $files = request()->file('file');//TP5自带接受文件的方法
        if($files){
            $info = $files->move(ROOT_PATH . 'public' . DS . 'uploads'); //把它移入到指点目录
            if($info){
                return json_encode($info->getSaveName());//如果上传成功返回json类型的图片地址（适用多图）
            }else{
                echo $files->getError();
            }
        }
    }

}
