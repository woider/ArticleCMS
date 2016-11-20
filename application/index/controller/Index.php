<?php
namespace app\index\controller;


class Index extends \think\Controller
{
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

    public function getCommend($id)
    {
        $json = array();
        $model = \think\Loader::model('Article');
        /* 根据关键字查找 */
        $keyword = $model->where(['id' => $id, 'status' => 1])->value('keyword');
        foreach (explode('、', $keyword) as $keyword) {
            $result = $model->searchKeyword($keyword);
            foreach ($result as $item) {
                if ($item['id'] != $id && !in_array($item, $json)) {
                    array_push($json, $item);
                }
            }
        }
        //检查查找结果
        if (count($json) > 3) {
            return json_encode(array_slice($json, 0, 4));
        }
        /* 根据分类查找 */
        $belong = $model->where(['id' => $id, 'status' => 1])->value('belong');
        $result = $model->sortArticle($belong);
        foreach ($result as $item) {
            if ($item['id'] != $id && !in_array($item, $json)) {
                array_push($json, $item);
            }
        }
        //检查查找结果
        if (count($json) > 3) {
            return json_encode(array_slice($json, 0, 4));
        }
        /* 查找剩余文章 */
        $result = $model->randomArticle($belong);
        foreach ($result as $item) {
            array_push($json, $item);
        }
        return json_encode(array_slice($json, 0, 4));

    }
}
