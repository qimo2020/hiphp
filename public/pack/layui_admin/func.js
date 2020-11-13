layui.define(['jquery', 'form'], function(exports) {
    var $ = layui.jquery, form = layui.form;
    var obj = {
        assign: function(formData) {
            var input = '', form = layui.form;
            for (var i in formData) {
                switch($('.field-'+i).attr('type')) {
                    case 'select':
                        input = $('.field-'+i).find('option[value="'+formData[i]+'"]');
                        input.prop("selected", true);
                        break;
                    case 'radio':
                        input = $('.field-'+i+'[value="'+formData[i]+'"]');
                        input.prop('checked', true);
                        break;
                    case 'switch':
                        input = $('.field-'+i+'[value="'+formData[i]+'"]');
                        input.prop('checked', true);
                        break;
                    case 'checkbox':
                        if (typeof(formData[i]) == 'object') {
                            for(var j in formData[i]) {
                                input = $('.field-'+i+'[value="'+formData[i][j]+'"]');
                                input.prop('checked', true);
                            }
                        } else {
                            input = $('.field-'+i+'[value="'+formData[i]+'"]');
                            input.prop('checked', true);
                        }
                        break;
                    case 'img':
                        if (formData[i]) {
                            input = $('.field-'+i);
                            if(typeof formData[i+'_link'] != "undefined"){
                                input.attr({'src':formData[i+'_link']});
                            }else{
                                input.attr({'src':formData[i]});
                            }
                            input.css('display','inline-block');
                            input.parent().children($('.field-'+i+'[name="'+i+'"]')).val(formData[i]);
                        }
                        break;
                    case 'multi':
                        if (formData[i]) {
                            input = $('.field-'+i);
                            if(!$.isArray(formData[i])){
                                var arr = [];
                                arr.push(formData[i]);
                                formData[i] = arr;
                                if(typeof formData[i+'_link'] != "undefined"){
                                    var arrLink = [];
                                    arrLink.push(formData[i+'_link']);
                                    formData[i+'_link'] = arrLink;
                                }
                            }
                            $.each(formData[i], function(j){
                                if(formData[i+'_link'] && formData[i+'_link'][j]){
                                    input.attr({'src':formData[i+'_link'][j]});
                                    input.parent().find('.multi-priview').append('<li class="item_img"><div class="operate"><i class="close layui-icon">&#xe640;</i></div><a href="' + formData[i+'_link'][j] + '" target="_blank"><img src="' + formData[i+'_link'][j] + '" type="multi" class="field-'+i+'" ></a><input type="hidden" name="'+i+'[]" value="' + formData[i][j] + '" /></li>');;
                                }else{
                                    input.attr({'src':formData[i][j]});
                                    input.parent().find('.multi-priview').append('<li class="item_img"><div class="operate"><i class="close layui-icon">&#xe640;</i></div><a href="' + formData[i][j] + '" target="_blank"><img src="' + formData[i][j] + '" type="multi" class="field-'+i+'" ></a><input type="hidden" name="'+i+'[]" value="' + formData[i][j] + '" /></li>');;
                                }
                            })
                        }
                        break;
                    case 'inputMulti':
                        if (formData[i]) {
                            input = $('.field-'+i).find('input');
                            input.each(function (index, node) {
                                let target = node.type;
                                $.each($.parseJSON(formData[i]), function(k, v){
                                    if(target == 'text'){
                                        if(node.name == i+'['+k+']'){
                                            node.value = v;
                                        }
                                    }else if(target == 'radio'){
                                        if(node.name == i+'['+k+']' && node.value == v){
                                            node.checked = true;
                                        }
                                    }
                                })
                            })
                        }
                        break;
                    case 'file':
                        if (formData[i]) {
                            input = $('.field-'+i);
                            if(formData[i+'_link']){
                                input.text(formData[i+'_link']);
                            }else{
                                input.text(formData[i]);
                            }
                            input.parent().children($('.field-'+i+'[name="'+i+'"]')).val(formData[i]);
                        }
                        break;
                    case 'editor':
                        break;
                    default:
                        input = $('.field-'+i);
                        input.val(formData[i]);
                        break;
                }
                
                if (input.attr('data-disabled')) {
                    input.prop('disabled', true);
                }

                if (input.attr('data-readonly')) {
                    input.prop('readonly', true);
                }
            }
            form.render();
        },
    };

    exports('func', obj);
}); 