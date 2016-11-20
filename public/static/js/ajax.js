$(function () {
    $("a[data-toggle=popover]").popover();
    //切换操作面板
    $(".cms-nav li").click(function () {
        $(this).addClass("active").siblings("li").removeClass("active");
        var index = $(this).index();
        $(".cms-panel .panel").eq(index).removeClass("hidden")
            .siblings(".panel").addClass("hidden");
    });
    //初始化操作面板
    $(".cms-nav li:first").addClass("active");
    $(".cms-panel .panel:first").removeClass("hidden");
    //初始化面板数据
    if ($(".cms-panel-home")[0]) {
        query_exhibit_list(1);
    }
    if ($(".cms-panel-admin")[0]) {
        query_article_admin(1, '', '');
    }
    if ($(".cms-panel-check")[0]) {
        query_article_check(1, '', '');
    }
    if ($(".cms-panel-list")[0]) {
        query_article_list(1, '', '');
    }
    //监听回车事件
    $(document).keydown(function () {
        if (event.keyCode == 13) {
            return false;
        }
    });
    //创建用户表单验证
    $(".cms-panel-logon input[type=text]").change(function () {
        var regex = $(this).attr("data-regex");
        regex = new RegExp(regex);
        $(this).parent().parent().removeClass("has-success has-error");
        $(this).next().removeClass("glyphicon-ok glyphicon-remove");
        if ($(this).val().match(regex)) {
            $(this).parent().parent().addClass("has-success");
            $(this).next().addClass("glyphicon-ok");
        } else {
            $(this).parent().parent().addClass("has-error");
            $(this).next().addClass("glyphicon-remove");
        }
    })
    //异步更新个人信息
    $("#update-personal-infomation-btn").click(function () {
        var $realname = $("#realname");
        var $email = $("#email");
        var alert = $("#cms-modal-setting .modal-footer .alert");
        alert.empty().removeClass("alert-warning alert-danger");
        if (!$realname.val().match(/^.{2,8}$/)) {
            alert.addClass("alert-warning");
            alert.text("请输入真实姓名");
            return false;
        }
        if (!$email.val().match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/)) {
            alert.addClass("alert-warning");
            alert.text("请输入真实邮箱");
            return false;
        }
        $.ajax({
            url: '/update_personal_infomation',
            type: 'POST',
            data: {"realname": $realname.val(), "email": $email.val()},
            success: function (result) {
                if (result) {
                    $("#cms-modal-setting").modal("hide");
                } else {
                    alert.addClass("alert-danger");
                    alert.text("信息更新失败");
                }
            }
        });
    });
    //修改密码
    $("#update-personal-password-btn").click(function () {
        var $password = $("#password");
        var $firstPwd = $("#password-first");
        var $secondPwd = $("#password-second");
        var alert = $("#cms-modal-modify .modal-footer .alert");
        alert.empty().removeClass("alert-warning alert-danger");
        if (!$password.val()) {
            alert.addClass("alert-warning");
            alert.text("未填写原始密码");
            return false;
        }
        if (!$firstPwd.val()) {
            alert.addClass("alert-warning");
            alert.text("未填写新密码");
            return false;
        }
        if ($firstPwd.val() != $secondPwd.val()) {
            alert.addClass("alert-warning");
            alert.text("两次输入的密码不同");
            return false;
        }
        $.ajax({
            url: '/update_personal_password',
            type: 'POST',
            data: {"old_password": $password.val(), "new_password": $firstPwd.val()},
            success: function (result) {
                result = JSON.parse(result);
                console.log(result)
                if (result.success) {
                    $("#cms-modal-modify").modal("hide");
                } else {
                    alert.addClass("alert-danger");
                    alert.text(result.message);
                }
            }
        });
    });
    //重置表单
    $("#reset-form-btn").click(function () {
        $(".cms-panel-logon input[type=text]").val("");
        $(".cms-panel-logon .has-feedback").removeClass("has-success has-error");
        $(".cms-panel-logon .form-control-feedback").removeClass("glyphicon-ok glyphicon-remove");
        $(".cms-panel-logon .panel-footer .alert").empty().removeClass("alert-success alert-danger");
    });
    //创建用户
    $("#create-user-btn").click(function () {
        var postData = {};
        var $alert = $(".cms-panel-logon .panel-footer .alert");
        $alert.empty().removeClass("alert-success alert-danger");
        postData.username = $("#newuser-username").val();
        postData.password = $("#newuser-password").val();
        postData.realname = $("#newuser-realname").val();
        postData.email = $("#newuser-email").val();
        $.ajax({
            url: '/root_create_user',
            type: 'POST',
            data: postData,
            success: function (result) {
                if (result) {
                    $alert.addClass("alert-success");
                    $alert.text("用户" + postData.username + "创建成功");
                } else {
                    $alert.addClass("alert-danger");
                    $alert.text("用户" + postData.username + "创建失败");
                }
            }
        });
    });
    //异步上传图片
    $("#upload-image-btn").click(function () {
        var $upload = $("#upload-image-file");
        if (!$upload.val()) {
            return false;
        }
        var formData = new FormData();
        formData.append("image", $upload.get(0).files[0]);
        $.ajax({
            url: '/ajax_upload_image',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (result) {
                if (result) {
                    $("#article-image").val(result);
                    $("#thumbnail-img").attr("src", result)
                        .parent().removeClass("hidden");
                }
            }
        });
        $upload.val(null);
    });
    //预览文章内容
    $("#preview-article-btn").click(function () {
        var article = get_article_data();
        if (!check_article_field(article)) {
            return false;
        }
        $(".cms-panel-edit form").submit();
    });
    //存储文章信息
    $("#save-article-btn").click(function () {
        var article = get_article_data();
        var $alert = $(".cms-panel-edit .panel-footer .alert");
        if (!check_article_field(article)) {
            return false;
        }
        $.ajax({
            url: '/save_article_data',
            type: 'POST',
            data: article,
            success: function (result) {
                if (result === false) {
                    $alert.text("文章发布失败").removeClass("alert-warning").addClass("alert-danger");
                } else if (result === 0) {
                    $alert.text("文章已经存在").removeClass("alert-warning").addClass("alert-danger");
                } else {
                    $alert.empty().removeClass("alert-warning alert-danger");
                    $(".cms-panel-edit form input,textarea").val(null);
                    $("#thumbnail-img").parent().addClass("hidden");
                    $("#publish-success-modal .modal-body")
                        .html("<strong>" + article.title + "</strong><p/>" + article.abstract + "</p>");
                    $("#publish-success-modal .modal-footer a").attr("href", "/preview/" + result);
                    $("#publish-success-modal").modal("show");
                    //更新相关列表信息
                    query_article_list(1, '', '');
                }
            }
        });
    });
    //查询结果分页
    $(".pagination li").click(function () {
        var $pagelink = $(this).parent();
        var page_type = $(this).parent().attr("data-page");
        var page_data = $(this).children("a").text();
        if (!isNaN(page_data)) {
            $(this).addClass("active").siblings("li").removeClass();
        }
        var page = $pagelink.children(".active").children("a").text();
        var index = $pagelink.children(".active").index();
        var $a = $pagelink.children("li").children("a");
        if (page_data == "«") {
            if (page > 1 && index > 1) {
                $pagelink.children("li").eq(index).removeClass();
                $pagelink.children("li").eq(index - 1).addClass("active");
            } else if (page > 1) {
                for (var i = 1; i < 6; i++) {
                    $a.eq(i).text(parseInt($a.eq(i).text()) - 1);
                }
            }
            if (page > 1) {
                page--;
            }
        } else if (page_data == "»") {
            if (index != 5) {
                $pagelink.children("li").eq(index).removeClass();
                $pagelink.children("li").eq(index + 1).addClass("active");
            } else {
                for (var i = 1; i < 6; i++) {
                    $a.eq(i).text(parseInt($a.eq(i).text()) + 1);
                }
            }
            page++;
        }
        if (page_type == "list") {
            query_article_list(page);
        }
        if (page_type == "check") {
            query_article_check(page);
        }
        if (page_type == "admin") {
            query_article_admin(page);
        }
        if (page_type == "home") {
            query_exhibit_list(page);
        }
    });
    //文章列表搜索
    $("#list-search-article-btn").click(function () {
        var search = $(this).siblings("input:text").val();
        if (!search) {
            query_article_list(1, '', '');
        } else {
            query_article_list(1, 'search', search);
        }
    });
    //审查列表搜索
    $("#check-search-article-btn").click(function () {
        var search = $(this).siblings("input:text").val();
        if (!search) {
            query_article_check(1, '', '');
        } else {
            var field = $(".cms-panel-check .form-group input:radio:checked").val();
            query_article_check(1, field, search);
        }
    });
    //用户列表搜索
    $("#admin-search-article-btn").click(function () {
        var search = $(this).siblings("input:text").val();
        if (!search) {
            query_article_admin(1, '', '');
        } else {
            var field = $(".cms-panel-admin .form-group input:radio:checked").val();
            query_article_admin(1, field, search);
        }
    });
    //发布推荐文章
    $("#publish-exhibit-btn").click(function () {
        var id = $("#exhibit-id").val();
        var title = $("#exhibit-title").val();
        var picture = $("#exhibit-picture").val();
        if (!(id && title && picture))return false;
        $.ajax({
            url: 'save_exhibit_data',
            type: 'POST',
            data: {"id": id, "title": title, "picture": picture},
            success: function (result) {
                if (result) {
                    query_exhibit_list(1);
                    $(".cms-panel-home .panel-heading .input-group input").val(null);
                }
            }
        });
    });
});
var listFilter = {"type": "", "param": ""};
var checkFilter = {"type": "", "param": ""};
var adminFilter = {"type": "", "param": ""};
//获取文章数据
function get_article_data() {
    var article = {};
    article.title = $("#article-title").val();
    article.date = $("#article-date").val();
    article.image = $("#article-image").val();
    article.source = $("#article-source").val();
    article.author = $("#article-author").val();
    article.keyword = $("#article-keyword").val();
    article.abstract = $("#article-abstract").val();
    article.content = $("#article-content").val();
    //处理文章内容数据
    var $handle = $("#handle-article-content");
    $handle.html(article.content);
    $handle.find("img").remove();
    $handle.find("a").attr("target", "_blank");
    article.content = $handle.html();
    $handle.empty();
    return article;
}
//验证文章字段
function check_article_field(article) {
    var $alert = $(".cms-panel-edit .panel-footer .alert");
    $alert.empty().removeClass("alert-danger").addClass("alert-warning");
    if (!article.title) {
        $alert.text("请输入标题");
    } else if (article.title.length > 32) {
        $alert.text("标题长度超过32个字符");
    } else if (!article.date) {
        $alert.text("日期未填写");
    } else if (!article.date.match(/^\d{4}-\d{2}-\d{2}$/)) {
        $alert.text("日期不合法");
    } else if (!article.image) {
        $alert.text("未上传封面");
    } else if (!article.source) {
        $alert.text("来源未填写");
    } else if (!article.source.match(/(guokr|zhihu|douban)+/)) {
        $alert.text("文章来源不正确");
    } else if (!article.author) {
        $alert.text("原文作者未填写");
    } else if (article.author.length > 32) {
        $alert.text("作者名过长");
    } else if (!article.keyword) {
        $alert.text("关键字未填写");
    } else if (article.keyword.length > 64) {
        $alert.text("关键字过于宽泛");
    } else if (!article.abstract) {
        $alert.text("请输入内容简介");
    } else if (!article.content) {
        $alert.text("请输入文章内容");
    } else {
        $alert.empty().removeClass("alert-warning alert-danger");
        return true;
    }
    return false;
}
//填充文章信息
function fill_article_list(json) {
    var $tbody = $(".cms-panel-list .panel-body table tbody");
    for (var i = 0; i < 10; i++) {
        var $tr = $tbody.children("tr").eq(i);
        $tr.children("td").html("&nbsp;");
        $tr.removeClass();
        if (json[i] != null) {
            $tr.children("td").eq(0).text(json[i].id);
            json[i].belong = (json[i].belong == 1) ? '果壳' : (json[i].belong == 2) ? '知乎' : '豆瓣';
            $tr.children("td").eq(1).text("【" + json[i].belong + "】" + json[i].title);
            $tr.children("td").eq(2).text(json[i].time);
            $tr.children("td").eq(3).text(json[i].amount);
            var html = "<a href='/preview/" + json[i].id + "' target='_blank' title='预览'><i class='glyphicon glyphicon-eye-open'></i></a>"
            html += "&nbsp;" + "<a href='javascript:void(0)' onclick='return update_article_data(" + json[i].id + ")' title='编辑'><i class='glyphicon glyphicon-edit'></i></a>"
            $tr.children("td").eq(4).html(html);
            if (json[i].status == 1) {
                $tr.addClass("success");
            } else if (json[i].status == -1) {
                $tr.addClass("danger");
            }
        }
    }
}
//查询文章列表
function query_article_list(page, type, param) {
    if (isNaN(page)) {
        return false;
    } else if (page == 1) {
        var $pag_li = $(".cms-panel-list .pagination li");
        for (var i = 1; i < 6; i++) {
            $pag_li.eq(i).children("a").text(i);
        }
        $pag_li.removeClass();
        $pag_li.eq(1).addClass("active");
    }
    if (type == undefined) {
        type = listFilter.type;
    } else {
        listFilter.type = type;
    }
    if (param == undefined) {
        param = listFilter.param;
    } else {
        listFilter.param = param;
    }
    var postData = {};
    postData["page"] = page;
    postData[type] = param;
    $.ajax({
        url: '/query_article_list',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            fill_article_list(json);
        }
    });
}
//修改文章内容
function update_article_data(id) {
    $.ajax({
        url: 'query_article_data',
        type: 'POST',
        data: {"id": id},
        dataType: 'json',
        success: function (result) {
            var article = JSON.parse(result);
            $("#article-title").val(article.title);
            $("#article-date").val(article.date);
            $("#article-image").val(article.image);
            $("#article-source").val(article.source);
            $("#article-author").val(article.author);
            $("#article-keyword").val(article.keyword);
            $("#article-abstract").val(article.abstract);
            $("#article-content").val(article.content);
            $("#thumbnail-img").attr("src", article.image).parent().removeClass("hidden");
            $(".cms-nav li:last").click();
        }
    });
}
//填充审查内容
function fill_article_check(json) {
    var $tbody = $(".cms-panel-check .panel-body table tbody");
    for (var i = 0; i < 10; i++) {
        var $tr = $tbody.children("tr").eq(i);
        $tr.children("td").html("&nbsp;");
        $tr.removeClass();
        if (json[i] != null) {
            $tr.children("td").eq(0).text(json[i].id);
            json[i].belong = (json[i].belong == 1) ? '果壳' : (json[i].belong == 2) ? '知乎' : '豆瓣';
            $tr.children("td").eq(1).text("【" + json[i].belong + "】" + json[i].title);
            $tr.children("td").eq(2).text(json[i].realname + "(" + json[i].username + ")");
            $tr.children("td").eq(3).text(json[i].time);
            var html = "<a href='/preview/" + json[i].id + "' target='_blank' title='预览'><i class='glyphicon glyphicon-eye-open'></i></a>"
            if (json[i].auditor_id == null) {
                html += "&nbsp;" + "<a href='javascript:void(0)' onclick='return check_article_status(" + i + "," + json[i].id + ",true)' title='通过'><i class='glyphicon glyphicon-ok'></i></a>";
                html += "&nbsp;" + "<a href='javascript:void(0)' onclick='return check_article_status(" + i + "," + json[i].id + ",false)' title='关闭'><i class='glyphicon glyphicon-remove'></i></a>";
            } else {
                html += "&nbsp;" + "<a href='javascript:void(0)' onclick='return query_auditor_info(" + i + "," + json[i].auditor_id + ")' title='审核信息'><i class='glyphicon glyphicon-user'></i></a>";
            }
            $tr.children("td").eq(4).html(html);
            if (json[i].status == 1) {
                $tr.addClass("success");
            } else if (json[i].status == -1) {
                $tr.addClass("danger");
            }
        }
    }
}
//查询审查列表
function query_article_check(page, type, param) {
    if (isNaN(page)) {
        return false;
    } else if (page == 1) {
        var $pag_li = $(".cms-panel-check .pagination li");
        for (var i = 1; i < 6; i++) {
            $pag_li.eq(i).children("a").text(i);
        }
        $pag_li.removeClass();
        $pag_li.eq(1).addClass("active");
    }
    if (type == undefined) {
        type = checkFilter.type;
    } else {
        checkFilter.type = type;
    }
    if (param == undefined) {
        param = checkFilter.param;
    } else {
        checkFilter.param = param;
    }
    var postData = {};
    postData["page"] = page;
    postData[type] = param;
    $.ajax({
        url: '/query_article_check',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            fill_article_check(json);
        }
    });
}
//查询审查者信息
function query_auditor_info(index, id) {
    $.ajax({
        url: '/query_auditor_info',
        type: 'POST',
        data: {"id": id},
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            var $td = $(".cms-panel-check .panel-body tbody tr").eq(index).children("td:last");
            if (result) {
                $td.children("a:last").remove();
                var html = "<a href='javascript:void(0)' data-container='body' data-toggle='popover'";
                html += "data-placement='top' data-html='true' data-trigger='focus'";
                html += "data-content='<strong>姓名: </strong>" + json.realname + "(" + json.username + ")";
                html += "<br><strong>邮箱: </strong>" + json.email + "' data-original-title='审核者信息'>";
                html += "<i class='glyphicon glyphicon-user'></i></a>";
                $td.append($(html)).children("a[data-toggle=popover]").popover().focus();
            }
        }
    });
}
//审查文章内容
function check_article_status(index, id, pass) {
    $.ajax({
        url: '/check_article_status',
        type: 'POST',
        data: {"id": id, "pass": pass},
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            if (json != null) {
                $current_row = $(".cms-panel-check .panel-body tbody tr").eq(index);
                $current_row.removeClass().addClass((pass ? "success" : "danger"));
                $current_row.children("td:last").empty();
                var html = "<a href='/preview/" + id + "' target='_blank' title='预览'><i class='glyphicon glyphicon-eye-open'></i></a>"
                html += "&nbsp;" + "<a href='javascript:void(0)' title='审查者信息' data-container='body' data-toggle='popover'";
                html += "data-placement='top' data-html='true' data-trigger='focus'";
                html += "data-content='<strong>姓名: </strong>" + json.realname + "(" + json.username + ")";
                html += "<br><strong>邮箱: </strong>" + json.email + "' data-original-title='审查者信息'>";
                html += "<i class='glyphicon glyphicon-user'></i></a>";
                $current_row.children("td:last").html(html);
                $current_row.children("td:last").children("a[data-toggle=popover]").popover();
            }
        }
    });
}
//填充用户列表
function fill_article_admin(json) {
    var $tbody = $(".cms-panel-admin .panel-body table tbody");
    for (var i = 0; i < 10; i++) {
        var $tr = $tbody.children("tr").eq(i);
        $tr.children("td").html("&nbsp;");
        $tr.removeClass();
        if (json[i] != null) {
            $tr.children("td").eq(0).text(json[i].id);
            $tr.children("td").eq(1).text(json[i].username);
            json[i].rank = (json[i].rank == 1) ? '管理' : (json[i].rank == 2) ? '审核' : '编辑';
            $tr.children("td").eq(2).text(json[i].rank);
            $tr.children("td").eq(3).text(json[i].realname);
            $tr.children("td").eq(4).text(json[i].email);
            var html = "<a href='javascript:void(0)' onclick='return update_admin_info(" + i + "," + json[i].id + ",false)' title='级别晋升'><i class='glyphicon glyphicon-chevron-up'></i></a>";
            html += "&nbsp;" + "<a href='javascript:void(0)' onclick='return update_admin_info(" + i + "," + json[i].id + ",true)' title='更改状态'><i class='glyphicon glyphicon-refresh'></i></a>";
            html += "&nbsp;" + "<a href='javascript:void(0)' title='登录信息' data-container='body' data-toggle='popover' data-placement='top' data-html='true' data-trigger='focus'";
            html += "data-content='<strong>时间: </strong>" + json[i].login_time + "<br><strong>地址: </strong>" + json[i].login_ip + "'><i class='glyphicon glyphicon-info-sign'></i></a>";
            $tr.children("td").eq(5).html(html);
            if (json[i].status == 1) {
                $tr.addClass("info");
            } else {
                $tr.addClass("warning");
            }
        }
    }
    $("a[data-toggle=popover]").popover();
}
//查询用户信息
function query_article_admin(page, type, param) {
    if (isNaN(page)) {
        return false;
    } else if (page == 1) {
        var $pag_li = $(".cms-panel-admin .pagination li");
        for (var i = 1; i < 6; i++) {
            $pag_li.eq(i).children("a").text(i);
        }
        $pag_li.removeClass();
        $pag_li.eq(1).addClass("active");
    }
    if (type == undefined) {
        type = adminFilter.type;
    } else {
        adminFilter.type = type;
    }
    if (param == undefined) {
        param = adminFilter.param;
    } else {
        adminFilter.param = param;
    }
    var postData = {};
    postData["page"] = page;
    postData[type] = param;
    $.ajax({
        url: '/query_article_admin',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            fill_article_admin(json);
        }
    });
}
//修改用户信息
function update_admin_info(index, id, check) {
    $.ajax({
        url: '/update_admin_info',
        type: 'POST',
        data: {"id": id, "check": check},
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            var $tr = $(".cms-panel-admin .panel-body table tbody tr");
            if (json.rank != null) {
                json.rank = (json.rank == 1) ? '管理' : (json.rank == 2) ? '审核' : '编辑';
                $tr.eq(index).children("td").eq(2).html(json.rank);
                $tr.eq(index).removeClass().addClass("success");
            }
            if (json.status != null) {
                $tr.eq(index).removeClass();
                if (json.status == 1) {
                    $tr.eq(index).addClass("info");
                } else {
                    $tr.eq(index).addClass("warning");
                }
            }
        }
    });
}
//填充推荐列表
function fill_exhibit_list(json) {
    var $tbody = $(".cms-panel-home .panel-body table tbody");
    for (var i = 0; i < 10; i++) {
        var $tr = $tbody.children("tr").eq(i);
        $tr.children("td").html("&nbsp;");
        if (json[i] != null) {
            $tr.children("td").eq(0).text(json[i].id);
            json[i].belong = (json[i].belong == 1) ? '果壳' : (json[i].belong == 2) ? '知乎' : '豆瓣';
            $tr.children("td").eq(1).text("【" + json[i].belong + "】" + json[i].title);
            $tr.children("td").eq(2).text(json[i].time);
            $tr.children("td").eq(3).text(json[i].amount);
            var html = "<a href='/preview/" + json[i].a_id + "' target='_blank'  title='预览'><i class='glyphicon glyphicon-eye-open'></i></a>";
            html += "&nbsp;" + "<a href='javascript:void(0)' onclick='return delete_exhibit_item(" + json[i].id + ")' title='删除'><i class='glyphicon glyphicon-trash'></i></a>";
            $tr.children("td").eq(4).html(html);
        }
    }
}
//查询推荐信息
function query_exhibit_list(page) {
    if (isNaN(page)) {
        return false;
    } else if (page == 1) {
        var $pag_li = $(".cms-panel-home .pagination li");
        for (var i = 1; i < 6; i++) {
            $pag_li.eq(i).children("a").text(i);
        }
        $pag_li.removeClass();
        $pag_li.eq(1).addClass("active");
    }
    $.ajax({
        url: '/query_exhibit_list',
        type: 'POST',
        data: {"page": page},
        dataType: 'json',
        success: function (result) {
            var json = JSON.parse(result);
            fill_exhibit_list(json);
        }
    });
}
//删除推荐数据
function delete_exhibit_item(id) {
    $.ajax({
        url: '/delete_exhibit_item',
        type: 'POST',
        data: {"id": id},
        success: function (result) {
            if (result) {
                var page = $(".cms-panel-home .panel-footer .pagination li[class=active]").children("a").text();
                query_exhibit_list(page);
            }
        }
    });
}
