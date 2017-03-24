/*
peoject:后台操作js
author:FLc
time:2014-09-01 09:37:59
*/

//批量事件提示
function batchConfirm(i){
    var action=$(i).find("select[name='action']").val();
    if(action==''){
        alert("请选择操作类型！"); return false;
    }
    switch(action){
        case 'del':
            confirmTips='确认批量删除吗？';
            break;
    }
    if(!!confirmTips){
        return confirm(confirmTips);
    }else{
        return false;
    }
    return true;
}

/**
 * 使用弹窗插件显示
 * @param  {string} url 页面链接
 * @param  {number} w   宽度
 * @param  {number} h   高度
 * @param  {string} t   标题
 * @return {object}     
 */
function dialog(url,w,h,t){
    var width  = w||'60%';
    var height = h||'80%';
    var title  = t||'消息';
    return $.dialog.open(url,{
        title: title,
        width: width,
        height: height,
        lock: true,
    });
}


/**
 * 弹出上传框
 * @param  {string}   type     上传文件类型（配置参考Common/Conf/Config::FILE_UPLOAD_TYPES）。默认image
 * @param  {int}      number   上传文件最大数量
 * @param  {Function} callback 回调函数
 * @param  {int}      size     上传文件大小
 * @return {object}            artdialog对象
 */
function getUploadify(type, number, callback, size){
    var url = '/admin/file/index';
    var params = [];

    // 类型
    if (!type) {
        type = 'image';
    }
    params.push('type=' + type); 

    // 数量
    number = parseInt(number);
    number = Math.max(number, 1);
    params.push('number=' + number);

    // 文件大小
    if (!!size) {
        size = parseInt(size);
        params.push('size=' + size);
    }

    // 拼合url
    url = url + '?' + params.join('&');

    return $.dialog.open(url, {
        title: '文件上传',
        lock: true,
        background: '#fff',
        opacity: 0.5,
        resize: false,
        width: '505px',
        height: '375px',
        fixed: true,
        okVal: '保存',
        ok: function(){
            var res = $.dialog.data('upload_files');
            if (typeof callback == 'function') {
                callback(res);
            }
        },
        cancelVal: '取消',
        cancel: true
    });
}

/**
 * 单文件上传，并直接赋值文件id
 * @param  {string} id_name input框id名：传入文件id
 * @param  {string} type    上传类型
 * @param  {int}    size    上传文件大小
 * @return 当前文件数据信息
 */
function getOneFile(id_name, type, size){
    var result;
    getUploadify(type, 1, function(res){
        if (typeof res == 'object') {
            $.each(res, function(i, v){
                result = v;
                $('#' + id_name).val(v.fileid);
            })
        }
    }, size);
    return result;
}