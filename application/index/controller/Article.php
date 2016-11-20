<?php

namespace app\index\controller;


class Article extends \think\Controller
{
    /**
     * 文章详情页面
     * @param $id
     * @return mixed
     * @throws \think\Exception
     */
    public function index($id)
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
     * 获取文章推荐
     * @param $id
     * @return string
     */
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