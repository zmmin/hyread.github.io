<?php
namespace app\index\controller;
use app\index\model\Article as ArticleModel;
use think\Request;
use think\Db;

class Article extends Base
{

    public function  articleAdd()
    {

    }

    //渲染文章信息列表
    public function  articleList(Request $request)
    {

        $map = [];

        $keywords = $request -> param('keywords');

        if(!empty($keywords)) {
            $map['article_name'] = ['like', '%' . $keywords . '%'];

            $articleList = Db::table('article')
                ->where($map)
                ->order('article_id', 'desc')
                ->paginate(10);
        }else {
            //获取所有数据
            $articleList = ArticleModel::paginate(10);
            //获取记录数量
            $count = ArticleModel::count();
        }

        $this -> view -> assign('articleList', $articleList);
        $this -> view -> assign('count', $count);

        return $this -> view -> fetch('article_list');
    }


}