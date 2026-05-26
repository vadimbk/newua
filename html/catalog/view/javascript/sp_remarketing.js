function remarketingAddToCart(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'add_to_cart_sent');
	heading = $('h1').text();
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	if (json['remarketing']) {
		if (json['remarketing']['google_status'] != null) {
			if (typeof gtag != 'undefined') {
				gtag('event', 'add_to_cart', json['remarketing']['google_remarketing_event']);
			}
			
			if (json['remarketing']['google_ads_identifier_cart'] != '') { 
				if (typeof gtag != 'undefined') {
					gtag('event', 'conversion', json['remarketing']['google_ads_event']);
				}
			}
		}

		if (json['remarketing']['facebook_status'] != null && json['remarketing']['facebook_pixel_status'] != null) {
			if (typeof fbq != 'undefined') {
				fbq('track', 'AddToCart', json['remarketing']['facebook_pixel_event'], {eventID: json['remarketing']['event_id']}); 
			}
		}
		
		if (json['remarketing']['tiktok_status'] != null && json['remarketing']['tiktok_pixel_status'] != null) {
			if (typeof ttq != 'undefined') {
				ttq.track('AddToCart', json['remarketing']['tiktok_event'], {eventID: json['remarketing']['event_id']}); 
			}
		}

		if (json['remarketing']['snapchat_status'] != null && json['remarketing']['snapchat_pixel_status'] != null) {
			if (typeof snaptr != 'undefined') {
				snaptr('track','ADD_CART', json['remarketing']['snapchat_event']);
			}
		}
		
		if (json['remarketing']['ecommerce_status'] !== null) {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({ ecommerce: null });
			dataLayer.push(json['remarketing']['ga4_datalayer']);  
		}
		
		if (json['remarketing']['ecommerce_ga4_status'] != null) {
			if (typeof gtag != 'undefined') {
				json['remarketing']['ecommerce_ga4_event']['items'][0]['item_list_name'] = heading;
				gtag('event', 'add_to_cart', json['remarketing']['ecommerce_ga4_event']);
			}
		}
		
		if (json['remarketing']['esputnik_status'] != null) {
			if (typeof eS != 'undefined') {
				eS('sendEvent', 'StatusCart', json['remarketing']['esputnik_event']); 
			}
		}
		
		if (typeof events_cart_add != 'undefined') {
			events_cart_add();
		}
	}
}	  

function remarketingRemoveFromCart(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'remove_from_cart_sent');
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}

	if (json['remarketing']) {
		if (json['remarketing']['ecommerce_status'] != null) {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({ ecommerce: null });
			dataLayer.push(json['remarketing']['ga4_datalayer']);   
		}
		
		if (json['remarketing']['ecommerce_ga4_status'] != null) {
			if (typeof gtag != 'undefined') {
				json['remarketing']['ecommerce_ga4_event']['items'][0]['item_list_name'] = heading;
				gtag('event', 'remove_from_cart', json['remarketing']['ecommerce_ga4_event']);
			}
		}

		if (json['remarketing']['esputnik_status'] != null) {
			if (typeof eS != 'undefined') {
				eS('sendEvent', 'StatusCart', json['remarketing']['esputnik_event']); 
			}
		}
	}
}	

function remarketingRemoveFromSimpleCart(cart_product_id, quantity) {
	if (cart_product_id && quantity) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/removeProduct',
		data: {'product_id' : cart_product_id, 'quantity': quantity},
			dataType: 'json',
            success: function(json) { 
				remarketingRemoveFromCart(json);
			}
		});
	}
}

function sendGa4Click(data) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ga4_click_sent');
	heading = $('h1').text();
	currency = $('.currency_ecommerce_code').val();
	
	if (data) {
		if (typeof gtag != 'undefined') {
			gtag('event', 'select_item', {
				'send_to': $('.ecommerce_ga4_identifier').val(),
				'currency': currency,
				'items': [data]
			});
		}
	}
}	 

function sendGa4Impressions(data, search = false, measurement = false) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ga4_impressions_sent');
	currency = $('.currency_ecommerce_code').val();
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}
	
	if (data && measurement == false) {
		if (typeof gtag != 'undefined') {
			if (!search) {
				event_name = 'view_item_list';
			} else {
				event_name = 'view_search_results';
			}

			gtag('event', event_name, {
				'send_to': $('.ecommerce_ga4_identifier').val(),
				'currency': currency,
				'items': data 
			});
		}
	}
	if (data && measurement == true) {
		if (!search) {
			event_name = 'view_item_list';
		} else {
			event_name = 'view_search_results';
		}
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendGa4MeasurementImpressions',
		data: {products: data, event_name: event_name, heading: heading},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_ga4_measurement_sent');
			}
		});
	}
}	 

