<?php

namespace app\admin\controller;


class Preview extends \think\Controller
{
    /**
     * 文章预览
     * @param null $id
     * @return mixed
     */
    public function article($id = null)
    {
        if (empty($id)) {
            $article = $this->articlePost();
        } else {
            $article = $this->articleGet($id);
        }
        if (empty($article)) {
            throw new \think\exception\HttpException(404);
        }
        $this->assign('article', $article);
        return $this->fetch();
    }

    /**
     * 表单获取预览数据
     * @return array
     */
    private function articlePost()
    {
        $source = request()->post('source');
        if (preg_match('/guokr/', $source)) {
            $belong = 1;
        } else if (preg_match('/zhihu/', $source)) {
            $belong = 2;
        } else if (preg_match('/douban/', $source)) {
            $belong = 3;
        }
        return [
            'title' => request()->post('title'),
            'abstract' => request()->post('abstract'),
            'content' => request()->post('content'),
            'author' => request()->post('author'),
            'date' => request()->post('date'),
            'image' => request()->post('image'),
            'belong' => $belong,
        ];
    }

    /**
     * 数据库获取预览数据
     * @param $id
     * @return null
     */
    private function articleGet($id)
    {
        /* 检查文章预览权限 */
        $editor_id = \think\Loader::model('Article')->getEditorId($id);
        $editor = \think\Loader::model('User')->findUserById($editor_id);
        if (empty($editor_id)) return null;
        if (empty($editor)) return null;
        if (!\think\Session::has('admin')) return null;
        if (session('admin')['rank'] > $editor['rank']) return null;
        $article = \think\Loader::model('Article')->findArticleById($id);
        return $article;
    }
}