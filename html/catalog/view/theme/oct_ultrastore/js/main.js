/*********** Function viewport ***********/
function viewport() {
	var e = window,
		a = 'inner';
	if (!('innerWidth' in window)) {
		a = 'client';
		e = document.documentElement || document.body;
	}
	return {
		width: e[a + 'Width'],
		height: e[a + 'Height']
	};
}

/*********** Function preloader ***********/

function masked(element, status) {
  if (status == true) {
    $('<div/>').attr({ 'class':'masked' }).prependTo(element);
    $('<div class="masked_loading" />').insertAfter($('.masked'));
  } else {
    $('.masked').remove();
    $('.masked_loading').remove();
  }
}

/*********** Function popups ***********/

function octPopupCallPhone() {
	masked('body', true);
	$( ".modal-backdrop" ).remove();
	$.ajax({
        type: 'post',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_popup_call_phone',
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#us-callback-modal").modal("show");
        }
    });
}

function octPopupCart() {
	masked('body', true);
	$( ".modal-backdrop" ).remove();
	$.ajax({
        type: 'get',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_popup_cart',
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#us-cart-modal").modal("show");
        }
    });
}

function octPopupSubscribe() {
	if($(".modal-backdrop").length > 0) { 
	    return;
	}
	masked('body', true);
	$( ".modal-backdrop" ).remove();
	$.ajax({
        type: 'post',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_subscribe',
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#us-subscribe-modal").modal("show");
        }
    });
}

function octPopupFoundCheaper(product_id) {
	masked('body', true);
	$( ".modal-backdrop" ).remove();
	$.ajax({
        type: 'post',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_popup_found_cheaper',
        data: 'product_id=' + product_id,
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#us-cheaper-modal").modal("show");
        }
    });
}

function octPopupLogin() {
	masked('body', true);
	$( ".modal-backdrop" ).remove();
    $.ajax({
        type: "post",
        url: 'index.php?route=octemplates/module/oct_popup_login',
        data: $(this).serialize(),
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#loginModal").modal("show");
        }
    });
}

function octPopUpView(product_id) {
	masked('body', true);
	$( ".modal-backdrop" ).remove();
	$.ajax({
        type: 'post',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_popup_view',
        data: 'product_id=' + product_id,
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#us-quickview-modal").modal("show");
        }
    });
}

function octPopPurchase(product_id) {
	masked('body', true);
	$( ".modal-backdrop" ).remove();
	$.ajax({
        type: 'post',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_popup_purchase',
        data: 'product_id=' + product_id,
        	cache: false,
        success: function(data) {
	        masked('body', false);
            $(".modal-holder").html(data);
            $("#us-one-click-modal").modal("show");
        }
    });
}

/*********** Button column ***********/

function octShowColumnProducts(octButtonPrevID, octButtonNextID, octModuleID) {
	const buttonPrevID = octButtonPrevID;
	const buttonNextID = octButtonNextID;
	const moduleID = octModuleID + ' > .us-item';
	$("#" + moduleID).slice(0, 1).show();
	$("#" + octButtonNextID).click(function () {
		const visibleProduct = $("#" + moduleID + ":visible");
		const NextProduct = visibleProduct.next();
		if (NextProduct.length > 0) {
			visibleProduct.css('display', 'none');
			NextProduct.fadeIn("slow");
		} else {
			visibleProduct.css('display', 'none');
			$("#" + moduleID + ":hidden:first").fadeIn('slow');
		}
	});
	$("#" + buttonPrevID).click(function () {
		const visibleProduct = $("#" + moduleID + ":visible");
		const NextProduct = visibleProduct.prev();
		if (NextProduct.length > 0) {
			visibleProduct.css('display', 'none');
			NextProduct.fadeIn("slow");
		} else {
			visibleProduct.css('display', 'none');
			$("#" + moduleID + ":hidden:last").fadeIn('slow');
		}
	});
}

/*********** Button show more ***********/

