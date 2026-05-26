<?php
// Heading
$_['heading_title']                     = 'Ecommerce Google Analytics 4<span style="font-size:12px; color:#999999"> by <a href="https://vanstudio.co.ua/redirect-more" style="font-size:1em; color:#999999" target="_blank">VanStudio</a></span>';

// Text
$_['text_title']                        = 'Ecommerce GA4';
$_['text_home']                         = '<i class="fa fa-home"></i>';
$_['text_success']                      = '<b>Success:</b> You have modified Ecommerce GA4 extension settings!';
$_['text_session_expired']              = '<b>Attention:</b> Your session has expired, please log in again!';
$_['text_validation_enabled']           = '<b>Warning:</b> Ecommerce <b>tracking</b> for Google Analytics 4 <b>is disabled in Validation Mode</b>, instead the tracking data is sent to the Google Validation Server and validation results are written to the logs!';
$_['text_multi_store_disabled']         = '<b>Warning:</b> The Multi-Store option is disabled in the module settings for the default store! The settings for this store will be applied only after enabling the Multi-Store option, otherwise the settings for the default store will be used.';
$_['text_page_refresh']                 = 'The web page will be refreshed and all unsaved module settings will be lost. Continue?';
$_['text_gmp_only']                     = 'meas. protocol only';
$_['text_option_not_available']         = 'This option is not available for the selected measurement type';
$_['text_option_partially_available']   = 'This option is supported by all events tracked by Measurement Protocol but only purchase (if simple mode disabled) and refund events for other tracking types (gtag.js & GTM)';
$_['text_extensions']                   = 'Extensions';
$_['text_enabled']                      = 'Enabled';
$_['text_disabled']                     = 'Disabled';
$_['text_default']                      = 'Default';
$_['text_optional']                     = 'optional';
$_['text_tab_required']                 = '* - tab contains required field(s)';
$_['text_incompatible_version']         = 'The installed version of the Ecommerce GA4 extension is not compatible with your version of OpenCart store, please download and install the extension version compatible with your OpenCart (For more details, see FAQ #104)<br><br><a href="#" class="btn btn-default" onclick="history.back()"><i class="fa fa-reply" aria-hidden="true"></i> Back</a>';

$_['text_js_position_0']                = 'Header - position before the closing &lt;/head&gt; tag';
$_['text_js_position_1']                = 'Header - position before the list of module scripts';
$_['text_js_position_2']                = 'Footer - position before the list of module scripts';
$_['text_js_position_3']                = 'Footer - position before the closing &lt;/body&gt; tag';
$_['text_list_limit_skip']              = '<i class="fa fa-scissors"></i> Skip tracking items that exceed the specified limit';
$_['text_list_limit_multi_event']       = '<i class="fa fa-clone"></i> Track items that exceed the specified limit as a new event';
$_['text_multicurrency']                = 'Multicurrency';
$_['text_multilingual']                 = 'Multilingual';
$_['text_product_id']                   = 'product ID (default)';
$_['text_product_category_0']           = 'Type 1 - Each separate product category (without full path) as separate item parameter';
$_['text_product_category_1']           = 'Type 2 - Full path of each separate product category as separate item parameter';
$_['text_product_category_2']           = 'Type 3 - Each element (category) from full path of one product category as separate item parameter';
$_['text_product_category_3']           = 'Type 4 - One (any) product category (without full path)';
$_['text_non_personalized_ads']         = '<a href="%s" target="_blank"></a>';

$_['text_statuses_not_match']           = '<b>Warning:</b> The selected statutes do not match the processing or completion statuses selected for orders in the store settings, this can lead to orders not being tracked!<button type="button" class="btn btn-warning btn-sm pull-right" onclick="showModalImg(\'%s\')"><i class="fa fa-eye" aria-hidden="true"></i></button>';
$_['text_status_unregistered']          = '<span class="label label-default">Unregistered</span>';
$_['text_status_client_id_not_set']     = '<span class="label label-warning">Client ID Not Set</span>';
$_['text_status_untracked']             = '<span class="label label-primary">Untracked</span>';
$_['text_status_tracked']               = '<span class="label label-success"><i class="fa fa-check" aria-hidden="true"></i> Tracked</span>';
$_['text_status_fully_refunded']        = ' <span class="label label-danger">Fully Refunded</span>';
$_['text_status_partly_refunded']       = ' <span class="label label-info">Partly Refunded</span>';
$_['text_customer']                     = 'Customer';
$_['text_store']                        = 'Store';
$_['text_registration']                 = 'Registration';
$_['text_tracking']                     = 'Tracking';
$_['text_refund']                       = 'Refund';
$_['text_missing']                      = 'Missing';

