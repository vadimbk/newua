<?php
/*
 * Shoputils
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.3.0.x-3.1.x.ENG.TXT
  * It is also available through the world-wide-web at this URL:
 * http://opencart.shoputils.com/LICENSE.3.0.x-3.1.x.ENG.TXT
 * 
 * =================================================================
 *                 OPENCART/ocStore 3.0.x-3.1.x USAGE NOTICE
 * =================================================================
 * This package designed for Opencart/ocStore 3.0.x-3.1.x
 * Shoputils does not guarantee correct work of this extension
 * on any other Opencart edition except Opencart/ocStore 3.0.x-3.1.x.
 * Shoputils does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
*/

// Heading
$_['heading_title']             = 'Накопительные скидки, v1.3';

//Buttons
$_['button_remove_discount']    = 'Удалить&nbsp;cкидку';
$_['button_add_discount']       = 'Добавить&nbsp;скидку';

//Tabs
$_['tab_general']               = 'Общие';
$_['tab_cumulative_discounts']  = 'Скидки';

// Text
$_['text_enabled']          = 'On';
$_['text_disabled']         = 'Off';
$_['text_total']            = 'Учитывать в заказе';
$_['text_success']          = 'Настройки модуля "%s" обновлены!';
$_['text_default']          = 'Основной магазин';
$_['text_setting_general']  = 'Общие настройки';
$_['text_setting_summ']     = 'Настройки накопительной суммы';
$_['text_setting_discount'] = 'Настройки скидки в текущем заказе';
$_['text_setting_other']    = 'Настройки модуля скидок';
$_['text_copyright']        = 'Модуль "%s" разработан <a href="https://opencartforum.com/profile/3463-shoputils/" target="_blank">ShopUtils</a>. Вопросы по техподдержке и работе модуля отправляйте через сайт <a href="https://opencart.market/?route=information/contact" target="_blank">https://opencart.market</a>.<br />&copy; ShopUtils 2010 &mdash; %s';

// Entry
$_['entry_status']                        = 'Статус:';
$_['entry_sort_order']                    = 'Порядок сортировки:';
$_['entry_discount_order_statuses']       = 'Статусы оплаченных заказов:';
$_['entry_discount_totals']               = 'Прибавлять к накопленной сумме данные следующих модулей учета в заказе:';
$_['entry_discount_disallow_categories']  = 'Запрещенные категории:';
$_['entry_description_before']            = 'Страница скидок (начало):';
$_['entry_description_after']             = 'Страница скидок (конец):';
$_['entry_discount_description']          = 'Описание скидки';
$_['entry_discount_description_default']  = 'Скидка N%, при покупке товаров на сумму более Y рублей, в течении Z';
$_['entry_discount_params']               = 'Параметры';
$_['entry_discount_customer_groups']      = 'Группы покупателей';
$_['entry_discount_days']                 = 'Дни';
$_['entry_discount_summ']                 = 'Сумма (%s)';
$_['entry_discount_percent']              = 'Процент';
$_['entry_discount_products_special']     = 'Не учитывать товары по акциям';
$_['entry_discount_first_order']          = 'Учитывать скидку в текущем (оформляемом) заказе';
$_['entry_discount_stores']               = 'Магазины';

// Help
$_['help_discount_order_statuses']    = 'Статусы заказов, для которых будет расчитываться накопительная сумма';
$_['help_discount_totals']            = 'Сумма модулей учета в заказе, для которых будет расчитываться накопительная сумма';
//$_['help_discount_disallow_categories'] = 'Выберите категории, товары из которых не будут прибавляться к накопленной сумме и также будут исключены из текущей скидки';
$_['help_discount_disallow_categories'] = 'Выберите категории, товары из которых будут исключены из текущей скидки';
$_['help_description_before']         = 'Текст на странице с описанием всех скидок, до списка скидок';
$_['help_description_after']          = 'Текст на странице с описанием всех скидок, после списка скидок';
$_['help_discount_description']       = 'Пример: Скидка 10% при заказе на сумму 10000 рублей в течении месяца';
$_['help_discount_params']            = 'Параметры скидки (дни, сумма, процент)';
$_['help_discount_customer_groups']   = 'Выбор групп покупателей для которых будет разрешена скидка';
$_['help_discount_days']              = 'Количество дней в течении которых будут учитываться оплаченные заказы';
$_['help_discount_summ']              = 'Сумма оплаченных заказов, которую необходимо накопить за установленное количество дней, чтобы скидка заработала';
$_['help_discount_percent']           = 'Процент скидки от суммы цен товаров в заказе';
$_['help_discount_products_special']  = 'Если опция установлена, в скидке текущего заказа НЕ БУДУТ учитываться: акционные товары (вкладка "Акции" в карточке товара)';
$_['help_discount_first_order']       = 'Если опция установлена и у покупателя еще не было ни одной покупки, накопительная скидка начнет действовать с первого, оформляемого покупателем заказа';
$_['help_discount_stores']            = 'Выбор магазинов для которых будет разрешена скидка';
$_['help_discount']                   = ' - При оформлении заказа будет выбрана максимальная скидка (если не выбрана опция "Учитывать скидку в текущем (оформляемом) заказе"), с учетом цены всех товаров в заказах со статусом оплаченныx, дата создания у которых входит в период установленный параметром "Дни".<br/> - Модуль работает только для зарегистрированных пользователей.';

//lic
$_['text_get_key']          = 'Если Вы не знаете как получить лицензионный ключ - прочтите <a href="https://opencart.market/license_key" target="_blank">инструкцию на нашем официальном сайте</a>.';
$_['text_ok']               = ' - OK';
$_['text_error']            = ' - <span style="color:red;">ERROR</span>';
$_['text_domain']           = 'Ваш домен: <b>%s</b>';
$_['text_loader']           = 'Версия IonCube Loader: <b>%s</b>. Требуется IonCube Loader не ниже v<b>%s</b>';
$_['text_php']              = 'Версия PHP: <b>%s</b>. Требуется PHP не ниже v<b>%s</b>';
$_['entry_key']             = 'Введите лицензионный ключ:';
$_['error_loader']          = '<span style="color:red;">Отсутствует IonCube Loader!</span><br />Обратитесь к Вашему хостеру с просьбой установить IonCube Loader не ниже версии %s';
$_['error_loader_version']  = '<span style="color:red;">Не корректная версия IonCube Loader!</span><br />Обратитесь к Вашему хостеру с просьбой установить IonCube Loader не ниже версии %s';
$_['error_php_version']     = '<span style="color:red;">Не корректная версия PHP!</span>';
$_['error_key']             = 'Недействительный лицензионный ключ!';
$_['error_dir_perm']        = 'Директория "%s" не доступна для записи. Установите необходимые права!';

// Error
$_['error_permission']                = 'У Вас нет прав для управления модулем "%s"!';
$_['error_need_select_order_status']  = 'Необходимо выбрать хотя бы один статус заказа!';
$_['error_need_select_order_total']   = 'Необходимо выбрать хотя бы один модуль учета в заказе!';
?>