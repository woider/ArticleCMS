<?php

namespace app\admin\validate;


class User extends \think\Validate
{
    protected $rule = [
        'username' => ['regex' => '/^\w{4,12}$/'],
        'password' => ['regex' => '/^[0-9a-zA-Z!@#$%^&*()]{4,16}$/'],
        'realname' => 'length:2,8',
        'email' => 'email',
    ];

}