$_['text_alert_custom_definition']      = '<b>Pay Attention:</b> If you have a standard property, avoid creating <a href="%s" target="_blank">high-cardinality</a> custom dimensions. High-cardinality dimensions are dimensions with more than 500 unique values per day. These dimensions may negatively impact your reports and cause data to aggregate under <a href="%s" target="_blank">the (other) row</a>. For example, high-cardinality dimensions include Invoice Number (i.e., when you want to collect an Invoice Number for each distinct order), Email, IP address and etc.';
$_['text_event_scope_product']          = 'Item/Product (event scope)';
$_['text_event_scope_order']            = 'Purchase/Order (event scope)';
$_['text_user_scope_customer']          = 'Customer (user scope)';
$_['text_definition_left']              = 'custom definitions left';

$_['text_internal_traffic']             = '<a href="%s" target="_blank">See here how to set up internal traffic filtering.</a>';
$_['text_example']                      = 'For example';
$_['text_your_ip']                      = 'your current IP address';

$_['text_logs_cleared']                 = 'Logs successfully cleared!';

$_['text_license_alert']                = 'Note: Please use a unique license key (order ID) for each individual OpenCart live site but multi-store and test site are allowed by one license key!';
$_['text_support_alert']                = 'Note: Please check out this <a href="%s" target="_blank">Frequently Asked Questions</a> before sending a support request - your question may have already been answered!';

$_['text_ext']                          = 'Extension';
$_['text_info']                         = 'Info';
$_['text_author']                       = 'Author';
$_['text_version']                      = 'Version';
$_['text_installation_date']            = 'Installation Date';

// Entry
$_['entry_store']                       = 'Store';
$_['entry_status']                      = 'Status';
$_['entry_bulk_status']                 = 'Bulk Status Change';
$_['entry_multi_store']                 = 'Multi-Store';
$_['entry_type']                        = 'Measurement Type';
$_['entry_measurement_id']              = 'Measurement ID';
$_['entry_measurement_secret']          = 'Measurement Protocol API Secret Key';
$_['entry_validation']                  = 'Validation Mode';
$_['entry_debug']                       = 'Debug View Mode';

$_['entry_js_position']                 = 'JavaScript Position';
$_['entry_theme']                       = 'OpenCart Theme';
$_['entry_language']                    = 'Language';
$_['entry_currency']                    = 'Currency';
$_['entry_affiliation']                 = 'Affiliation';
$_['entry_list_limit']                  = 'Items Per List View Event';
$_['entry_extended_list']               = 'Extended List Parameters';
$_['entry_client_id']                   = 'Client ID';
$_['entry_product_id']                  = 'Item ID';
$_['entry_product_category']            = 'Item Category';
$_['entry_tax']                         = 'Item Price + Taxes';
$_['entry_total_shipping']              = 'Shipping + Total';
$_['entry_total_tax']                   = 'Taxes + Total';
$_['entry_user_id']                     = 'User ID';
$_['entry_timestamp']                   = 'Timestamp';
$_['entry_non_personalized_ads']        = 'Non Personalized Ads';

$_['entry_view_simple']                 = 'Simple Mode';

$_['entry_select_promotion']            = 'Select Promotion';

$_['entry_wish_add']                    = 'Add To Wishlist';

$_['entry_cart_add']                    = 'Add To Cart';
$_['entry_cart_edit']                   = 'Cart Update';
$_['entry_cart_remove']                 = 'Remove From Cart';

$_['entry_checkout_extension']          = 'Checkout Extension';
$_['entry_checkout_custom']             = 'Checkout Route';
$_['entry_checkout_shipping']           = 'Shipping Trigger';
$_['entry_checkout_payment']            = 'Payment Trigger';
$_['entry_ajax_load']                   = 'Ajax load';

