layui.define(['element', 'form', 'table', 'md5'], function(exports) {
    var $ = layui.jquery,element = layui.element,
        layer = layui.layer,
        form = layui.form,
        table = layui.table,
        $win = $(window);

    var checkBrowser = function() {
        var d = layui.device();
        d.ie && d.ie < 10 && layer.alert("IE" + d.ie + "下体验不佳，推荐使用：Chrome/Firefox/Edge/极速模式");
    }
    checkBrowser();

    /* 静态表格全选 */
    form.on('checkbox(allChoose)', function(data) {
        var child = $(data.elem).parents('table').find('tbody input.checkbox-ids');
        child.each(function(index, item) {
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });

    /**
     * 通用状态设置开关
     * @attr data-href 请求地址
     * @attr confirm 确认提示
     */
    form.on('switch(switchStatus)', function(data) {
        var that = $(this), status = data.elem.checked == false ? 0 : 1, func = function() {
            $.get(that.attr('data-href'), {v:status}, function(res) {
                if (res.code == 0) {
                    that.trigger('click');
                    form.render('checkbox');
                }
            });
        };
        if (typeof(that.attr('data-href')) == 'undefined') {
            layer.msg('请设置data-href参数');
            return false;
        }
        if (this.checked) {
            status = 1;
        }

        if (typeof(that.attr('confirm')) == 'undefined') {
            func();
        } else {
            layer.confirm(that.attr('confirm') || '你确定要执行操作吗？', {title:false, closeBtn:0}, function(index){
                func();
            }, function() {
                that.trigger('click');
                form.render('checkbox');
            });
        }
    });

    if(typeof tableSwitchList != "undefined"){
        /**
         * 通用switch设置开关,可用于后台数据表格
         * @attr data-href 请求地址
         * @attr confirm 确认提示
         */
        for(var i=0; i<=tableSwitchList.length; i++){
            form.on('switch('+tableSwitchList[i]+')', function(data) {
                var that = $(this), status = data.elem.checked == false ? 0 : 1, func = function() {
                    $.get(that.attr('data-href'), {status:status}, function(res) {
                        if (res.code == 0) {
                            that.trigger('click');
                            form.render('checkbox');
                        }
                    });
                };
                if (typeof(that.attr('data-href')) == 'undefined') {
                    layer.msg('请设置data-href参数');
                    return false;
                }
                if (this.checked) {
                    status = 1;
                }
                if (typeof(that.attr('confirm')) == 'undefined') {
                    func();
                } else {
                    layer.confirm(that.attr('confirm') || '你确定要执行操作吗？', {title:false, closeBtn:0}, function(index){
                        func();
                    }, function() {
                        that.trigger('click');
                        form.render('checkbox');
                    });
                }
            });
        }
    }

    /**
     * iframe弹窗
     * @href 弹窗地址
     * @title 弹窗标题
     * @hi-data {width: '弹窗宽度', height: '弹窗高度', idSync: '是否同步ID', table: '数据表ID(同步ID时必须)', type: '弹窗类型'}
     */
    $(document).on('click', '.hi-iframe-pop,.hi-iframe', function() {
        var that = $(this), query = '';
        var def = {width: '750px', height: '500px', idSync: false, table: 'dataTable', type: 2, url: that.attr('href'), title: that.attr('title')};
        var opt = new Function('return '+ that.attr('hi-data'))() || {};
        if(!opt.width){
            opt.width = '50%';
        }
        if(!opt.height){
            opt.height = '85%';
        }
        opt = Object.assign({}, def, opt);
        if (!opt.url) {
            layer.msg('请设置href参数');
            return false;
        }

        if ($('.checkbox-ids:checked').length <= 0) {
            var checkStatus = table.checkStatus(opt.table);

            for (var i in checkStatus.data) {
                query += '&id[]=' + checkStatus.data[i].id;
            }
        } else {
            $('.checkbox-ids:checked').each(function() {
                query += '&id[]=' + $(this).val();
            })
        }

        if (opt.idSync && (query == '' || query == null)) {
            layer.msg('请选择要操作的数据');
            return false;
        }
        if (opt.url.indexOf('?') >= 0) {
            opt.url += '&hi_iframe=yes'+query;
        } else {
            opt.url += '?hi_iframe=yes'+query;
        }
        layer.open({type: opt.type, offset: 'auto', title: opt.title, content: opt.url, maxmin: true, area: [opt.width, opt.height]});
        return false;
    });

    /**
     * 监听表单提交
     * @attr action 请求地址
     * @attr data-form 表单DOM
     */
    form.on('submit(formSubmit)', function(data) {
        var _form = '',
            that = $(this),
            text = that.text(),
            opt = {},
            def = {pop: false, refresh: true, jump: false, callback: null, time: 3000};
        if ($(this).attr('data-form')) {
            _form = $(that.attr('data-form'));
        } else {
            _form = $(data.form);
        }

        if (that.attr('hi-data')) {
            opt = new Function('return '+ that.attr('hi-data'))();
        } else if (that.attr('lay-data')) {
            opt = new Function('return '+ that.attr('lay-data'))();
        }

        opt = Object.assign({}, def, opt);

        /* CKEditor专用 */
        if (typeof(CKEDITOR) != 'undefined') {
            for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
        }
        that.removeClass('layui-btn-normal').addClass('layui-btn-disabled').prop('disabled', true).text('提交中...');
        //_post = _form.serialize().split("&").filter(function(str){return !str.endsWith("=")}).join("&"); //过滤空值
        $.ajax({
            type: "POST",
            url: _form.attr('action'),
            data: _form.serialize(),
            success: function(res) {
                that.removeClass("layui-btn-disabled");
                if (res.code == 0) {
                    that.text(res.msg).prop('disabled', false).removeClass('layui-btn-normal').addClass('layui-btn-danger');
                    setTimeout(function(){
                        that.removeClass('layui-btn-danger').addClass('layui-btn-normal').text(text);
                    }, opt.time);
                } else {
                    that.addClass('layui-btn-normal').text(res.msg);
                    setTimeout(function() {
                        that.text(text).prop('disabled', false);
                        if (opt.callback) {
                            opt.callback(that, res);
                        }
                        console.log(opt);
                        if (opt.pop == true) {
                            if (opt.refresh == true) {
                                parent.location.reload();
                            } else if (opt.jump == true && res.url != '') {
                                parent.location.href = res.url;
                            }
                            console.log('dd');
                            parent.layui.layer.closeAll();
                        } else if (opt.refresh == true) {
                            if (res.url != '') {
                                location.href = res.url;
                            } else {
                                history.back(-1);
                            }
                        }
                    }, opt.time);
                }
            }
        });
        return false;
    });

    /**
     * 通用TR数据行删除
     * @attr href或data-href 请求地址
     * @attr refresh 操作完成后是否自动刷新
     */
    $(document).on('click', '.j-tr-del,.hi-tr-del', function() {
        var that = $(this),
            href = !that.attr('data-href') ? that.attr('href') : that.attr('data-href');
        layer.confirm('删除之后无法恢复，您确定要删除吗？', {title:false, closeBtn:0}, function(index){
            if (!href) {
                layer.msg('请设置data-href参数');
                return false;
            }
            $.get(href, function(res) {
                if (res.code == 0) {
                    layer.msg(res.msg);
                } else {
                    layer.msg(res.msg);
                    that.parents('tr').remove();
                }
            });
            layer.close(index);
        });
        return false;
    });

    /**
     * ajax请求操作
     * @attr href或data-href 请求地址
     * @attr refresh 操作完成后是否自动刷新
     * @class confirm confirm提示内容
     */
    $(document).on('click', '.j-ajax,.hi-ajax', function() {
        var that = $(this),
            href = !that.attr('data-href') ? that.attr('href') : that.attr('data-href'),
            refresh = !that.attr('refresh') ? 'true' : that.attr('refresh');
        if (!href) {
            layer.msg('请设置data-href参数');
            return false;
        }

        if (!that.attr('confirm')) {
            layer.msg('数据提交中...', {time:500000});
            $.get(href, {}, function(res) {
                layer.msg(res.msg, {}, function() {
                    if (refresh == 'true' || refresh == 'yes') {
                        if (typeof(res.url) != 'undefined' && res.url != null && res.url != '') {
                            location.href = res.url;
                        } else {
                            location.reload();
                        }
                    }
                });
            });
            layer.close();
        } else {
            layer.confirm(that.attr('confirm'), {title:false, closeBtn:0}, function(index){
                layer.msg('数据提交中...', {time:500000});
                $.get(href, {}, function(res) {
                    layer.msg(res.msg, {}, function() {
                        if (refresh == 'true') {
                            if (typeof(res.url) != 'undefined' && res.url != null && res.url != '') {
                                location.href = res.url;
                            } else {
                                location.reload();
                            }
                        }
                    });
                });
                layer.close(index);
            });
        }
        return false;
    });

    /**
     * 数据列表input编辑自动选中ids
     * @attr data-value 修改前的值
     */
    $(document).on('blur', '.j-auto-checked,hi-auto-checked',function() {
        var that = $(this);
        if(that.attr('data-value') != that.val()) {
            that.parents('tr').find('input[name="ids[]"]').attr("checked", true);
        }else{
            that.parents('tr').find('input[name="ids[]"]').attr("checked", false);
        };
        form.render('checkbox');
    });

    /**
     * input编辑更新
     * @attr data-value 修改前的值
     * @attr data-href 提交地址
     */
    $(document).on('focusout', '.j-ajax-input,.hi-ajax-input',function(){
        var that = $(this), _val = that.val();
        if (_val == '') return false;
        if (that.attr('data-value') == _val) return false;
        if (!that.attr('data-href')) {
            layer.msg('请设置data-href参数');
            return false;
        }
        $.post(that.attr('data-href'), {v:_val}, function(res) {
            if (res.code == 1) {
                that.attr('data-value', _val);
            }
            layer.msg(res.msg);
        });
    });

    /**
     * 小提示
     */
    $('.tooltip').hover(function() {
        var that = $(this);
        that.find('i').show();
    }, function() {
        var that = $(this);
        that.find('i').hide();
    });

    /**
     * 列表页批量操作按钮组
     * @attr href 操作地址
     * @attr data-table table容器ID
     * @class confirm 类似系统confirm
     * @attr tips confirm提示内容
     */
    $(document).on('click', '.j-page-btns,.hi-page-btns,.hi-table-ajax', function(){
        var that = $(this),
            query = '',
            code = function(that) {
                var href = that.attr('href') ? that.attr('href') : that.attr('data-href');
                var tableObj = that.attr('data-table') ? that.attr('data-table') : 'dataTable';
                if (!href) {
                    layer.msg('请设置data-href参数');
                    return false;
                }

                if ($('.checkbox-ids:checked').length <= 0) {
                    var checkStatus = table.checkStatus(tableObj);
                    console.log(checkStatus.data.length);
                    if (checkStatus.data.length <= 0) {
                        layer.msg('请选择要操作的数据');
                        return false;
                    }
                    for (var i in checkStatus.data) {
                        if (i > 0) {
                            query += '&';
                        }
                        query += 'id[]='+checkStatus.data[i].id;
                    }
                } else {
                    if (that.parents('form')[0]) {
                        query = that.parents('form').serialize();
                    } else {
                        query = $('#pageListForm').serialize();
                    }
                }

                layer.msg('数据提交中...',{time:500000});
                $.post(href, query, function(res) {
                    layer.msg(res.msg, {}, function(){
                        if (res.code != 0) {
                            location.reload();
                        }
                    });
                });
            };
        if (that.hasClass('confirm')) {
            var tips = that.attr('tips') ? that.attr('tips') : '您确定要执行此操作吗？';
            layer.confirm(tips, {title:false, closeBtn:0}, function(index){
                code(that);
                layer.close(index);
            });
        } else {
            code(that);
        }
        return false;
    });

    /**
     * layui非静态table过滤渲染
     * @attr data-table table容器ID
     * @attr hi-data table基础参数
     * @attr href 过滤请求地址
     */
    $(document).on('click', '.hi-table-search', function() {
        var that = $(this),
            arr = that.closest('form').serializeArray(),
            where = new Array(),
            dataTable = that.attr('data-table') ? that.attr('data-table') : 'dataTable',
            options = new Function('return '+ that.attr('hi-data'))() || {page: {curr:1}};
        for(var i in arr) {
            where[arr[i].name] = arr[i].value;
        }
        options.url = that.closest('form').attr('action');
        options.where = where;
        console.log(options);
        table.reload(dataTable, options);
        return false;
    });

    exports('base', {});
});