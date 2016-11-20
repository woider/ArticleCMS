<?php

namespace app\admin\controller;


class Ajax extends \think\Controller
{

    /**
     * 更改用户信息
     * @return bool
     */
    public function updatePersonalInfomation()
    {
        $request = \think\Request::instance();
        /* 更新数据库信息 */
        $data['realname'] = $request->post('realname');
        $data['email'] = $request->post('email');
        try {
            \think\Loader::model('User')
                ->save($data, ['id' => session('admin')['id']]);
        } catch (\Exception $e) {
            return false;
        }
        /* 更新Session信息 */
        $admin = session('admin');
        $admin['realname'] = $data['realname'];
        $admin['email'] = $data['email'];
        session('admin', $admin);
        return true;
    }

    /**
     * 更新个人密码
     * @return string
     */
    public function updatePersonalPassword()
    {
        $request = \think\Request::instance();
        $password = $request->post('new_password');
        /* 校检原密码 */
        $result = \think\Loader::model('User')->where([
            'id' => session('admin')['id'],
            'password' => sha1($request->post('old_password'))
        ])->find();
        if (empty($result)) {
            return json_encode([
                'success' => false,
                'message' => '原始密码错误',
            ]);
        }
        /* 校检新密码 */
        if (!preg_match('/^[0-9a-zA-Z!@#$%^&*()]{4,16}$/', $password)) {
            return json_encode([
                'success' => false,
                'message' => '密码格式不正确',
            ]);
        }
        /* 更新密码信息 */
        try {
            \think\Loader::model('User')
                ->save(['password' => sha1($password)], ['id' => session('admin')['id']]);
            return json_encode([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'message' => '密码修改失败',
            ]);
        }
    }