$_['entry_purchase_simple']             = 'Simple Mode';
$_['entry_purchase_tracking_status']    = 'Order Status for Purchase Tracking';

$_['entry_order_id']                    = 'Order ID';
$_['entry_order_status']                = 'Order Status';
$_['entry_total']                       = 'Total';
$_['entry_date_added']                  = 'Date Added';
$_['entry_date_modified']               = 'Date Modified';
$_['entry_quantity']                    = 'Quantity';

$_['entry_refund_tracking_status']      = 'Order Status for Refund Tracking';

$_['entry_promotion_simple']            = 'Simple Mode';

$_['entry_object']                      = 'Object';
$_['entry_parameter_value']             = 'Parameter Value';
$_['entry_parameter_name']              = 'Parameter Name';

$_['entry_traffic_type']                = 'Traffic Type';
$_['entry_exclude_u_agent']             = 'User-Agent';
$_['entry_exclude_ip']                  = 'IP Address';
$_['entry_exclude_admin']               = 'Administrators';

$_['entry_extended_log']                = 'Extended Logs';

$_['entry_license_id']                  = 'License Key / Order ID';
$_['entry_support_data']                = 'Provide data to autocomplete support form fields';

// Button
$_['button_mp']                         = 'Measurement Protocol';
$_['button_gtag']                       = 'Global Site Tag - gtag.js (Recommended)';
$_['button_gtm']                        = 'Google Tag Manager';

$_['button_filter']                     = 'Filter';
$_['button_clean_filter']               = 'Clean Filter';
$_['button_track']                      = 'Track / send to Google Analytics';
$_['button_cancel']                     = 'Cancel';
$_['button_full_refund']                = 'Full Order Refund';
$_['button_refund_product']             = 'Refund Product';

$_['button_license']                    = 'License';
$_['button_info']                       = 'Info';
$_['button_support']                    = 'Support';
$_['button_create_request']             = 'Create Support Request';
$_['button_official_page']              = 'Official Page';
$_['button_faq']                        = 'FAQ';
$_['button_demo_site']                  = 'Demo Site';
$_['button_more_extensions']            = 'More Extensions';
$_['button_changelog']                  = 'Changelog';
$_['button_demo_admin']                 = 'Demo Admin';
$_['button_add_this']                   = 'Add this value';
$_['button_add']                        = 'Add';

// Tooltip
$_['tooltip_translator']                = 'Google Translate (legacy)';
$_['tooltip_save']                      = 'Save';
$_['tooltip_cancel']                    = 'Cancel';
$_['tooltip_show_demo']                 = 'Show Demo';
$_['tooltip_add_and_track_btn']         = 'Register and track';
$_['tooltip_edit_and_track_btn']        = 'Add client ID and track';
$_['tooltip_track_btn']                 = 'Track / send to Google Analytics';
$_['tooltip_refund_btn']                = 'Order Refund';
$_['tooltip_refresh_btn']               = 'Refresh';
$_['tooltip_get_client_id']             = 'Use the Google Analytics client ID for your browser';
$_['tooltip_refund_product_btn']        = 'Refund Product';
$_['tooltip_refunded_quantity']         = 'Quantity of products for which a refund has already been tracked';

$_['tooltip_status']                    = 'Completely enable/disable the extension';
$_['tooltip_bulk_status']               = 'Enable/Disable statuses of all tracking sections in one click';
$_['tooltip_multi_store']               = 'Separate module settings for each store from OpenCart multi-store list';
$_['tooltip_type']                      = 'Select the measurement / implementation type for tracking events of Google Analytics 4 Ecommerce';
$_['tooltip_measurement_id']            = 'Your \'G-XXXXXXXXXX\' identifier for a Data Stream. Found in the Google Analytics UI under: Admin > Data Streams > choose your stream > Measurement ID';
$_['tooltip_found_measurement_id']      = 'Use the measurement ID from the Google Analytics extension';
$_['tooltip_measurement_secret']        = 'An API Secret key that is generated through the Google Analytics UI. To create a new secret key, navigate in the Google Analytics UI to: Admin > Data Streams > choose your stream > Measurement Protocol > Create';
$_['tooltip_validation']                = 'Send tracking data to the Google Validation Server <b>(disable tracking for Google Analytics)</b> and write the validation result to the logs.<br>Note that the logs option is required in validation mode and will be activated automatically.';
$_['tooltip_debug']                     = 'Add a \'debug_mode\' parameter to each ecommerce event tracked by this extension, which allows you to monitor them in the DebugView section of Google Analytics 4';

