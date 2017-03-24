/**
 * 商品分类选择js
 */

function getSubCategorys(pid){
    $.get(cat_url, {pid: pid}, function(d){
        if (d.status) {
            var html = template('cats_template', d.data);
            if (d.data.pid == 0) {
                $('.s-cat-main').html(html);
            } else {
                var itemli = $('.s-cat-main').find('.s-cat-item ul li[data-id="' + d.data.pid + '"]').closest('.s-cat-item');
                itemli.nextAll().remove();
                itemli.after(html);
            }
        } else {
            alert(d.msg);
        }
    }, 'json');
}

/**
 * 点击选择
 */
$(function(){
    $('.s-cat-item ul li').live('click', function(){
        var id = $(this).attr('data-id');
        $(this).addClass('active').siblings('li').removeClass('active');
        getSubCategorys(id);
    })
})