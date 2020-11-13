layui.use(['jquery', 'form'], function() {
    var $ = layui.jquery, form = layui.form;
    $('input[lay-filter],select[lay-filter]').each(function(){
        var tagName = $(this)[0].tagName.toLowerCase(), filterType = '', inputType = '', filterName = $(this).attr('lay-filter'), field = $(this).attr('hi-filter-field'), action = $(this).attr('hi-filter-action'), type = $(this).attr('hi-filter-type'), targetField = $('.field-'+field);
        if(tagName == 'select'){
            filterType = 'select';
        }else{
            inputType = $(this).attr('type');
            if(inputType == 'checkbox' && $(this).attr('lay-skin') == 'switch'){
                filterType = 'switch';
            }else{
                switch (inputType) {
                    case 'checkbox':
                        filterType = 'checkbox';
                        break;
                    case 'radio':
                        filterType = 'radio';
                        break;
                }
            }
        }
        form.on(filterType+'('+filterName+')', function(data){
            $.ajax({
                url: action,
                data: {'id': data.value},
                type: "post",
                dataType: "json",
                success: function (data) {
                    html(type, field, data.data);
                    setTimeout(function () {
                        form.render();
                    }, 50)
                },
                error: function (data) {
                    $.messager.alert('错误', data.msg);
                }
            })
        })
    });

    function html(type, field, data) {
        let html = '', targetField = $('.field-'+field);
        switch (type) {
            case 'inputMulti':
                let parent = targetField.closest('.layui-field-box');
                $.each(data, function(i, val){
                    html += ' <div class="layui-inline"><label class="layui-form-label">'+val.name+'</label><div class="layui-input-inline">';
                    if(val.options == ''){
                        html += '<input type="text" class="layui-input field-text-'+field+'" name="'+field+'['+val.name+']" placeholder="" autocomplete="off">';
                    }else{
                        for(let j=0; j<val.options.length; j++){
                            html += '<input type="radio" class="layui-input field-radio-'+field+'" name="'+field+'['+val.name+']" value="'+val.options[j]+'" title="'+val.options[j]+'">';
                        }
                    }
                    html += '</div><div class="layui-form-mid layui-word-aux">';
                    html += '</div></div>';
                });
                parent.html(html);
                break;
        }
        return html;
    }
})