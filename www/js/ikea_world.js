$.fn.exist = function () {
    return $(this).length > 0;
}
$(function () {
    if ($('.bxslider').exist()) {
        $('.bxslider').bxSlider({
            auto: true,
            pause: 3000,
            pager: false
        });
    }
    if ($('.input-qty').exist()) {
        $('.input-qty').TouchSpin();
    }
    /* if ($(window).width() > 750) {
     $('.link-p img').centerImage();
     }*/
    $(window).resize(function () {
        var width = $(this).width();
        if (width > 750) {
            $('.link-p img').centerImage();
            $('.link-p img').removeClass('def-img');
        } else {
            $('.link-p img').addClass('def-img');
        }
    });
    $(window).load(function () {
        if ($('.sp-wrap').exist()) {
            $('.sp-wrap').smoothproducts();
        }
    });

    $('.add-to-cart').on('click', function () {
        $.post(contextUrl + "cart/addItem", {count: 1, id: $(this).data("id")}, function (data) {
           if(!data.error) {
               refreshCartMenu(data.content);
           }
        });

        return false;
    });

    if($('form').exist()){
        alert("ok");
        $("input,select,textarea").not("[type=submit]").jqBootstrapValidation( {
            preventSubmit: true,
            submitError: function($form, event, errors) {
            },
            submitSuccess: function($form, event) {
                event.preventDefault();
            },
            filter: function() {
                return $(this).is(":visible");
            }
        });
    }

    $(window).scroll(function () {
        if ($(this).scrollTop() > 70) {
            $('.back-top').fadeIn();
        } else {
            $('.back-top').fadeOut();
        }
    });

    function refreshCartMenu(content){
        $("#menu-cart").replaceWith(content);
    }
});