$_['tooltip_js_position']               = 'Set the position for adding JavaScript file of this extension on the front end of the site. Change this option if the following error occurs: \'JavaScript file of ecommerce tracking extension not found\'.';
$_['tooltip_theme']                     = 'Set the OpenCart theme you are using, if it is in the drop-down list, otherwise select the default value';
$_['tooltip_language']                  = 'Select the language for tracking ecommerce data without duplicating records in different languages in the GA reports (text in other languages will be automatically translated before sending to Google Analytics).<br>Or select the multilingual for tracking ecommerce data in all languages without translation (as is).';
$_['tooltip_currency']                  = 'Select the currency of your GA account to convert ecommerce data from other currencies at the rate specified in the OpenCart store settings before tracking.<br>Or select the multicurrency for tracking ecommerce data in different currencies (as is) and conversion into the currency of your GA account at the Google exchange rate.';
$_['tooltip_affiliation']               = 'Specify store name or department for \'affiliation\' parameter of ecommerce tracking.<br>You can leave this field empty to use the default store name from the store settings.';
$_['tooltip_user_id']                   = '';
$_['tooltip_list_limit']                = 'Maximum number of tracking items per one view of the list (per one request). You can try lowering the limit, if list view events are not tracked or return an error in validation mode as this may be caused by exceeding the allowable limits for tracking events through the measurement protocol, for example when the names of your products are unusually long.';
$_['tooltip_extended_list']             = 'Allows you to track category/manufacturer names as a list name parameter and category/manufacturer IDs as part of a list ID parameter';
$_['tooltip_client_id']                 = 'Allows you to track ecommerce events from clients for which it was not possible to get Google Analytics client ID from browser cookies. Note that all events without client ID will be tracked as the activity of the same one user whose ID will be specified.';
$_['tooltip_default_client_id']         = 'Use your current client ID';
$_['tooltip_product_id']                = 'Set the product parameter to track as the item ID parameter. Note that for products with an empty value of the selected parameter (other than product ID), the default product ID will be used as the item ID parameter because this parameter is required and cannot be empty.';
$_['tooltip_product_category']          = 'Set how to track item category parameters';
$_['tooltip_tax']                       = 'Track item price including taxes';
$_['tooltip_total_shipping']            = 'Include shipping to total order amount';
$_['tooltip_total_tax']                 = 'Include taxes to total order amount';
$_['tooltip_user_id']                   = 'Optional. Select the field from the customer table in the database that will be tracked as User ID parameter - a customer-generated ID used to differentiate between users and unify user events in reporting and exploration';
$_['tooltip_timestamp']                 = 'If less than 72 hours have passed since a purchase was added, Analytics reports will show the actual date the purchase was added instead of the current tracking date (<b>timestamp_micros</b> parameter is used)';
$_['tooltip_non_personalized_ads']      = 'Enable/disable the use of ecommerce tracking events for personalized ads (<b>non_personalized_ads</b> parameter)';

$_['tooltip_view_status']               = 'Enable/disable tracking of view events';
$_['tooltip_view_simple']               = 'Measure item list view and item detail view events with minimal impact on page loading time / site speed. It is recommended to use only for highly loaded sites with a large number of products.';

$_['tooltip_select_status']             = 'Enable/disable select event tracking';

$_['tooltip_wish_status']               = 'Enable/disable tracking of add to wishlist event';

$_['tooltip_cart_status']               = 'Enable/disable tracking of shopping cart events';