function octShowProducts(octButtonID, octModuleID, itemsLimit, countProducts) {
	const currentWidth = viewport().width;
	let octModId = octModuleID.split('_');
	
	if (currentWidth > 1200) {
		itemsLimit = 8;
	} else if ((currentWidth <= 1200) && (currentWidth > 992)) {
		itemsLimit = 6;
	} else if (currentWidth <= 992) {
		itemsLimit = 4;
	}
	
	if (octModId[0] === 'us-product-reviews') {
		itemsLimit = 4;
	} else if (octModId[0] === 'us-subcat') {
		itemsLimit = 8;
	}
	
	if (octModId[0] === 'us-product-reviews' && currentWidth <= 1024) {
		itemsLimit = 3;
	}
	
	if (octModId[0] === 'us-product-reviews' && currentWidth <= 992 && currentWidth >= 768) {
		itemsLimit = 4;
	}
	
	if (octModId[0] === 'us-blog-article' && currentWidth >= 1024) {
		itemsLimit = 3;
	}
	
	const buttonID = octButtonID;
	const moduleID = octModuleID + ' > .us-item:hidden';
	
	if (countProducts <= itemsLimit) {
		$('#' + buttonID).parent().parent().parent().hide();
	}
	
	$('#' + moduleID).slice(0, itemsLimit).show();
	$('#' + buttonID).on('click', function () {
		$('#' + moduleID).slice(0, itemsLimit).show();
		if ($('#' + moduleID).length == 0) {
			$('#' + buttonID).fadeOut('fast');
		}
	});
}
	
/*********** Animate scroll to element function ***********/

function scrollToElement(hrefTo) {
	const top = $(hrefTo).offset().top-50;
	$('body,html').animate({scrollTop: top}, 1000);
}
	
/*********** Notify function ***********/

function usNotify(type, text) {
	let iconType = 'info';
	switch (type) {
	  case 'success':
	    iconType = 'fas fa-check';
	    break;
	  case 'danger':
	    iconType = 'fas fa-times';
	    break;
	  case 'warning':
	    iconType = 'fas fa-exclamation';
	    break;
	}
	$.notify({
		message: text,
		icon: iconType
	},{
		type: type
	});
}

/*********** Mask function ***********/

function usInputMask(selector, mask) {
	$(selector).inputmask({'mask': mask});
}

