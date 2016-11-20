$(function () {
    var li_order = 1;
    var page = 1, lock = false;
    get_commend();//启动异步加载
    //判断模块分类
    var belong = $("#zero-navbar .nav li.active").children("a").attr("href");
    if (belong == "/guokr") {
        belong = 1;
    } else if (belong == "/zhihu") {
        belong = 2;
    } else if (belong == "/douban") {
        belong = 3;
    } else {
        belong = "";
    }
    //文章排行样式渲染
    $("ol.article-ranking li").each(function () {
        var $span = $("<span>" + (li_order++) + "</span>");
        $span.css("padding", "0 6px");
        if (li_order <= 4) {
            $span.css({
                "background-color": "#C63317",
                "color": "white"
            });
        } else {
            $span.css({
                "background-color": "#EEEEEE",
                "color": "black"
            });
        }
        $(this).prepend($span);
    });
    //页面滚动监听
    $(document).scroll(function () {
        if (($(document).scrollTop() + $(window).height()) > $(".article-ranking").parent().outerHeight() + 300) {
            $("#return-top").show();
        } else {
            $("#return-top").hide();
        }
        if ($(".article-list")[0] == null) return false;//异步加载条件
        if (($(document).height() - $(window).height()) < ($(document).scrollTop() + 300)) {
            loading_list();
        }
    });
    //异步文章获取
    function loading_list() {
        /* 异步访问锁 */
        if (lock == true) {
            return false;
        } else {
            page++;
            lock = true;
        }
        /* 异步加载 */
        $.ajax({
            url: '/loading/' + page + "/" + belong,
            type: 'GET',
            dataType: 'json',
            success: function (result) {
                var json = JSON.parse(result);
                for (i in json) {
                    var $item = $(".article-list li").eq(0).clone();
                    $item.children("div.col-md-4").children("a").attr("href", "/article/" + json[i].id);
                    $item.children("div.col-md-4").children("a").children("img").attr("src", json[i].image);
                    $item.children("div.col-md-8").children("h4").children("a").attr("href", "/article/" + json[i].id).text(json[i].title);
                    $item.children("div.col-md-8").children("p.abstract").text(json[i].abstract);
                    var $info = $item.children("div.col-md-8").children("div.article-info");
                    $info.children("strong").text(json[i].author);
                    $info.children("span").text(json[i].date);
                    $info.children("p").children("i").text(json[i].amount);
                    $(".article-list").append($item);
                }
                if (json.length != 0) {
                    lock = false;
                }
            }
        });
    }

    //异步推荐获取
    function get_commend() {
        if (window.location.pathname.match(/^\/article\//) == null) return;
        var $commend = $(".article-commend div.hidden");
        var id = $commend.children("a").attr("href").replace("/article/", "");
        $.ajax({
            url: '/commend/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (result) {
                var json = JSON.parse(result);
                $commend = $commend.removeClass("hidden").remove();
                for (i in json) {
                    $commend = $commend.clone();
                    $commend.children("a").attr("href", "/article/" + json[i].id)
                        .children("div.thumbnail").children("img").attr("src", json[i].image)
                        .siblings("div.caption").children("h3").text(json[i].title)
                        .siblings("p").text(json[i].abstract);
                    $(".article-commend").append($commend);
                }
            }
        });
    }
});
