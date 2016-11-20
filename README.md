Article CMS
===============

零度文摘是基于Bootstrap和ThinkPHP搭建的多元资讯网站，其内容来源于果壳、知乎、豆瓣。**文章版权属于原网站**，本项目仅用于交流学习，切勿用作商业用途。


主要特性
----
 - 用户分为四个等级，包括超级用户、管理员、审核员、编辑
 - ROOT用户是唯一的超级用户，并且只能通过数据库直接创建
 - 没有任何人能冻结ROOT账号，审核ROOT用户发布的文章
 - 后台管理相关操作全部采用异步方式，操作中切记不要刷新页面
 - 管理员可以冻结或解冻账号，冻结的账号不能登录
 - 用户创建的文章必须通过其他更高级别的管理员审核
 - 未通过审核的文章可再次编辑发布，但需要再次审核
 - 高级用户可以晋升低价用户等级，但是不能超过高级用户本身
 - 文章内容采用HTML格式编辑，后台会自动过滤JavaScript代码

 
开发环境
----

 - 操作系统：Windows 7
 - 开发环境：WampServer
 - 编辑工具：PhpStorm IDE
 - 前端框架：Bootstrap 3.2
 - 后端框架：ThinkPHP 5.0

数据库
---

```
cms                     数据库
│  
├─user                  用户表
│  │
│  ├─id                 用户ID
│  ├─username           用户名
│  ├─password           密码
│  ├─realname           真实姓名
│  ├─email              邮箱地址
│  ├─rank               级别
│  ├─status             状态
│  ├─last_login_time    上次登录时间
│  └─last_login_ip      上次登录IP
│  
├─article               文章表
│  │
│  ├─id                 文章ID
│  ├─title              标题
│  ├─keyword            关键字
│  ├─image              插图
│  ├─abstract           描述
│  ├─author             作者
│  ├─source             来源
│  ├─date               日期
│  ├─content            内容
│  ├─belong             分类
│  ├─status             状态
│  ├─amount             浏览量
│  ├─editor_id          编辑者ID
│  ├─auditor_id         审核者ID
│  ├─create_time        创建时间
│  └─update_time        修改时间
│  
└─exhibit               首页展示表
   ├─id                 展示ID
   ├─article_id         文章ID
   ├─picture            展示图片
   └─create_time        创建时间
```

目录结构
----

```
application                 应用目录（非部署目录）
├─admin                     后台管理模块
│  │
│  ├─controller
│  │  ├─Ajax.php            异步请求控制器
│  │  ├─Index.php           后台程序控制器
│  │  ├─Login.php           后台登录控制器
│  │  └─Preview.php         文章预览控制器
│  │
│  ├─model
│  │  ├─Article.php         文章模型
│  │  └─User.php            用户模型
│  │
│  ├─validate
│  │  └─User.php            用户信息验证器
│  │
│  └─view
│     ├─index
|     │  └─admin.html       后台主界面
│     │
│     ├─login           
|     │  └─index.html       登录界面模板
│     │
│     ├─module          
|     │  ├─admin.html       用户管理面板
|     │  ├─check.html       文章审核面板
|     │  ├─edit.html        内容编辑面板
|     │  ├─home.html        主页推荐面板
|     │  ├─list.html        文章列表面板
|     │  └─logon.html       账号创建面板
│     │
│     └─preview         
|        └─article.html     文章预览界面
│
├─index                     网站前台模块
│  │
│  ├─controller
│  │  ├─Article.php         文章展示控制器
│  │  └─Index.php           网站首页控制器
│  │
│  ├─model
│  │  └─Article.php         文章模型
│  │
│  └─view
│     ├─article
│     │  └─index.html       文章展示模板
│     │
│     ├─index
│     │  ├─index.html       网站首页模板
│     │  └─plates.html      栏目分类模板
│     │
│     └─module          
│        ├─article.html     文章内容模块
│        ├─article_list     文章列表模块
│        ├─carousel.html    文章轮播模块
│        ├─commend.html     文章推荐模块
│        └─ranking.html     文章排行模块
│
├─config.php                应用配置
├─database.php              数据库配置
└─route.php                 路由配置
```



部署指导
----

 1. 在 `httpd.conf` 文件中设置网站根目录为 `ArticleCMS/public/`
 2. 在 `phpMyAdmin` 中创建数据库并导入 `cms_struct.sql` 文件
 3. 在 `application/database.php` 中设置数据库连接参数
 4. 在 `applicati/config.php` 中设置 `'app_debug' => false`
 5. 在 `exception_tmpl` 属性中配置错误页面模板
 
> 部署完成后务必以root用户身份登录后台（默认密码root）更改默认密码。