function sendGa4Details(data, measurement = false) {
	if (data && measurement == false) {
		if (typeof gtag != 'undefined') {
			gtag('event', 'view_item', data);
		}
		console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_ga4_sent');
	}	
	if (data && measurement == true) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendGa4Details',
		data: {products : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_ga4_measurement_sent');
			}
		});
	}
}	 

function sendGa4Cart(data) { 
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendGa4Cart',
		data: {cart : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'ecommerce_ga4_cart_sent');
			}
		});
	}
}	 

function sendFacebookDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendFacebookDetails',
		data: {products: data['products'], event_id: data['event_id'], time: data['time'], url: window.location.href},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_facebook_sent');
			}
		});
	}
}	 

function sendTiktokDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendTiktokDetails',
		data: {properties: data['properties'], event_id: data['event_id'], url: window.location.href},
			dataType: 'json',
            success: function() {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_tiktok_sent'); 
			}
		});
	}
}	 

function sendFacebookCart(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendFacebookCart',
		data: {cart : data, url : window.location.href},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'facebook_cart_sent');
			}
		});
	}
}	 

function sendTiktokCart(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendTiktokCart',
		data: {cart : data, url : window.location.href},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'tiktok_cart_sent');
			}
		});
	}
}	 

function sendFacebookCategoryDetails(data, search_page) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendFacebookCategory',
		data: {products: data['products'], event_id: data['event_id'], time: data['time'], url: window.location.href, search: search_page},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'category_details_facebook_sent');
			}
		});
	}
}	 

function sendEsputnikDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendEsputnik',
		data: {product : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'details_esputnik_sent');
			}
		});
	}
}

function sendEsputnikCategoryDetails(data) {
	if (data) {
		$.ajax({ 
        type: 'post',
        url:  'index.php?route=common/remarketing/sendEsputnikCategory',
		data: {category : data},
			dataType: 'json',
            success: function(json) {
				console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'category_esputnik_sent');
			}
		});
	}
}

function sendGoogleRemarketing(data) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'remarketing_event_sent');

	if (typeof gtag != 'undefined') {
		gtag('event', data['event'], data['data']);
	}
}	

function sendWishList(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'wishlist_sent');
	
	heading = $('h1').text();
	
	if (typeof heading == 'undefined' || heading == '') {
		heading = 'other';
	}
	
	if (json['remarketing']['facebook_status'] != null && json['remarketing']['facebook_pixel_status'] != null) {
		if (typeof fbq != 'undefined') {
			fbq('track', 'AddToWishlist', json['remarketing']['facebook_pixel_event'], {eventID: json['remarketing']['event_id']}); 
		}
	}
	
	if (json['remarketing']['tiktok_status'] != null && json['remarketing']['tiktok_pixel_status'] != null) {
		if (typeof ttq != 'undefined') { 
			ttq.track('AddToWishlist', json['remarketing']['tiktok_event'], {eventID: json['remarketing']['event_id']}); 
		}
	}
	
	if (json['remarketing']['snapchat_status'] != null && json['remarketing']['snapchat_pixel_status'] != null) {
		if (typeof snaptr != 'undefined') {
			snaptr('track','ADD_TO_WISHLIST', json['remarketing']['snapchat_event']);
		}
	}
	
	if (json['remarketing']['ecommerce_ga4_status'] != null) {
		if (typeof gtag != 'undefined') {
			json['remarketing']['ecommerce_ga4_event']['items'][0]['item_list_name'] = heading;
			gtag('event', 'add_to_wishlist', json['remarketing']['ecommerce_ga4_event']);
		}
	}
	
	if (json['remarketing']['ecommerce_status'] !== null) {
		window.dataLayer = window.dataLayer || [];
		dataLayer.push({ ecommerce: null });
		dataLayer.push(json['remarketing']['ga4_datalayer']);  
	} 
	
	if (json['remarketing']['esputnik_status'] != null) {
		if (typeof eS != 'undefined') {
			eS('sendEvent', 'AddToWishlist', json['remarketing']['esputnik_event']);
		}
	}
	
	if (typeof events_wishlist != 'undefined') {
		events_wishlist();
	}
}

