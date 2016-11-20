<?php

namespace app\index\model;


class Article extends \think\Model
{
    /**
     * 获取文章展示列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getExhibits()
    {
        return \think\Db::table('exhibit')->alias('e')
            ->field(['a.id', 'a.title', 'a.abstract', 'e.picture'])
            ->join('article a', 'a.id = e.article_id')
            ->order('e.create_time DESC')
            ->limit(5)->select();
    }

    /**
     * 获取文章排行
     * @param null $belong
     * @return mixed
     */
    public function getRankings($belong = null)
    {
        $query = new \think\db\Query();
        $query->table('article')->field(['id', 'title', 'abstract'])
            ->order('amount DESC')->limit(7);
        if ($belong == null) {
            $query->where([
                'status' => 1,
                'create_time' => array('>', time() - 604800),
            ]);
        } else {
            $query->where([
                'status' => 1,
                'belong' => $belong,
                'create_time' => array('>', time() - 604800),
            ]);
        }
        return \think\Db::select($query);
    }

    /**
     * 获取文章列表
     * @param $page
     * @param null $belong
     * @return mixed
     */
    public function getArticles($page, $belong = null)
    {
        $query = new \think\db\Query();
        $query->table('article')->field(['id', 'title', 'image', 'abstract', 'author', 'date', 'amount'])
            ->order('create_time DESC')->page($page, 5);
        if ($belong == null) {
            $query->where(['status' => 1]);
        } else {
            $query->where(['status' => 1, 'belong' => $belong]);
        }
        return \think\Db::select($query);
    }

    /**
     * 根据ID获取文章
     * @param $id
     * @return mixed
     */
    public function findArticleById($id)
    {
        return $this->field(['id', 'title', 'author', 'image', 'abstract', 'date', 'content', 'belong'])
            ->where(['id' => $id, 'status' => 1])->find();
    }

    /**
     * 搜索关键字
     * @param $keyword
     * @return mixed
     */
    public function searchKeyword($keyword)
    {
        return $this->where(['keyword' => array('like', '%' . $keyword . '%'), 'status' => 1])
            ->field(['id', 'title', 'image', 'abstract'])
            ->order(['amount' => 'DESC'])->limit(5)->select();
    }

    /**
     * 随机搜索分类
     * @param $belong
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function sortArticle($belong)
    {
        $count = $this->where(['status' => 1, 'belong' => $belong])->count();
        $random = ($count > 5) ? mt_rand(0, $count - 5) : 0;
        return $this->where(['status' => 1, 'belong' => $belong])
            ->field(['id', 'title', 'image', 'abstract'])
            ->order(['create_time' => 'DESC'])
            ->limit($random, 5)->select();
    }

    /**
     * 搜索剩余分类
     * @param $id
     * @param $belong
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function randomArticle($belong)
    {
        $count = $this->where(['status' => 1, 'belong' => array('<>', $belong)])->count();
        $random = ($count > 4) ? mt_rand(0, $count - 4) : 0;
        return $this->where(['status' => 1, 'belong' => array('<>', $belong)])
            ->field(['id', 'title', 'image', 'abstract'])
            ->order(['create_time' => 'DESC'])
            ->limit($random, 4)->select();
    }
}