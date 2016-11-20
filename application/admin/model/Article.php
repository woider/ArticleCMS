<?php

namespace app\admin\model;


class Article extends \think\Model
{
    protected $autoWriteTimestamp = true;

    /**
     * 创建文章
     * @param $article
     * @return mixed
     */
    public function createArticle($article)
    {
        $this->allowField(true)->data($article)->save();
        return $this->id;
    }

    /**
     * 根据ID更新文章
     * @param $id
     * @param $article
     * @return false|int
     */
    public function updateArticle($id, $article)
    {
        return $this->allowField(true)->save($article,['id'=>$id]);
    }

    /**
     * 查询文章ID
     * @param $source
     * @return mixed
     */
    public function findArticleId($source)
    {
        return $this->where('source', $source)->value('id');
    }

    /**
     * 获取创建者ID
     * @param $id
     * @return mixed
     */
    public function getEditorId($id)
    {
        return $this->where('id', $id)->value('editor_id');
    }


    /**
     * 根据ID获取文章
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function findArticleById($id)
    {
        return $this->where('id', $id)->find();
    }
}