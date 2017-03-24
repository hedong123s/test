/**
 * 商品类型js
 */

/**
 * 添加类型
 */
$('.productTypeAddForm').validate({
    //验证规则
    rules: {
        name: {
            required: true
        },
        sortid: {
            required: true,
            isNumber: true,
            range:[0,100000000]
        }
    },
    //错误信息
    messages: {
        name: {
            required: '请输入商品类型名称'
        },
        sortid: {
            required: '请输入排序编号',
            isNumber: '请输入数字',
            range: '值范围在{0}-{1}之间'
        }
    },
    //输出错误
    errorPlacement: function(error, element){
        //$('.errorInfos').html(error).show();
    },
    //提交验证
    invalidHandler: function (form, validator) {
        $.each(validator.errorList, function (key, value) {
            $(".errorInfo").html(value.message).show();
            return false;
        });
    },
    //输出成功
    success: function(error, element){
        //$('.errorInfos').empty().show();
    },
    //提交
    submitHandler: function(form){
        $(".errorInfo").empty().show();

        $.post(add_url, $(form).serialize(), function(d) {
            if (d.status) {
                window.location.href = d.url;
            } else {
                $('.errorInfo').html(d.msg).show();
            }
        }, 'json');

        return false;
    }
});


/**
 * 编辑类型
 */
$('.productTypeEditForm').validate({
    //验证规则
    rules: {
        name: {
            required: true
        },
        sortid: {
            required: true,
            isNumber: true,
            range:[0,100000000]
        }
    },
    //错误信息
    messages: {
        name: {
            required: '请输入商品类型名称'
        },
        sortid: {
            required: '请输入排序编号',
            isNumber: '请输入数字',
            range: '值范围在{0}-{1}之间'
        }
    },
    //输出错误
    errorPlacement: function(error, element){
        //$('.errorInfos').html(error).show();
    },
    //提交验证
    invalidHandler: function (form, validator) {
        $.each(validator.errorList, function (key, value) {
            $(".errorInfo").html(value.message).show();
            return false;
        });
    },
    //输出成功
    success: function(error, element){
        //$('.errorInfos').empty().show();
    },
    //提交
    submitHandler: function(form){
        $(".errorInfo").empty().show();

        $.post(edit_url, $(form).serialize(), function(d) {
            if (d.status) {
                window.location.href = d.url;
            } else {
                $('.errorInfo').html(d.msg).show();
            }
        }, 'json');

        return false;
    }
});


/**
 * 页面初始化
 */
$(function(){
    /**
     * 增加扩展属性
     */
    $('#attr_add').click(function(){
        var html = template('add_attr_template', {guid: guid()});
        $('#attrs_body').append(html);
    })

    /**
     * 删除扩展属性
     */
    $('#attrs_body tr a.del').live('click', function(){
        $(this).closest('tr').remove();
    })

    /**
     * 编辑属性值
     */
    $('.attr_values_editbtn').live('click', function(){
        var $tr     = $(this).closest('tr.attrs_item');
        var $inputs = $tr.find('.attrs_item_values_inputs');
        var guid    = $tr.attr('data-guid');

        $.dialog({
            title: '设置属性值',
            padding: '0',
            lock: true,
            background: '#fff',
            content: $('#attr_values_box')[0],
            init: function(){
                var alrValuesArr = $inputs.find('input.attr_values_input_item').map(function(){
                    var $self = $(this);
                    return {
                        vid: $self.attr('data-vid') ? $self.attr('data-vid') : '',
                        val: $.trim($self.val())
                    };
                }).get();

                var html = template('add_attr_value_template', {data: alrValuesArr});
                $('#attr_values_body').html(html);
            },
            button: [
                {
                    name: '新增',
                    callback: function (){
                        var html = template('add_attr_value_template');
                        $('#attr_values_body').append(html);
                        $('#attr_values_box').scrollTop(9999999999);  // 翻到最底部
                        return false;
                    }
                },
                {
                    name: '确定',
                    focus: true,
                    callback: function(){
                        var valArr = $('#attr_values_body input.attr_values_input').map(function(){
                            var $self = $(this);
                            if ($.trim($self.val()) != '') {
                                return {
                                    vid: $self.attr('data-vid') ? $self.attr('data-vid') : window.guid(),
                                    val: $.trim($self.val())
                                };
                            }
                        }).get();

                        var tmp = '';
                        $.each(valArr, function(i, v){
                            tmp += '<input type="hidden" class="attr_values_input_item" name="attrs[values][' + guid + '][' + v.vid + ']" data-vid="' + v.vid + '" value="' + v.val + '" />';
                            tmp += ' <span class="av_item">' + v.val + '</span> ';
                        })
                        $inputs.html(tmp);

                        // 清空
                        $('#attr_values_body').empty();
                    }
                }
            ]
        });
    })

    /**
     * 删除属性值
     */
    $('#attr_values_body .attr_value_del').live('click', function(){
        $(this).closest('tr').remove();
    })
})

/**
 * 生成guid
 */
function guid() {
    var s = [];
    var hexDigits = "0123456789abcdef";
    for (var i = 0; i < 36; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }
    s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "-";
 
    var uuid = s.join("");
    return uuid;
}