$(document).ready(function() {
	
	function viewport() {
		var e = window,
			a = 'inner';
		if (!('innerWidth' in window)) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		return {
			width: e[a + 'Width'],
			height: e[a + 'Height']
		};
	}
	
	/*********** Menu ***********/
	
	$('#oct-menu-box').hover(function () {
		$('#oct-menu-dropdown-menu').addClass('oct-menu-active');
	});
	
	$('#oct-menu-box').on('mouseleave', function () {
		$('#oct-menu-dropdown-menu').removeClass('oct-menu-active');
	});
	
	$('.oct-menu-li').hover(function () {
		$(this).children('.oct-menu-child-ul').addClass('oct-menu-child-ul-active');
	});
	
	$('.oct-menu-li').on('mouseleave', function () {
		$(this).children('.oct-menu-child-ul').removeClass('oct-menu-child-ul-active');
	});
	
	$('.oct-mm-link').hover(function () {
		$(this).children('.oct-mm-dropdown').addClass('oct-mm-dropdown-active');
	});
	
	$('.oct-mm-link').on('mouseleave', function () {
		$(this).children('.oct-mm-dropdown').removeClass('oct-mm-dropdown-active');
	});
	
	/*********** To top button ***********/
	
	$("#back-top").hide(),
	$(function() {
	    $(window).scroll(function() {
	        $(this).scrollTop() > 450 ? $("#back-top").fadeIn() : $("#back-top").fadeOut()
	    }),
	    $("#back-top a").click(function() {
	        return $("body,html").animate({
	            scrollTop: 0
	        }, 800),
	        !1
	    })
	});
	
	/*********** Fixed contacts ***********/
	
	$('#us_fixed_contact_button').on('click', function () {
		$(this).toggleClass('clicked');
		$('.us-fixed-contact-dropdown').toggleClass('expanded');
		$('.us-fixed-contact-icon .fa-comment-dots').toggleClass('d-none');
		$('.us-fixed-contact-icon .fa-times').toggleClass('d-none');
		$('#us_fixed_contact_substrate').toggleClass('active');
	});
	
	$('#us_fixed_contact_substrate').on('click', function () {
		$(this).removeClass('active');
		$('.us-fixed-contact-dropdown').removeClass('expanded');
		$('.us-fixed-contact-icon .fa-comment-dots').removeClass('d-none');
		$('.us-fixed-contact-icon .fa-times').toggleClass('d-none');
		$('#us_fixed_contact_button').removeClass('clicked');
	});
	
	$('.address-dropdown-menu, .shedule-dropdown-menu, .us-fixed-contact-dropdown').click(function(e){
		e.stopPropagation();
	});
	
	/*********** Category column module ***********/
	
	$('.us-categories-toggle').on('click', function () {
		if ($(this).hasClass('clicked') || $(this).parent().parent().hasClass('active')) {
			$(this).parent().parent().removeClass('active');
			$(this).parent().next().removeClass('expanded');
			$(this).removeClass('clicked');
		} else {
			$(this).toggleClass('clicked');
			$(this).parent().next().toggleClass('expanded');
		}
	});
	
	$('.us-product-nav-item').on('click', function () {
		$('.us-product-nav-item').removeClass('us-product-nav-item-active');
		$(this).addClass('us-product-nav-item-active');
	});
	
	/*********** Mobile scprits ***********/
	
	let screenWidth = viewport().width;
	
	let resizeId;
	
	function makeResize() {
		// for ajax menu
		//$('#us_menu_mobile_content').append($('#oct-menu-ul'));
		$('#us_info_mobile .mobile-address-box').append($('.address-dropdown-menu li'));
		$('#us_info_mobile .mobile-shedule-box').append($('.shedule-dropdown-menu li'));
		$('#us_info_mobile .nav-dropdown-menu-content').prepend($('#currency'));
		$('#us_info_mobile .nav-dropdown-menu-content').prepend($('#language'));
	}
	
	function unResize() {
		// for ajax menu
		//$('#oct-menu-dropdown-menu').append($('#oct-menu-ul'));
		$('.address-dropdown-menu').append($('#us_info_mobile .mobile-address-box li.us-dropdown-item, #us_info_mobile .mobile-address-box .us-mobile-map-box'));
		$('.shedule-dropdown-menu').append($('#us_info_mobile .mobile-shedule-box li.us-dropdown-item'));
		$('#top-links').prepend($('#currency'));
		$('#top-links').prepend($('#language'));
	}
	
	window.addEventListener('resize', function() {
	    clearTimeout(resizeId);
	    resizeId = setTimeout(doneResizing, 500);
	});
	
	function doneResizing(){
		let screenWidth = viewport().width;
	    if (screenWidth > 767 && screenWidth < 1024) {
			makeResize();
		} 
		if (screenWidth > 1023) {
			unResize();
		}
		if (screenWidth < 768) {
			$('.us-footer-contact-box').append($('.us-footer-social'));
		} else {
			$('.us-footer-shedule-box').append($('.us-footer-social'));
		}
	}
	
	function mobileMenu() {
		let screenWidth = viewport().width;
		
		if (screenWidth < 992) {
			let parentSelector = $("#us_menu_mobile_content").find('#oct-menu-ul');
			
			$('.oct-menu-li').hover(function () {
				$(this).children('.oct-menu-child-ul').addClass('oct-menu-child-ul-active');
			});
			
			$('.oct-menu-li').on('mouseleave', function () {
				$(this).children('.oct-menu-child-ul').removeClass('oct-menu-child-ul-active');
			});
			
			$('.oct-mm-link').hover(function () {
				$(this).children('.oct-mm-dropdown').addClass('oct-mm-dropdown-active');
			});
			
			$('.oct-mm-link').on('mouseleave', function () {
				$(this).children('.oct-mm-dropdown').removeClass('oct-mm-dropdown-active');
			});
			
			$('#us_menu_mobile_close').on('click', function () {
				$('#us_menu_mobile_box').removeClass('expanded');
				$('body').removeClass('no-scroll');
				parentSelector.css('transform', 'translateX(0)').removeClass('second-toggled').removeClass('third-toggled');
				
				$('#us_menu_mobile_content').removeClass('opened');
			});
			
			$('.oct-menu-toggle').on('click', function () {
				parentSelector.css('transform', 'translateX(-100%)').addClass('second-toggled');
				$('#us_menu_mobile_content').addClass('opened').scrollTop(0.5);
			});
			
			$('.oct-childmenu-toggle').on('click', function () {
				parentSelector.css('transform', 'translateX(-200%)').addClass('third-toggled');
			});
			
			$('.oct-menu-back').on('click', function () {
				parentSelector.css('transform', 'translateX(0)').removeClass('second-toggled');
				$('#us_menu_mobile_content').removeClass('opened');
				$('.oct-menu-ul').scrollTop(0.5);
			});
			
			$('.oct-childmenu-back').on('click', function () {
				parentSelector.css('transform', 'translateX(-100%)').removeClass('third-toggled');
				$('.oct-menu-ul').scrollTop(0.5);
			});
			
			$('#us_menu_mobile_title').on('click', function () {
				if (parentSelector.hasClass('second-toggled')) {
					parentSelector.css('transform', 'translateX(0)').removeClass('second-toggled');
				}
				if (parentSelector.hasClass('third-toggled')) {
					parentSelector.css('transform', 'translateX(100)').removeClass('third-toggled');
				}
				$('#us_menu_mobile_content').removeClass('opened');
				$('.oct-menu-ul').scrollTop(0.5);
			});
		} else {
			unResize();
		}
	}
	
	/*
	for ajax menu
	$('#us_menu_mobile_button').on('click', function () {
		$('#us_menu_mobile_box').addClass('expanded');
		$('body').addClass('no-scroll');
	});
	*/
	
	$('#us_menu_mobile_button').on('click', function () {
		
		const $menuId = $('#us_menu_mobile_content').html().length == 0;

	    if (!$menuId) {
		    
	        	$('#us_menu_mobile_box').addClass('expanded');
			$('body').addClass('no-scroll');
			
	    } else {
			masked('body', true);
			$.ajax({
		        type: 'post',
		        dataType: 'html',
		        url: 'index.php?route=octemplates/module/oct_megamenu',
		        data: 'mobile=1',
		        	cache: false,
		        success: function(data) {
			        $("#us_menu_mobile_content").html(data);
			        $('#us_menu_mobile_box').addClass('expanded');
					$('body').addClass('no-scroll');
					
					mobileMenu();
					masked('body', false);
		        }
		    });
		}
	});

	// Info mobile
	
	$('#dropdown_menu_info').on('click', function () {
		$('#us_info_mobile').addClass('expanded');
		$('body').addClass('no-scroll');
	});
	
	$('#us_info_mobile').on('click', function (e) {
		  e.stopPropagation();
	});
	$('#us_info_mobile_close').on('click', function () {
		$('#us_info_mobile').removeClass('expanded');
		$('body').removeClass('no-scroll');
	});
	
	if (screenWidth < 992) {
		makeResize();
		
		$('.us-footer-title').on('click', function () {
			$(this).next().slideToggle();
			$(this).toggleClass('open');
		});
		
	} else {
		unResize();
	}
	
	if (screenWidth < 768) {
		$('.us-footer-contact-box').append($('.us-footer-social'));
	} else {
		$('.us-footer-shedule-box').append($('.us-footer-social'));
	}
	
});