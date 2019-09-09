jQuery(function($){
    $('.slider').slick({
        arrows:false,
        asNavFor:'.thumb',
    });
    $('.thumb').slick({
        asNavFor:'.slider',
        focusOnSelect: true,
        slidesToShow:4,
        slidesToScroll:1
    });
});