function remarketingQuickOrder(json) {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'quick_order_sent');
	
	if (json['remarketing']) {
		
		if (json['remarketing']['google_status'] != null) {
			if (typeof gtag != 'undefined') {
				gtag('event', 'purchase', json['remarketing']['ads_event']);
			}
		
			if (json['remarketing']['google_ads_identifier'] != '') {
				if (typeof gtag != 'undefined') {
					gtag('event', 'conversion', json['remarketing']['ads_conversion_event']);
				}
			}
		} 
		
		if (json['remarketing']['ecommerce_status'] != null) {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({ ecommerce: null });
			dataLayer.push(json['remarketing']['ga4_datalayer']);
		}

		if (json['remarketing']['ecommerce_ga4_status'] != null) {
			if (typeof gtag != 'undefined') {
				gtag('event', 'purchase', json['remarketing']['ga4_event']);	
			}			
		}
		
		if (json['remarketing']['facebook_status'] != null && json['remarketing']['facebook_pixel_status'] != null) {
			 if (typeof fbq != 'undefined') {
				fbq('track', 'Purchase', json['remarketing']['facebook_event'], {'eventID': json['remarketing']['fb_event_id']}); 
				if (json['remarketing']['facebook_lead'] != null) {
					fbq('track', 'Lead', json['remarketing']['facebook_lead_event'], {'eventID': json['remarketing']['fb_lead_event_id']}); 
				}
			}
		} 
		
		if (json['remarketing']['tiktok_status'] != null && json['remarketing']['tiktok_pixel_status'] != null) {
			 if (typeof ttq != 'undefined') {
				ttq.track('Purchase', json['remarketing']['tt_event'], {'eventID': json['remarketing']['tt_event_id']}); 
			}
		}
		
		if (json['remarketing']['snapchat_status'] != null && json['remarketing']['snapchat_pixel_status'] != null) {
			if (typeof snaptr != 'undefined') {
				snaptr('track','PURCHASE', json['remarketing']['snapchat_event']);
			}
		}

		if (json['remarketing']['reviews_status'] != null && json['remarketing']['reviews_status'] != false) {
			$.getScript('https://apis.google.com/js/platform.js?onload=renderOptIn');
			window.renderOptIn = function() {  
				window.gapi.load('surveyoptin', function() {
					window.gapi.surveyoptin.render(json['remarketing']['reviews_event']);
				})
			}
		}

		if (json['remarketing']['esputnik_status'] != null) {
			if (typeof eS != 'undefined') {
				eS('sendEvent', 'PurchasedItems', json['remarketing']['esputnik_event']); 
			}
		}
		
		if (typeof quickPurchase != 'undefined') { 
			quickPurchase(json['remarketing']['order_id'], json['remarketing']['default_total']);
		}
		
	}
}
	
function decodePostParams(str) {
    return (str || document.location.search).replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0];
}

/*click on phone or social*/
  document.addEventListener('DOMContentLoaded', function () {
    // Телефон
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
    phoneLinks.forEach(link => {
      link.addEventListener('click', function () {
        if (typeof ttq !== 'undefined') {
          ttq.track('Contact', {
            content_name: 'Phone Click',
            phone_number: link.getAttribute('href')
          });
		  console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'Phone Click');
        }
      });
    });
	
	

    // Месенджери та соцмережі
    const socialSelectors = [
      'a[href*="t.me"]',
      'a[href*="viber://"]',
      'a[href*="wa.me"]',
      'a[href*="m.me"]',
      'a[href*="facebook.com"]',
      'a[href*="instagram.com"]',
      'a[href*="youtube.com"]',
      'a[href*="tiktok.com"]'
    ];

    const socialLinks = document.querySelectorAll(socialSelectors.join(','));

    socialLinks.forEach(link => {
      link.addEventListener('click', function () {
        if (typeof ttq !== 'undefined') {
          ttq.track('Contact', {
            content_name: 'Social Click',
            social_platform: getPlatformName(link.href),
            link_url: link.href
          });
		  console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'Social Click');
        }
      });
    });

    // Допоміжна функція для визначення платформи
    function getPlatformName(url) {
      if (url.includes('t.me')) return 'Telegram';
      if (url.includes('viber://')) return 'Viber';
      if (url.includes('wa.me')) return 'WhatsApp';
      if (url.includes('m.me')) return 'Messenger';
      if (url.includes('facebook.com')) return 'Facebook';
      if (url.includes('instagram.com')) return 'Instagram';
      if (url.includes('youtube.com')) return 'YouTube';
      if (url.includes('tiktok.com')) return 'TikTok';
      return 'Other';
    }
  });
