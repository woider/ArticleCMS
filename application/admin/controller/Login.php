<?php

namespace app\admin\controller;


use think\Request;

class Login extends \think\Controller
{
    public function index()
    {
        $this->assign('admin', \think\Session::get('admin'));
        return $this->fetch();
    }

    /**
     * 异步登录验证方法
     */
    public function check()
    {
        $username = \think\Request::instance()->post('username');
        $password = \think\Request::instance()->post('password');
        $error = \think\Loader::model('User')->checkLoginError($username, $password);
        if (empty($error)) {
            $json['success'] = true;
            $json['message'] = '/admin';
        } else {
            $json['success'] = false;
            $json['message'] = $error;
        }
        return json_encode($json);
    }
}