$_['tooltip_checkout_status']           = 'Enable/disable checkout event tracking';
$_['tooltip_checkout_custom']           = 'Specify URL route parameter or SEO-keyword of your custom checkout page to initialize checkout events tracking if any of the checkout events (begin_checkout, add_shipping_info, add_payment_info) are not tracked by default. Use a comma separator to specify more than one value.';
$_['tooltip_checkout_extension']        = 'Select an additional extension that you use for the checkout page. Leave the default value if you are not using any extension or the extension you are using is not in the dropdown list.';
$_['tooltip_checkout_shipping']         = 'Specify an ID or class of the button tag that submits the shipping method selection form or confirmation (submit) button of the entire checkout process if there is no division into steps as in the case of the quick checkout page. Use a comma separator to specify more than one value. For example: #shipping-method-btn, .checkout-button-confirm, input[type=submit]';
$_['tooltip_checkout_payment']          = 'Specify an ID or class of the button tag that submits the payment method selection form or confirmation (submit) button of the entire checkout process if there is no division into steps as in the case of the quick checkout page. Use a comma separator to specify more than one value. For example: #payment-method-btn, .checkout-button-confirm, input[type=submit]';
$_['tooltip_ajax_load']                 = 'Select this option if the element (HTML tag) you specified as a trigger is loaded after loading the checkout page or refreshes without reloading the page and as a result tracking does not work';

$_['tooltip_purchase_status']           = 'Enable/disable purchase event tracking';
$_['tooltip_purchase_simple']           = 'Measurement of purchase events through the successful checkout page';
$_['tooltip_purchase_tracking_status']  = 'Allows you to select one or more order statuses after reaching which an order will be tracked/sent to Google Analytics as purchase event';
$_['tooltip_client_id']                 = 'Specify a valid Google Analytics client ID that will be used if the extension is unable to retrieve a real client ID from browser cookies. This option will prevent you from losing tracking data from users that have cookies disabled.';

$_['tooltip_refund_status']             = 'Enable/disable refund event tracking';
$_['tooltip_refund_tracking_status']    = 'Allows you to select one or more order statuses after reaching which an order will be tracked/sent to Google Analytics as refund event';

$_['tooltip_promotion_status']          = 'Enable/disable tracking of promotion events – measure promotion views and clicks in default OpenCart modules (carousel, slider, banner) but only for items containing a product link';
$_['tooltip_promotion_simple']          = 'Measure promotion view events with minimal impact on page loading time / site speed. It is recommended to use only for highly loaded sites with a large number of products.';

$_['tooltip_custom_definition_quota']   = '<b>Quota information</b><br>Custom dimension:<br>user scoped – 25 max.<br>event scoped – 50 max.<br>Custom metric:<br>event scoped – 50 max.<br>';

$_['tooltip_traffic_type']              = 'Specify a value for the \'traffic_type\' parameter to add it to filtered events instead of excluding events completely. The default value for internal traffic is \'internal\'.';
$_['tooltip_exclude_u_agent']           = 'Allows you to exclude/skip event tracking from users whose \'User-Agent\' request header contains the specified word(s).<br> Use a vertical bar character \'|\' as a separator for two or more values.';
$_['tooltip_exclude_ip']                = 'Allows you to exclude/skip event tracking from the specified IP-address(es).<br>Use a new line for each value.';
$_['tooltip_exclude_admin']             = 'Allows you to exclude/skip event tracking from users logged in to the admin panel';

$_['tooltip_log_status']                = 'Enable/disable recording and display of logs';
$_['tooltip_extended_log']              = 'Add all event parameters to logs, if the option is disabled, only a few basic parameters are added';

$_['tooltip_license_id']                = 'The order ID is a unique number assigned to each purchase on opencart.com or vanstudio.co.ua';
$_['tooltip_support_data']              = 'Provide the following information: <br>name – %s<br>e-mail – %s<br>website – %s<br>to automatically fill support form fields';


// Tab
$_['tab_general']                       = 'General';
$_['tab_advanced']                      = 'Advanced';
$_['tab_view']                          = 'View';
$_['tab_select']                        = 'Select';
$_['tab_wish']                          = 'Wishlist';
$_['tab_cart']                          = 'Cart';
$_['tab_checkout']                      = 'Checkout';
$_['tab_purchase']                      = 'Purchase';
$_['tab_refund']                        = 'Refund';
$_['tab_promotion']                     = 'Promotion';
$_['tab_custom_definition']             = 'Custom Definition';
$_['tab_filter']                        = 'Filter';
$_['tab_log']                           = 'Logs';
$_['tab_help']                          = 'Help';