/*click on phone or social*/

$(document).ready(function() {
	console.log ('%c%s', 'color: green; font: 1.2rem/1 Tahoma;', 'sp remarketing 6.4.55590605844.57093 start');

	$.each($("[onclick*='cart.add'], [onclick*='get_revpopup_cart'], [onclick*='addToCart'], [onclick*='get_oct_popup_add_to_cart']"), function() {
		product_id = $(this).attr('onclick').match(/[0-9]+/);
		$(this).addClass('remarketing_cart_button').attr('data-product_id', product_id);
	});
	
	$(document).ajaxSuccess(function(event, xhr, settings) {

		if (settings.url.indexOf('checkout/cart/add') != -1 || settings.url.indexOf('extension/module/technics/technicscart/fastadd2cart') != -1 || settings.url.indexOf('checkout/cart/add&oct_dirrect_add=1') != -1 || settings.url.indexOf('madeshop/cart/add') != -1 || settings.url.indexOf('extension/module/frametheme/ft_cart/add') != -1 || settings.url.indexOf('extension/basel/basel_features/add_to_cart') != -1 || settings.url.indexOf('octemplates/module/oct_popup_cart/status_cart') != -1) {
			if (typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof remarketingAddToCart == 'function') {
					remarketingAddToCart(xhr.responseJSON);
				}
			}
		}
		
		if (settings.url.indexOf('checkout/cart/remove') != -1 || settings.url.indexOf('octemplates/module/oct_popup_cart/status_cart') != -1) {
			if (typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof remarketingRemoveFromCart == 'function') {
					remarketingRemoveFromCart(xhr.responseJSON);
				}
			}
		}
		
		if (settings.url.indexOf('account/wishlist/add') != -1) {
			if (typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof sendWishList == 'function') {
					sendWishList(xhr.responseJSON);
				}
			} 
		}
		
		if (settings.url.indexOf('extension/module/luxshop_newfastordercart') != -1 || settings.url.indexOf('extension/module/luxshop_newfastorder') != -1 || settings.url.indexOf('index.php?route=extension/module/cyber_newfastordercart') != -1 || settings.url.indexOf('extension/module/cyber_newfastorder') != -1 || settings.url.indexOf('extension/module/chameleon_newfastorder/addFastOrder') != -1 || settings.url.indexOf('extension/module/newfastorder') != -1 || settings.url.indexOf('extension/module/newfastordercart') != -1 || settings.url.indexOf('extension/module/uni_quick_order/add') != -1) {
			if (settings.type == 'POST' && typeof xhr.responseJSON['success'] !== 'undefined' && typeof xhr.responseJSON['remarketing'] !== 'undefined') {
				if (typeof remarketingQuickOrder == 'function') {
					remarketingQuickOrder(xhr.responseJSON);
				}
			}
		} 
		
		if (settings.url.indexOf('checkout/simplecheckout&group=0') != -1) {
			simple_data = decodePostParams(decodeURI(settings.data));

			if (simple_data.remove !== 'undefined' && simple_data.remove !== '') {
				quantity_key = 'quantity[' + simple_data.remove + ']';
				quantity = simple_data[quantity_key]; 
				
				if (typeof cart_products[simple_data.remove] !== 'undefined') {
					cart_product_id = cart_products[simple_data.remove]['product_id'];
					if (typeof remarketingRemoveFromSimpleCart == 'function') {
						remarketingRemoveFromSimpleCart(cart_product_id, quantity);
					}
				}
			}
		}
		
		if (settings.url.indexOf('checkout/simplecheckout/prevent_delete') != -1) {
			if (typeof fbq != 'undefined' && typeof facebook_payment_data != 'undefined') {
				fbq('track', 'AddPaymentInfo', facebook_payment_data);
			}
			 
			if (typeof ttq != 'undefined' && typeof tiktok_payment_data != 'undefined') {
				ttq('track', 'AddPaymentInfo', tiktok_payment_data);
			}
			 
			if (typeof gtag != 'undefined' && typeof ga4_payment_data != 'undefined') {
				gtag('event', 'add_payment_info', ga4_payment_data);
			}
		}
	});
	/* 6.4.55590605844.57093 */
});
