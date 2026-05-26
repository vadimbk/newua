<?php
include('seolang.php');

$_['ocmod_langmark_name'] = $_['seolang_model'] ;
$_['ocmod_langmark_version'] = $_['seolang_version'] ;
$_['ocmod_langmark_mod'] = 'langmark';
$_['ocmod_langmark_author'] = 'support.opencartadmin.com';
$_['ocmod_langmark_link'] = 'https://support.opencartadmin.com';
$_['ocmod_langmark_html'] = 'Модифікатор для'. $_['seolang_model'] . 'успішно встановлено';

$_['text_widget_langmark'] = $_['seolang_model'] . ' ' . $_['seolang_version'];
$_['widget_langmark_version'] = $_['seolang_version'];
$_['text_mod_add_langmark'] = '';
$_['text_widget_langmark_settings'] = $_['text_widget_langmark'];

$_['order_langmark'] = '0';

$_['text_separator'] = ' > ';

$_['entry_langmark_widget_status'] = 'Статус модуля';
$_['entry_langmark_widget_status_scripts'] = 'Скрипти (scripts js) <br>тіла списку продуктів';
$_['entry_langmark_widget_content'] = 'CSS селектор тіла списку продуктів';
$_['entry_langmark_widget_breadcrumb'] = 'CSS селектор хлібних крихт';
$_['entry_langmark_widget_h1'] = 'CSS селектор мета тега H1';
$_['entry_langmark_widget_install_success'] = 'Таблиця віджета мітки продуктів успішно встановлена<br>';
$_['entry_langmark_widget_install'] = 'Підключення віджета SEO Багатомовна-успішно<br>';
$_['entry_langmark_widget_types'] = 'Елементи, що видаляються <br>з шаблону';
$_['entry_number'] = 'Номер';
$_['entry_add_langmark_widget_type'] = 'Додати елемент';
$_['entry_url_langmark'] = 'Сторінка налаштувань модуля';


$_['entry_anchor_templates'] = 'Шаблони прив’язки';
$_['entry_anchor_value'] = 'Поточне значення';
$_['entry_anchor_templates_clear'] = 'Очистити';

$_['entry_anchor_templates_tab'] = 'У вкладці (за замовчуванням)';
$_['entry_box_begin_templates'] = 'Блок (початковий HTML код) шаблони';
$_['entry_box_end_templates'] = 'Блок (закриває HTML-код) шаблони';
$_['entry_box_begin_templates_tab'] = 'Блок (початковий HTML код) шаблон у вкладці(за замовчуванням)';
$_['entry_box_end_templates_tab'] = 'Блок (закриває HTML-код) шаблон у вкладці (за замовчуванням)';
$_['entry_box_begin_templates_empty'] = 'Блок (початковий HTML код) шаблон порожній блок(за замовчуванням)';
$_['entry_box_end_templates_empty'] = 'Блок (закриває HTML-код) шаблон порожній блок(за замовчуванням)';
$_['entry_box_begin_value'] = 'Поточне значення';
$_['entry_box_end_value'] = 'Поточне значення';

$_['entry_anchor_templates_html'] = 'html шаблон';
$_['entry_anchor_templates_prepend'] = 'prepend шаблон';
$_['entry_anchor_templates_append'] = 'append шаблон';
$_['entry_anchor_templates_before'] = 'before шаблон';
$_['entry_anchor_templates_after'] = 'after шаблон';
$_['text_anchor_templates_selector'] = 'введіть СЕЛЕКТОР TAG, #ID,.CLASS';

$_['text_adapter_edit'] = 'Адаптувати багатомовну мову';
$_['entry_replace_text'] = 'Значення для заміни';
$_['entry_replace_text_na'] = 'на';


$_['entry_load_template'] = 'Завантажити зразок шаблону';
$_['entry_load_template_new'] = 'Завантажити змінений шаблон';

$_['html_help_adapter'] = <<<EOF
Приберіть зайві теги & Lt; form ...&gt; &lt;/form&gt; &lt;input ...&gt;<br>
Додайте або змініть тегам&lt;a&gt; атрибут href, він повинен бути href=" & lt;?php echo \$language[<strong style="color: green;">'url'</strong>]; ?&gt;"<br>
Внизу, то що знайшов ШІ і спробує замінити

EOF;
/*********************************************************************/

$_['url_text_seolang'] = 'Віджети';
$_['url_back_text'] = 'В налаштування модуля';
$_['url_modules_text'] = 'До списку модулів';