// Column
$_['column_order_id']                   = 'Order ID';
$_['column_tracking_status']            = 'Tracking Status';
$_['column_order_status']               = 'Order Status';

$_['column_product']                    = 'Product';
$_['column_model']                      = 'Model';
$_['column_order_quantity']             = 'Ordered Quantity';
$_['column_price']                      = 'Unit Price';
$_['column_total']                      = 'Total';
$_['column_refund_quantity']            = 'Refunded Quantity';
$_['column_order_info']                 = 'Order Info';
$_['column_date_added']                 = 'Date Added';
$_['column_date_modified']              = 'Date Modified';
$_['column_tracking_dates']             = 'Tracking Dates';
$_['column_action']                     = 'Action';

// Note
$_['note_mp']                           = '<a href="%s" target="_blank">The Google Analytics Measurement Protocol</a> for Google Analytics 4 allows to measure ecommerce by sending events directly to Google Analytics servers via HTTP requests (server-side implementation). This implementation has a number of advantages over gtag.js and GTM, including additional features and options, but so far the events tracked by the measurement protocol are not associated with user data such as source, city, etc. in GA4 reports, so this type of measurement is not recommended for use by default yet.';
$_['note_gtag']                         = 'The global site tag (gtag.js) is a JavaScript tagging framework and API that allows you to send ecommerce event data to Google Analytics 4. For this type of implementation, <a href="%s" target="_blank">the global site tag code snippet</a> must be added on your website.';
$_['note_gtm']                          = 'Google Tag Manager is a tag management system that allows you to quickly and easily update measurement codes and related code fragments collectively known as tags on your website. For this type of implementation, <a href="%s" target="_blank">the code snippets of Google Tag Manager</a> must be added to your website. At the same time, <a href="%s" target="_blank">the Google Analytics: GA4 Configuration tag</a> and <a href="%s" target="_blank">the Google Analytics: GA4 Event tag</a> for each of the ecommerce events must be added by the google tag manager workspace (<a href="%s" target="_blank">here you can find the tag configuration for each GA4 Event tag</a>).<br><a href="%s" target="_blank"><b>GTM container file</b></a> for importing GA4 ecommerce settings (be sure to change the measurement ID for the GA4 Configuration tag after importing).';
$_['note_measurement_id']               = '<a href="%s" target="_blank">How to find Measurement ID (i.e. "G-" ID)?</a>';
$_['note_measurement_secret']           = 'Link to API secrets is located in the additional settings section on same page as Measurement ID';
$_['note_validation_mode']              = '<a href="%s" target="_blank">More information about Measurement Protocol Validating</a>';
$_['note_debug_mode']                   = '<a href="%s" target="_blank">More information about GA4 DebugView</a>';
$_['note_debug_mode_gtm']               = 'This option only applies to purchase (if simple mode disabled) and refund events when using Google Tag Manager type of measurement, for all other events you need to <a href="%s" target="_blank">enable debug mode by Google Tag Manager</a>';

$_['note_theme']                        = '';
$_['note_client_id']                    = 'Ecommerce events for which this client ID will be used will also include other data of the user who owns this ID, such as country, city, source, etc.';
$_['note_user_id']                      = 'See <a href="%s" target="_blank">User-ID for cross-platform analysis</a> for more information on this identifier';
$_['note_non_personalized_ads']         = '<a href="%s" target="_blank">How to completely disable advertising personalization features by gtag.js or GTM</a>.';

$_['note_view_simple']                  = '<strong>Pay Attention!</strong> In simple mode, the following options: <b>Language</b>, <b>Item ID</b>, <b>Item Category</b> and <b>Item Price + Taxes</b> from the advanced settings do not apply to events of item list views and detailed item views. Instead, the following values of these options will be applied: <b>Language</b> = <i>Multilingual</i>, <b>Item ID</b> = <i>product ID</i>, <b>Item Category</b> = <i>Type 4</i>, <b>Item Price + Taxes</b> = <i>the value of the <b>Display Prices With Tax</b> option from the store settings</i>. As a result, this may lead to differences in the values of the above parameters for viewing events and other events in GA reports.';

