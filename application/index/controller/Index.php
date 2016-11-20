<?php
namespace app\index\controller;


class Index extends \think\Controller
{
    /**
     * 网站主页
     * @return mixed
     */
    public function index()
    {
        $model = \think\Loader::model('Article');
        //获取轮播图效果
        $this->assign('exhibits', $model->getExhibits());
        //获取文章排行榜
        $this->assign('rankings', $model->getRankings());
        //获取文章列表
        $this->assign('articles', $model->getArticles(1));
        return $this->fetch();
    }

    /**
     * 栏目分类
     * @param $plate
     * @return mixed
     */
    public function plates($plate)
    {
        /* 栏目分类 */
        switch ($plate) {
            case 'guokr':
                $belong = 1;
                break;
            case 'zhihu':
                $belong = 2;
                break;
            case 'douban':
                $belong = 3;
                break;
        }
        /* 获取内容 */
        //注册文章模块
        $this->assign('belong', $belong);
        $model = \think\Loader::model('Article');
        //获取文章排行榜
        $this->assign('rankings', $model->getRankings($belong));
        //获取文章列表
        $this->assign('articles', $model->getArticles(1, $belong));
        return $this->fetch();
    }

    /**
     * 文章详细内容
     * @param $id
     * @return mixed|null
     */
    public function article($id)
    {
        $model = \think\Loader::model('Article');
        $article = $model->findArticleById($id);
        if (empty($article)) {
            throw new \think\exception\HttpException(404);
        }
        //增加文章浏览次数
        \think\Db::table('article')->where(['id' => $id, 'status' => 1])->setInc('amount');
        $this->assign('article', $article);
        $belong = $article['belong'];
        $this->assign('rankings', $model->getRankings($belong));
        return $this->fetch();
    }

    /**
     * 异步获取文章数据
     * @param $page
     * @param null $belong
     * @return mixed
     */
    public function loadingList($page, $belong = null)
    {
        if ($belong == null) {
            $json = \think\Loader::model('Article')->getArticles($page);
        } else {
            $json = \think\Loader::model('Article')->getArticles($page, $belong);
        }
        return json_encode($json);
    }

}