$_['tab_main'] = 'Головна сторінка';
$_['entry_main_title'] = 'Заголовок головної сторінки <br> <span class= "vhelp" >Мета-тег: title</span>';
$_['entry_main_description'] = 'Опис головної сторінки <br> <span class= "vhelp" >Мета-тег: description</span>';
$_['entry_main_keywords'] = 'Ключові слова головної сторінки <br> <span class= "vhelp">Мета-тег: keywords</span>';

$_['tab_ex'] = 'Винятки';
$_['entry_ex_multilang'] = 'В маршрутизаторі <span class= "help" >(роздільник - переклад каретки PHP_EOL)</span>';
$_['entry_ex_multilang_route'] = 'Винятки для route';
$_['entry_ex_multilang_uri'] = 'Винятки для uri';
$_['entry_ex_url'] = 'У формувачі префіксів <span class= "help" >(роздільник - переклад каретки PHP_EOL)</span>';
$_['entry_ex_url_route'] = 'Винятки для route';
$_['entry_ex_url_uri'] = 'Винятки для uri';
$_['entry_add'] = 'Додати';

$_['entry_url_close_slash'] = 'Закривати URL списку слешем /';

$_['entry_main_prefix_status'] = 'Прибрати мовний префікс (якщо він встановлений) для головної сторінки. Решта сторінок буде з префіксом<br>
<span class="vhelp">включати тільки в тому випадку, якщо у вас для всіх "мов" встановлений префікс і ви хочете для якоїсь "мови" використовувати головну без префікса</span>';


$_['text_pagination_title'] = 'сторінка';
$_['text_pagination_title_russian'] = 'сторінка';
$_['text_pagination_title_ukraine'] = 'сторінка';

$_['text_widget_html'] = 'Мовний HTML, HTML вставка';

$_['text_loading_small'] = '<div style=&#92;&#8217;color: #008000;&#92;&#8217;>Завантажується...<i class=&#92;&#8217;fa fa-refresh fa-spin&#92;&#8217;></i></div>';
$_['text_loading'] = '<div>завантажується...<i class="fa fa-refresh fa-spin"></i></div>';
$_['text_loading_langmark'] = '<div>завантажується...<i class="fa fa-refresh fa-spin"></i></div>';


$_['text_langmark_widget'] = 'Віджети';
$_['text_update_text'] = 'Натисніть на кнопку.<br>ви оновили модуль';
$_['text_module'] = 'Модулі';
$_['text_add'] = 'Додати';
$_['text_action'] = 'Дія:';
$_['text_success'] = ' Модуль успішно оновлено!';
$_['text_content_top'] = 'Зміст шапки';
$_['text_content_bottom'] = 'Утримання підвалу';
$_['text_column_left'] = 'Ліва колонка';
$_['text_column_right'] = 'Права колонка';
$_['text_what_lastest'] = 'Останні записи';
$_['text_select_all'] = 'Виділити все';
$_['text_unselect_all'] = 'Зняти виділення';
$_['text_sort_order'] = 'Порядок';
$_['text_further'] = '...';
$_['text_error'] = 'Помилка';
$_['text_layout_all'] = 'Все';
$_['text_enabled'] = 'Увімкнуто';
$_['text_disabled'] = 'Вимкнуто';
$_['text_multi_empty'] = 'Зайдіть в таб "Установка і оновлення" і натисніть кнопку "Створення та оновлення даних для модуля (при установці і оновленні модуля)"';

$_['entry_lang_default'] = 'Мова за замовчуванням';

$_['entry_name'] = 'Ім’я';
$_['entry_prefix'] = 'Префікс';
$_['entry_prefix_main'] = 'Головна мова';

$_['entry_seomore_code'] = 'Код JS';

$_['entry_hreflang'] = 'Мета тег hreflang';
$_['entry_hreflang_status'] = 'Статус мета тег hreflang ';
$_['entry_commonhome_status'] = '<span class="vhelp">Прибрати з URL головної</span> <br>index.php?route=common/home ';
$_['entry_languages'] = 'Пов’язана мова';
$_['entry_access'] = 'Доступ';

$_['entry_remove_description_status'] = 'Прибрати опис на <br>додаткових сторінках<br>пагінації';
$_['entry_add_rule'] = 'Додати правило';
$_['entry_title_template'] = 'Ім’я файлу шаблону';
$_['entry_desc_types'] = 'Правила, згідно з якими <br>в шаблонах (ім’я файлу)<br>буде прибрано опис <br>На додаткових <br>сторінках пагінації';

$_['entry_pagination'] = 'Пагінація';
$_['entry_jazz'] = 'Підтримка ЧПУ формувача Jazz';
$_['entry_pagination_prefix'] = 'Назва змінної пагінації';
$_['entry_title_pagination'] = 'Заголовок пагінації';
$_['entry_currencies'] = 'Пов’зана валюта';

