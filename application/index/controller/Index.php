<?php
namespace app\index\controller;

use app\index\controller\Base;

class Index extends Base
{
    public function index()
    {
        //$this -> isLogin();
        $this->view->assign('title', '书友');
        return $this->view->fetch();   //渲染首页模板
    }

    public function home()
    {
        //$this -> isLogin();
        $this->view->assign('title', '书友');
        return $this->view->fetch();   //渲染模板
    }

    public function article_index()
    {
        //$this -> isLogin();
        $this->view->assign('title', '书友');
        return $this->view->fetch();   //渲染模板
    }
}