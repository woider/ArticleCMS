<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use \think\Route;

/* 后台访问路由 */
Route::rule('admin', 'admin/Index/index');
Route::rule('login', 'admin/Login/index');
Route::rule('logout', 'admin/Index/logout');
Route::rule('login/check', 'admin/Login/check');
/* 异步访问路由 */
Route::post([
    'update_personal_infomation' => 'admin/Ajax/updatePersonalInfomation',
    'update_personal_password' => 'admin/Ajax/updatePersonalPassword',
    'root_create_user' => 'admin/Ajax/rootCreateUser',
    'ajax_upload_image' => 'admin/Ajax/ajaxUploadImage',
    'save_article_data' => 'admin/Ajax/saveArticleData',
    'query_article_list' => 'admin/Ajax/queryArticleList',
    'query_article_data' => 'admin/Ajax/queryArticleData',
    'query_article_check' => 'admin/Ajax/queryArticleCheck',
    'query_auditor_info' => 'admin/Ajax/QueryAuditorInfo',
    'check_article_status' => 'admin/Ajax/checkArticleStatus',
    'query_article_admin' => 'admin/Ajax/queryArticleAdmin',
    'update_admin_info' => 'admin/Ajax/updateAdminInfo',
    'query_exhibit_list' => 'admin/Ajax/queryExhibitList',
    'delete_exhibit_item' => 'admin/Ajax/deleteExhibitItem',
    'save_exhibit_data' => 'admin/Ajax/saveExhibitData',
]);
/* 文章预览路由 */
Route::rule([
    ['preview/article', 'admin/Preview/article', ['method' => 'post']],
    ['preview/:id', 'admin/Preview/article', ['method' => 'get']],
]);
/* 网站访问路由 */
Route::rule('', 'index/Index/index');
Route::rule('guokr', 'index/Index/plates?plate=guokr');
Route::rule('zhihu', 'index/Index/plates?plate=zhihu');
Route::rule('douban', 'index/Index/plates?plate=douban');
Route::rule('loading/:page/[:belong]', 'index/Index/loadingList');
Route::rule('commend/:id', 'index/Index/getCommend');
Route::rule('article/:id', 'index/Index/article');