$_['entry_title_list_latest'] = 'Заголовок';
$_['entry_editor'] = 'графічний редактор';
$_['entry_switch'] = 'Включити модуль';
$_['entry_title_prefix'] = 'Мовний префікс<span class= "help" > поставте Мовний префікс,<br>наприклад для англійської мови <b>en </ b> <br> (url матиме вигляд: http://site.com/en ) <br>якщо ви хочете щоб url з префіксом <br>закінчувався слешем<br>(приклад: http://site.com/en/),<br>то поставте префікс <b>en<ins style="color:green; text-decoration: none;">/</ins></b><br>або залиште поле <B>порожнім</b><br>якщо у вас ця мова стоїть <b>за замовчуванням</b></span>';
$_['entry_about'] = 'Про модуль';
$_['entry_category_status'] = 'Показувати категорію';
$_['entry_cache_widgets'] = 'Повне кешування віджетів<br / > <span class="help" >при повному кешуванні віджетів <br>швидкість обробки і виведення швидше в 2-5 разів <br>в залежності від кількості віджетів <br>використовуваних на сторінці</span>';
$_['entry_reserved'] = 'Зарезервовано';
$_['entry_service'] = 'Сервіс';
$_['entry_langfile'] = ' Мовний користувацький файл<br> <span class="help" >формат: <B>папка/файл</B> без розширення</span>';
$_['entry_widget_cached'] = 'Кешувати віджет<br><span class="help">має більший пріоритет, ніж повне кешування <br>всіх віджетів в загальних налаштуваннях, <br>іноді кешувати віджет не треба, <br>якщо у вас в шаблонах є логіка додавання <br>JS скриптів і CSS стилів в документ</span>';

$_['entry_anchor'] = '<b>Прив’язка</b><br><span class="help" style="line-height: 13px;">прив’язка до блоку через jquery<br>приклад для default теми opencart:<br>$(\'<b>#language</b>\').html (langmarkdata);<br>де langmarkdata (змінна javascript)<br>результат виконання html блоку</span>';


$_['entry_layout'] = 'Схеми:';
$_['entry_html'] = <<<EOF
<b>HTML, PHP, JS код</b><br><span class="help" style="line-height: 13px;">Розуміє виконання PHP коду<br>
Змінні:<br>
\$languages - масив, що має структуру:<br>
 [код мови] => Array<br>
        (<br>
&nbsp; &nbsp; [language_id] => id мови<br>
&nbsp; &nbsp; [name] => ім’я мови<br>
&nbsp; &nbsp; [code] => код мови<br>
&nbsp; &nbsp; [locale] = > locale мови<br>
&nbsp; &nbsp; [image] => Зображення мови<br>
&nbsp; &nbsp; [directory] => папка<br>
&nbsp; &nbsp; [filename] => ім’я мовного файлу<br>
&nbsp; &nbsp; [sort_order] => порядок<br>
&nbsp; &nbsp; [status] => статус<br>
&nbsp; &nbsp; [url] => url поточної сторінки для мови<br>
        )<br>
<br>
\$text_language-заголовок<br>
для мовного блоку
<br>
<br>
\$language_code-поточний код мови
<br>
\$language_prefix - поточний префікс мови
</span>
EOF;

$_['entry_position'] = 'Розташування:';
$_['entry_status'] = ' Статус:';
$_['entry_sort_order'] = 'Порядок:';

$_['entry_template'] = ' <b>Шаблон </b>';
$_['entry_what'] = 'what';
$_['entry_install_update'] = 'Встановлення та оновлення';


$_['tab_general'] = 'Схеми';
$_['tab_list'] = 'Віджети';
$_['tab_options'] = 'Налаштування';
$_['tab_pagination'] = 'Пагінація';

$_['button_add_list'] = 'Додати віджет';
$_['button_update'] = 'Змінити';
$_['button_clone_widget'] = 'Клонувати віджет';
$_['button_continue'] = "Далі";

$_['error_delete_old_settings'] = '<div style="color: red; text-align: left; text-decoration: none;">поки не можна видаляти Налаштування старих версій<br><ins style="text-align: left; text-decoration: none; font-size: 13px; color: red;">(перезбережіть "Налаштування", "схеми" і "віджети", <br>тільки після цього натисніть цю кнопку)</ins></div>';
$_['error_permission'] = 'У вас немає прав для зміни модуля!';
$_['error_addfields_name'] = 'Невірне ім’я додаткового поля';