$_['note_checkout_extension']           = '';

$_['note_refund_quantity']              = 'Max Value = %s (Ordered Quantity - Refunded Quantity)';
$_['note_tracking_status']              = 'Note that each order is tracked/sent to Google Analytics only once after reaching one (any) of the selected statuses';

$_['note_exclude_admin']                = 'Applies only to the default store and does not work for other stores in the multi-store implementation';
$_['note_traffic_type']                 = '<a href="%s" target="_blank">How to filter internal traffic.</a>';

// Legend
$_['legend_general']                    = 'General Settings';
$_['legend_advanced']                   = 'Advanced Settings <p>all options on this tab are optional (not required)</p>';
$_['legend_view']                       = 'Measure product impressions and product detail views <p>by tracking <b>view_item_list</b>, <b>view_cart</b> and <b>view_item</b> GA4 ecommerce events</p>';
$_['legend_select']                     = 'Measure product clicks <p>by tracking <b>select_item</b> GA4 ecommerce event</p>';
$_['legend_wish']                       = 'Measure additions to wishlist<p>by tracking <b>add_to_wishlist</b> GA4 ecommerce events</p>';
$_['legend_wish_alternative']           = 'An alternative way to measure a wishlist event (server-side implementation)<p> use the option below only if the add to wishlist tracking is not working properly</p>';
$_['legend_cart']                       = 'Measure additions to and removals from shopping carts<p>by tracking <b>add_to_cart</b> and <b>remove_from_cart</b> GA4 ecommerce events</p>';
$_['legend_cart_alternative']           = 'An alternative way to measure shopping cart events (server-side implementation)<p> use the options below only if the relevant cart event tracking is not working properly</p>';
$_['legend_checkout']                   = 'Measure checkouts <p>by tracking <b>begin_checkout</b>, <b>add_payment_info</b> and <b>add_shipping_info</b> GA4 ecommerce events</p>';
$_['legend_checkout_custom']            = 'Custom settings <p>use the options below only if one or more checkout events are not tracked after selecting the appropriate checkout extension or by default</p>';
$_['legend_purchase']                   = 'Measure purchases <p>by tracking <b>purchase</b> GA4 ecommerce event</p>';
$_['legend_refund']                     = 'Measure refunds <p>by tracking <b>refund</b> GA4 ecommerce event</p>';
$_['legend_promotion']                  = 'Measure promotion impressions and clicks <p>by tracking <b>view_promotion</b> and <b>select_promotion</b> GA4 ecommerce events</p>';
$_['legend_custom_definition']          = 'Custom Definitions <p>adding additional customer or order parameters to GA4 ecommerce events, that can be set up as <a href="%s" target="_blank">custom definitions</a></p>';
$_['legend_data_filters']               = 'Data Filters <p>exclude spam, web crawlers and internal traffic</p>';
$_['legend_log']                        = 'Logs <p>the logs of most events (except purchase and refund) are also displayed in real time in the browser console</p>';

$_['sub_legend_item']                   = 'Item Options';
$_['sub_legend_purchase']               = 'Purchase Options';
$_['sub_legend_additional_params']      = 'Additional Parameters';

// Error
$_['error_warning']                     = 'Warning: Please check the settings tabs carefully for error!';
$_['error_permission']                  = 'Warning: You do not have permission to modify Ecommerce GA4 module!';
$_['error_client_id']                   = 'Client ID must be between 1 and 64 characters!';
$_['error_measurement_id']              = 'GA4 Measurement ID must begin with the \'G-\' characters!';
$_['error_measurement_id_empty']        = 'Measurement ID field is required!';
$_['error_measurement_id_length']       = 'Measurement ID must be between 12 and 14 characters!';
$_['error_measurement_secret_empty']    = 'Measurement Secret API Key field is required!';
$_['error_measurement_secret_length']   = 'Measurement Secret API Key must be between 22 and 26 characters!';
$_['error_order_status']                = 'You must choose at least one order status!';
$_['error_license_id']                  = 'License Key must be between 6 and 8 characters!';
