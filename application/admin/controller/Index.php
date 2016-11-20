<?php

namespace app\admin\controller;


class Index extends \think\Controller
{
    public function index()
    {
        /* 用户身份过滤 */
        if (!\think\Session::has('admin')) {
            $this->redirect(url('admin/Login/index'));
            return null;
        }
        $this->assign([
            'admin' => \think\Session::get('admin'),
        ]);
        return $this->fetch('admin');
    }

    public function logout(){
        //清除Session
        \think\Session::delete('admin');
        //重定向至登录界面
        $this->redirect(url('admin/Login/index'));
    }
}