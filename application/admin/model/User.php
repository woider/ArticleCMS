<?php

namespace app\admin\model;


class User extends \think\Model
{
    /**
     * 检查登录错误
     * @param $username
     * @param $password
     */
    public function checkLoginError($username, $password)
    {
        /* 检查用户是否存在 */
        $result = $this->where(['username' => $username])->find();
        if (empty($result)) {
            return '用户不存在';
        }
        /* 检查密码是否匹配 */
        $result = $this->where(['username' => $username, 'password' => sha1($password)])->find();
        if (empty($result)) {
            return '密码错误';
        }
        /* 检查账号是否被冻结 */
        if ($result['status'] == 0) {
            return '账号已冻结';
        }
        $this->recordLoginInfo($result);
        return null;
    }

    /**
     * 记录登录信息
     * @param $id
     */
    private function recordLoginInfo($result)
    {
        /* 记录登录信息 */
        $this->get($result['id'])->isUpdate(true)->save([
            'last_login_ip' => request()->ip(),
            'last_login_time' => request()->time(),
        ]);
        /* 记录用户信息 */
        $admin = [
            'id' => $result['id'],
            'username' => $result['username'],
            'realname' => $result['realname'],
            'email' => $result['email'],
            'rank' => $result['rank'],
        ];
        \think\Session::set('admin', $admin);
    }

    /**
     * 创建用户
     * @param $data
     * @return false|int
     */
    public function createUser($data)
    {
        return $this->isUpdate(false)
            ->save([
                'username' => $data['username'],
                'password' => sha1($data['password']),//对原始密码加密
                'realname' => $data['realname'],
                'email' => $data['email'],
                'rank' => 3,// 默认为普通用户
                'status' => 1,
            ]);
    }

    /**
     * 查找用户
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function findUserById($id)
    {
        return $this->where('id', $id)->find();
    }
}