    /**
     * root创建用户
     * @return bool
     */
    public function rootCreateUser()
    {
        $data = [
            'username' => request()->post('username'),
            'password' => request()->post('password'),
            'realname' => request()->post('realname'),
            'email' => request()->post('email'),
        ];
        /* 验证身份权限 */
        if (session('admin')['rank'] != 0) {
            return false;
        }
        /* 验证数据合法性 */
        if (!\think\Loader::validate('User')->check($data)) {
            return false;
        }
        /* 存储新用户信息 */
        try {
            return \think\Loader::model('User')->createUser($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 异步上传图片
     * @return mixed|null
     */
    public function ajaxUploadImage()
    {
        $file = request()->file('image');
        /* 检查上传文件 */
        $file->validate([
            'size' => 1024000,
            'type' => 'image/jpeg',
        ]);
        if (!$file->check()) {
            return null;
        }
        /* 图像处理 */
        try {
            $upload = $file->move(ROOT_PATH . 'public/image');
            $image = \think\Image::open($upload->getRealPath());
            $image->thumb(620, 348, \think\Image::THUMB_CENTER)->save($upload->getRealPath());
        } catch (\Exception $e) {
            return null;
        }
        /* 拼接图像地址 */
        $path = str_replace(ROOT_PATH . 'public', '', $upload->getRealPath());
        return str_replace('\\', '/', $path);
    }

    /**
     * 储存文章信息
     * @return bool|int|string
     */
    public function saveArticleData()
    {
        /* 组装数据 */
        $article = [
            'title' => request()->post('title'),
            'date' => request()->post('date'),
            'image' => request()->post('image'),
            'source' => request()->post('source'),
            'author' => request()->post('author'),
            'keyword' => request()->post('keyword'),
            'abstract' => request()->post('abstract'),
            'content' => request()->post('content'),
            'status' => (session('admin')['rank'] == 0) ? 1 : 0,
        ];
        /* 文章分类 */
        if (preg_match('/guokr/', $article['source'])) {
            $article['belong'] = 1;
        } else if (preg_match('/zhihu/', $article['source'])) {
            $article['belong'] = 2;
        } else if (preg_match('/douban/', $article['source'])) {
            $article['belong'] = 3;
        } else {
            return false;
        }
        $article['editor_id'] = session('admin')['id'];
        /* 返回ID信息 */
        try {
            //文章查重
            $id = \think\Loader::model('Article')->findArticleId($article['source']);
            if (empty($id)) {
                //创建文章
                if (session('admin')['rank'] == 0) $article['auditor_id'] = 0;
                return \think\Loader::model('Article')->createArticle($article);
            }
            if (!\think\Db::table('article')->where(['editor_id' => $article['editor_id'], 'id' => $id])->value('id')) {
                return 0;//文章存在时检查是否为当前用户创建
            } else {
                //更新文章
                $article['auditor_id'] = null;//重置审查者信息
                return (\think\Loader::model('Article')->updateArticle($id, $article) == 1) ? $id : false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 查询文章信息
     * @return null|string
     */
    public function queryArticleList()
    {
        /* 获取相关数据 */
        $editor = session('admin')['id'];
        $page = request()->post('page');
        $sort = request()->post('sort');
        $order = request()->post('order');
        $search = request()->post('search');
        $query = new \think\db\Query();
        if (empty($page) || !is_numeric($page)) {
            return null;
        }
        /* 组装查询语句 */
        $query->table('article')->field(['id', 'title', 'belong', 'create_time', 'amount', 'status'])->page($page, 10);
        if (!empty($sort)) {
            $query->where(['editor_id' => $editor, 'belong' => $sort])->order('update_time DESC');
        } else if (!empty($order)) {
            $query->where(['editor_id' => $editor])->order($order);
        } else if (!empty($search)) {
            $query->where([
                'editor_id' => $editor,
                'title' => array('like', '%' . $search . '%'),
            ])->order('update_time DESC');
        } else {
            $query->where(['editor_id' => $editor])->order('update_time DESC');
        }
        /* 数据结果处理 */
        $result = \think\Db::select($query);
        $json = array();
        foreach ($result as $item) {
            $article = [
                'id' => $item['id'],
                'belong' => $item['belong'],
                'title' => $item['title'],
                'time' => date('m-d H:i', $item['create_time']),
                'amount' => $item['amount'],
                'status' => $item['status'],
            ];
            array_push($json, $article);
        }
        return json_encode($json);
    }

    /**
     * 查询文章数据
     * @return string
     */
    public function queryArticleData()
    {
        $id = request()->post('id');
        $editor = session('admin')['id'];
        $json = \think\Db::table('article')
            ->field(['title', 'date', 'image', 'source', 'author', 'keyword', 'abstract', 'content'])
            ->where(['id' => $id, 'editor_id' => $editor])->find();
        return json_encode($json);
    }

    /**
     * 文章审核数据
     * @return null|string
     */
    public function queryArticleCheck()
    {
        /* 获取相关数据 */
        $rank = session('admin')['rank'];
        $page = request()->post('page');
        $title = request()->post('title');
        $keyword = request()->post('keyword');
        $editor = request()->post('editor');
        $query = new \think\db\Query();
        if (empty($page) || !is_numeric($page)) {
            return null;
        }
        /* 组装查询语句 */
        $field = ['a.id', 'a.title', 'a.belong', 'a.create_time', 'a.status', 'a.auditor_id', 'u.username', 'u.realname'];
        $query->table('article')->alias('a')
            ->join('user u', 'u.id = a.editor_id')
            ->field($field)->page($page, 10)
            ->order('a.update_time DESC');
        if (!empty($title)) {
            $query->where([
                'u.rank' => array('>', $rank),
                'a.title' => array('like', '%' . $title . '%'),
            ]);
        } else if (!empty($keyword)) {
            $query->where([
                'u.rank' => array('>', $rank),
                'a.keyword' => array('like', '%' . $keyword . '%'),
            ]);
        } else if (!empty($editor)) {
            $query->where([
                'u.rank' => array('>', $rank),
                'u.realname' => array('like', '%' . $editor . '%'),
            ]);
        } else {
            $query->where(['u.rank' => array('>', $rank)]);
        }
        /* 数据结果处理 */
        $result = \think\Db::select($query);
        $json = array();
        foreach ($result as $item) {
            $article = [
                'id' => $item['id'],
                'belong' => $item['belong'],
                'title' => $item['title'],
                'time' => date('Y-m-d', $item['create_time']),
                'status' => $item['status'],
                'username' => $item['username'],
                'realname' => $item['realname'],
                'auditor_id' => $item['auditor_id'],
            ];
            array_push($json, $article);
        }
        return json_encode($json);
    }

    /**
     * 查询审核者信息
     * @return null|string
     */
    public function QueryAuditorInfo()
    {
        $id = request()->post('id');
        if (!\think\Session::has('admin') || session('admin')['rank'] > 2) return null;
        $json = \think\Db::table('user')->field(['username', 'realname', 'email'])->where('id', $id)->find();
        return json_encode($json);
    }

    /**
     * 修改文章状态
     * @return null|string
     * @throws \think\Exception
     */
    public function checkArticleStatus()
    {
        $id = request()->post('id');
        $pass = request()->post('pass');
        if (empty($id) || !is_numeric($id)) return null;
        /* 检查审查权限 */
        $editor_id = \think\Loader::model('Article')->getEditorId($id);
        if (empty($editor_id)) return null;
        $editor = \think\Loader::model('User')->findUserById($editor_id);
        if (session('admin')['rank'] >= $editor['rank']) return null;
        /* 更新审查结果 */
        $result = \think\Db::table('article')->where(['id' => $id, 'status' => 0])
            ->update([
                'auditor_id' => session('admin')['id'],
                'status' => ($pass == 'true') ? 1 : -1,
            ]);
        if (empty($result)) {
            return null;
        } else {
            //返回审查者信息
            return json_encode([
                'username' => session('admin')['username'],
                'realname' => session('admin')['realname'],
                'email' => session('admin')['email'],
            ]);
        }
    }

    /**
     * 查询用户数据
     * @return null|string
     */
    public function queryArticleAdmin()
    {
        /* 获取相关数据 */
        $rank = session('admin')['rank'];
        $page = request()->post('page');
        $username = request()->post('username');
        $realname = request()->post('realname');
        $email = request()->post('email');
        $query = new \think\db\Query();
        if (empty($page) || !is_numeric($page)) {
            return null;
        }
        /* 组装查询语句 */
        $query->table('user')->page($page, 10)->order('rank, last_login_time DESC')
            ->field(['id', 'username', 'realname', 'email', 'rank', 'status', 'last_login_ip', 'last_login_time',]);
        if (!empty($username)) {
            $query->where([
                'rank' => array('>', $rank),
                'username' => array('like', '%' . $username . '%'),
            ]);
        } else if (!empty($realname)) {
            $query->where([
                'rank' => array('>', $rank),
                'realname' => array('like', '%' . $realname . '%'),
            ]);
        } else if (!empty($email)) {
            $query->where([
                'rank' => array('>', $rank),
                'email' => array('like', '%' . $email . '%'),
            ]);
        } else {
            $query->where(['rank' => array('>', $rank),]);
        }
        /* 数据结果处理 */
        $result = \think\Db::select($query);
        $json = array();
        foreach ($result as $item) {
            $article = [
                'id' => $item['id'],
                'username' => $item['username'],
                'realname' => $item['realname'],
                'email' => $item['email'],
                'rank' => $item['rank'],
                'status' => $item['status'],
                'login_ip' => $item['last_login_ip'],
                'login_time' => date('Y-m-d H:i:s', $item['last_login_time']),
            ];
            array_push($json, $article);
        }
        return json_encode($json);
    }

    /**
     * 更新用户信息
     * @return null|string
     */
    public function updateAdminInfo()
    {
        $id = request()->post('id');
        $check = request()->post('check');
        if (empty($id) || !is_numeric($id)) return null;
        /* 检查审核权限 */
        $user = \think\Loader::model('User')->findUserById($id);
        if (session('admin')['rank'] >= $user['rank']) return null;
        /* 更改用户状态 */
        if ($check == 'true') {
            $result = \think\Db::table('user')->where('id', $id)
                ->setField('status', ($user['status'] + 1) % 2);
        }
        /* 用户级别晋升 */
        if ($check == 'false') {
            if (session('admin')['rank'] == $user['rank'] - 1) return null;
            $result = \think\Db::table('user')->where(['id' => $id, 'status' => 1])
                ->setField('rank', $user['rank'] - 1);
        }
        /* 返回用户信息 */
        if (!empty($result) && $check == 'true') {
            return json_encode(['status' => ($user['status'] + 1) % 2]);
        }
        if (!empty($result) && $check == 'false') {
            return json_encode(['rank' => $user['rank'] - 1]);
        }
    }

    /**
     * 查询推荐列表
     * @return null|string
     */
    public function queryExhibitList()
    {
        /* 获取相关数据 */
        $page = request()->post('page');
        $query = new \think\db\Query();
        if (session('admin')['rank'] != 0) return null;
        if (empty($page) || !is_numeric($page)) return null;
        /* 组装查询语句 */
        $query->table('exhibit')->alias('e')->field(['e.id', 'a.id as a_id', 'a.belong', 'a.title', 'e.create_time', 'a.amount'])
            ->join('article a', 'a.id = e.article_id')->page($page, 10)
            ->order('e.create_time DESC');
        /* 数据结果处理 */
        $result = \think\Db::select($query);
        $json = array();
        foreach ($result as $item) {
            $article = [
                'id' => $item['id'],
                'a_id' => $item['a_id'],
                'title' => $item['title'],
                'belong' => $item['belong'],
                'time' => date('Y-m-d', $item['create_time']),
                'amount' => $item['amount'],
            ];
            array_push($json, $article);
        }
        return json_encode($json);
    }

    /**
     * 删除推荐条目
     * @return bool
     * @throws \think\Exception
     */
    public function deleteExhibitItem()
    {
        if (session('admin')['rank'] != 0) return false;
        $id = request()->post('id');
        if (empty($id) || !is_numeric($id)) return false;
        return (\think\Db::table('exhibit')->delete($id)) ? true : false;
    }

    /**
     * 添加推荐条目
     * @return bool
     */
    public function saveExhibitData()
    {
        if (session('admin')['rank'] != 0) return false;
        $id = request()->post('id');
        $title = request()->post('title');
        $picture = request()->post('picture');
        if (\think\Db::table('article')->where(['id' => $id, 'status' => 1])->value('title') != $title) return false;
        $result = \think\Db::table('exhibit')->insert([
            'article_id' => $id,
            'picture' => $picture,
            'create_time' => time(),
        ]);
        return ($result == 1) ? true : false;
    }
}