$_['access_777'] = 'Не встановлено права на файл <br>встановіть права 777 на цей файл вручну.';
$_['text_install_ok'] = 'Дані успішно оновлені';
$_['text_install_already'] = 'Дані присутні';
$_['hook_not_delete'] = 'Цю схему не можна видаляти, вона потрібна для сервісних функцій модуля (seo)<br>у випадку, якщо ви випадково видалили, додайте таку ж схему з такими ж параметрами<br>';
$_['type_list'] = 'Віджет:';
$_['text_about'] = <<<EOF

EOF;

$_['tab_other'] = 'Інші';
$_['entry_two_status'] = 'Виправлення "повторюваних слешів //" в URL<br><span class="lm-help">Працює тільки з SeoPro</span>';

$_['entry_prefix_switcher'] = 'Вивід у перемикачі мов';
$_['entry_prefix_switcher_stores'] = 'Вивід у перемикачі мов усіх магазинів';
$_['entry_hreflang_switcher'] = 'Вивід у мета тезі hreflang';
$_['entry_hreflang_switcher_stores'] = 'Вивід у мета тезі hreflang усіх магазинів';


$_['entry_shortcodes'] = 'Шорткоди';

$_['entry_currency_switch'] = 'При зміні мови примусово <br>перемикати валюту згідно налаштувань, <br > В не залежності від користувацьких <br>перемикань валюти на інших мовах <br>домену або піддомену';

$_['entry_use_link_status'] = 'Використовувати штатний <br>алгоритм формування ЧПУ';

$_['text_shortcodes_in'] = 'Шорткод для заміни';
$_['text_shortcodes_out'] = 'Заміна';
$_['text_shortcodes_action'] = 'Дія';
$_['url_create_text'] = '<div style="text-align: center; text-decoration: none;">Створення та оновлення<br>даних для модуля<br><ins style="text-align: center; text-decoration: none; font-size: 13px;">(при встановленні та оновленні модуля)</ins></div>';
$_['url_delete_text'] = '<div style="text-align: center; text-decoration: none;">Видалення всіх<br>налаштувань модуля<br><ins style= " text-align: center; text-decoration: none; font-size: 13px;">(всі налаштування будуть видалені)</ins > </div>';


$_['entry_copy_rules'] = 'Скопіювати правила';

$_['entry_store_id_related'] = 'Пов’язаний магазин';

$_['url_store_id_repated_text'] = '<div style="text-align: center; text-decoration: none;">Прив’язати всі категорії, Товари,<br>виробників, інформаційні сторінки (статті)<br><ins style="text-align: center; text-decoration: none; font-size: 13px;">до цього магазину</ins></div>';

$_['entry_title_values'] = 'Змінні';
$_['entry_cache_diff'] = ' Роздільний кеш <br>(для регіонів поза мультимагазинів, <br>на одному магазині, з однаковими товарами)';

$_['entry_langswitch_replace'] = 'Замінювати стандартний <br>перемикач';
$_['entry_lm_image_status'] = 'Статус зображення мови';
$_['entry_cookie'] = 'Одноразова реакція <br>на cookie';
$_['entry_cookie_set'] = 'Встановити cookie <br>після вибору';
$_['entry_lm_text_close'] = 'Текст кнопки "Закрити"';

$_['entry_lm_redirect'] = 'Перенаправлення на головну мову <br>при закритті popup вікна';
$_['entry_lm_autoredirect'] = 'Перенаправлення на головну мову<br> <b>автоматичне </b>';


$_['entry_widget_status'] = 'Статус';
$_['entry_store'] = 'Магазин';
$_['entry_id'] = 'ID';

$_['entry_xdefault_status'] = 'Статус x-default';
$_['entry_seo_pagination'] = 'SEO URL строки пагінації<br><span class="helpshow">на кшталт .../page-2</span><br><span class="helpshow">(Не рекомендується)</span>';

$_['entry_multi_sort'] = 'Порядок сортування';

$_['entry_redirect_new'] = 'Редірект (старі URL без префіксу) <br>на нові URL з префіксом мови<br><span class="lm-help">Вмикати <b>тільки у випадку</b> <br>якщо ваші старі URL вже <br>проіндексовані пошуковими системами. <br>На новому магазині - не вмикати</span>';
$_['entry_redirect_code'] = 'Код редіректу <br><span class="lm-help">301 (постійний) або <br>302 (тимчасовий)</span>';

$_['entry_ex_url_amp'] = 'Заміна в URL амперсанда <br>& на &amp;amp;';

$_['entry_main_prefix_url'] = 'Зміна префіксу головної сторінки<br><span class="vhelp">Працює коли увімкнуто "Прибрати мовний префікс головної сторінки"<br></span>';
