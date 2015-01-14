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

    if($('#checkout-form').exist()){

        var validator = $("#checkout-form").validate({
            rules: {
                firstName: {
                    minlength: 3,
                    maxlength: 15,
                    required: true
                },
                lastName: {
                    minlength: 3,
                    maxlength: 15,
                    required: true
                }
            },

            highlight: function(element) {
                $(element).closest('.control-group').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).closest('.control-group').removeClass('has-error');
            },
            errorElement: 'span',
            errorClass: 'help-block',
            errorPlacement: function(error, element) {
                if(element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
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