<?php
include('version.php');

$_['text_seolang_title'] = "SEO мультимова";
$_['url_text_seolang_module_text'] = $_['text_seolang_title'];
$_['seolang_model_code'] = "seolang";
$_['order_seolang'] = '0';

$_["ico_seolang"] = '<i class="uflag-ukflag"></i>'; 


$_['ocmod_seolang_author'] = 'support.opencartadmin.com ';
$_['ocmod_seolang_link'] = 'https://support.opencartadmin.com';

$_['seolang_model_settings'] = $_['heading_title'] = $_['seolang_model'] . ' ' . $_['seolang_version'];
$_['heading_title'] = '<span style="color: #5c8284; font-size: 15px; font-weight: 400;"><a href="' . $_['ocmod_seolang_link'] . '" style="color: #5c8284;" target="_blank" data-toggle="tooltip" title="" data-original-title="' . $_['ocmod_seolang_author'] . '">'  . $_['ico_seolang'] . '</a>  ' . $_['heading_title'] . '</span>';

$_['widget_seolang_version'] = $_['seolang_version'];

$_['heading_title_seolang'] = $_['seolang_model'];
$_['heading_dev'] = 'Розробник <a href="' . $_['ocmod_seolang_link'] . '" target="_blank">' . $_['ocmod_seolang_author'] . '</a><br>&copy; 2011-' . date('Y') . ' Всі права захищені';


$_['error_text_seolang_permission'] = 'У Вас немає прав для зміни модуля!';
$_['error_text_seolang_modify'] = 'У вас немає прав на зміни модуля!';

$_['url_text_seolang_opencartadmin'] = $_['ocmod_seolang_link'];
$_['url_text_seolang_create_text'] = '<div style="text-align: center; text-decoration: none;">Створення та оновлення<br>даних для модуля<br><ins style="text-align: center; text-decoration: none; font-size: 13px;">(при установці і оновлення модуля)</ins></div>';
$_['url_text_seolang_delete_text'] = '<div style="text-align: center; text-decoration: none;">Видалення всіх<br>налаштувань модуля<br><ins style="text-align: center; text-decoration: none; font-size: 13px;">(всі налаштування будуть видалені)</ins></div>';
$_['url_text_seolang_delete_sure_text'] = '<div style="text-align: center; text-decoration: none;">Ви впевнені<br>що хочете видалити усі налаштування?<br><ins style="text-align: center; text-decoration: none; font-size: 13px;">(всі налаштування будуть видалені)</ins></div>';
$_['url_text_seolang_create_text'] = '<div style="text-align: center; text-decoration: none;">Установка та оновлення<br>модифікаторів, даних модуля<br>(виконується при установці або оновлення модуля)</div>';

$_['url_text_seolang_ocmodrefresh'] = 'Оновити';
$_['url_text_seolang_cacheremove'] = 'Видалити кеш';

$_['ocmod_seolang_name'] = $_['seolang_model'];

$_['ocmod_seolang_name_15'] = $_['seolang_model'].' 15';
$_['ocmod_seolang_menu_name'] = $_['seolang_model'] . " Меню";
$_['ocmod_seolang_menu_mod'] = $_['seolang_model_code'] . '_menu';
$_['ocmod_seolang_menu_html'] = $_['ocmod_seolang_menu_name'] . ' модифікатор успішно встановлено';

$_['ocmod_seolang_mod'] = $_['seolang_model_code'];
$_['ocmod_seolang_mod_15'] = $_['seolang_model_code'].'_15';
$_['ocmod_seolang_html'] = $_['ocmod_seolang_name'].' модифікатор успішно встановлено';
$_['ocmod_seolang_name'] = $_['seolang_model'];
$_['ocmod_seolang_version'] = $_['seolang_version'] ;
$_['ocmod_seolang_text_on'] = '<span style="color:green">ввімкнений</span>';
$_['ocmod_seolang_text_off'] = '<span style="color:red">вимкнено</span>';

$_['tab_text_seolang_options'] = 'Налаштування';
$_['tab_text_seolang_position'] = 'Макети та позиції';
$_['tab_text_seolang_doc'] = 'Документація';
$_['tab_text_seolang_menu'] = 'Меню';
$_['tab_text_seolang_main'] = 'Віджети';
$_['tab_text_seolang_service'] = 'Сервіс';
$_['tab_text_seolang_access'] = 'Доступи';

$_['entry_seolang_incont'] = 'Висновок через контролер<br><span class="vhelp">Не включати якщо кнопка "Показати ще"<br>виводиться і коректно працює</span>';
$_['entry_seolang_id'] = "ID";
$_['entry_seolang_copy'] = 'Копіювати';
$_['entry_seolang_install_update'] = 'Установка / Оновлення';
$_['entry_seolang_position'] = 'Позиція';
$_['entry_seolang_copy_rules'] = 'Скопіювати правила';
$_['entry_seolang_title_values'] = 'Змінні';
$_['entry_seolang_add_rule'] = 'Додати';
$_['entry_seolang_widget_status'] = "Статус";
$_['entry_seolang_seolang_ocmodrefresh'] = 'Оновити <br><span class="sc-color-clearcache">модифікатори</span>';
$_['entry_seolang_seolang_cacheremove'] = 'Видалити кеш <br><span class="sc-color-clearcache">файлів</span>';
$_['entry_seolang_store'] = 'Магазини:';
$_['entry_seolang_seolang_menu_status'] = 'Статус <i class="fa fa-dot-circle-o"></i> ПЕРЕГЛЯД SEO в меню';
$_['entry_seolang_seolang_menu_order'] = 'Порядок пункту <i class="fa fa-dot-circle-o"></i> ПЕРЕГЛЯД SEO в меню, після "номери"<br>пункту в меню <br>номер:';
$_['entry_seolang_seolang_widget_status'] = 'Статус модуля';
$_['entry_seolang_seolang_widget_install_success'] = 'Таблиці віджету ' . $_['seolang_model'] . ' успішно встановлена<br>';
$_['entry_seolang_seolang_widget_install'] = 'Підключення віджету ' . $_['seolang_model'] . ' - успішно<br>';
$_['entry_seolang_seolang_widget_types'] = 'Видаляються елементи <br>з шаблону';
$_['entry_seolang_number'] = 'Номер';
$_['entry_seolang_add_seolang_widget_type'] = 'Додати елемент';
$_['entry_seolang_html'] = 'HTML';
$_['entry_seolang_add'] = 'Додати';
$_['entry_seolang_lang_default'] = 'Мова за промовчанням';
$_['entry_seolang_name'] = 'Ім`я';
$_['entry_seolang_access'] = 'Доступ';
$_['entry_seolang_add_rule']  = 'Додати правило';
$_['entry_seolang_title_template']    = 'Ім`я файлу шаблону';
$_['entry_seolang_editor'] = 'Графічний редактор';
$_['entry_seolang_switch'] = 'Включити модуль';
$_['entry_seolang_about'] = 'Про модулі';
$_['entry_seolang_category_status'] = 'Показувати категорію';
$_['entry_seolang_reserved'] = 'Зарезервовано';
$_['entry_seolang_service'] = 'Сервіс';
$_['entry_seolang_layout'] = 'Макети:';
$_['entry_seolang_position'] = 'Позиція';
$_['entry_seolang_status'] = 'Статус:';
$_['entry_seolang_sort_order'] = 'Порядок:';
$_['entry_seolang_template'] = 'Шаблон';
$_['entry_seolang_install_update'] = 'Установка та оновлення';
$_['entry_seolang_show'] = 'Показати';
$_['entry_seolang_positions'] = 'Позиції';
$_['entry_seolang_hide'] = 'Приховати';
$_['entry_seolang_uri'] = "URI";
$_['entry_seolang_add_position_type'] = 'Додати, не стандартну,<br> наявну в opencart, <br>налаштовувану позицію';
$_['entry_seolang_layouts'] = 'Макети';
$_['entry_seolang_menu_status'] = 'Меню статус';
$_['entry_seolang_menu_order'] = 'Порядок в меню';
$_['entry_seolang_widgets_options'] = 'Глобальні налаштування віджетів';
$_['entry_seolang_customer_groups'] = 'Групи покупців';
$_['entry_seolang_complete_status'] = 'Статус того хто купив товар:<br /><span class="vhelp">Статус замовлення, при якому покупець <br>отримує статус що купив "цей" товар</span>';
$_['entry_seolang_complete'] = 'Статус того хто купив товар';
$_['entry_seolang_complete_choice'] = 'Оберіть статуси замовлення для того хто купив товар';
$_['entry_seolang_position_types']    = 'Позиції / Користувальницькі позиції';
$_['entry_seolang_position_controller']   = 'Контролер обробки';
$_['entry_seolang_position_name'] = 'Ім`я змінної виведення';
$_['entry_seolang_sort'] = 'Порядок';
$_['entry_seolang_show_pro_settings'] = 'Показати PRO налаштування';
$_['entry_seolang_hide_pro_settings'] = 'Приховати PRO налаштування';

$_['text_seolang_uri_template'] = 'За "слова" в URI';
$_['text_seolang_uri'] = 'URI (URL без протоколу і домену)<br><span class="vhelp">Не заповнюйте якщо використовуєте макети</span>';
$_['text_seolang_error_name'] = 'Ім`я віджета містить неприпустимі символи<br><span class="vhelp">Допустимі символи: a-zA-Z0-9-_<br>не можна використовувати кирилицю і т. п.</span>';
$_['text_seolang_status'] = 'Статус';
$_['text_seolang_mod_add_seolang'] = $_['seolang_model'].' модифікатор встановлено<br>';
$_['text_seolang_seolang_success'] = 'Успішно';
$_['text_seolang_ocmodrefresh_successfully'] = '<span style="color:green">Модифікатори успішно оновлено</span>';
$_['text_seolang_ocmodrefresh_success'] = 'Модифікатори успішно оновлено';
$_['text_seolang_ocmodrefresh_error'] = '<span style="color:red">Помилка оновлення модифікаторів</span>';
$_['text_seolang_ocmodrefresh_fail'] = 'Не вдалося оновити';
$_['text_seolang_ocmod'] = 'модифікатор';
$_['text_seolang_cacheremove'] = 'Видалити кеш';
$_['text_seolang_cacheremove_success'] = 'Виконано успішно';
$_['text_seolang_cacheremove_fail'] = 'Не вдалося видалити';
$_['text_seolang_seolang_about'] = 'Про модулі';
$_['text_seolang_default_store'] = 'Основний магазин';
$_['text_seolang_loading_main'] = '<div style=&#92;\'color: #008000; &#92;\'>Завантажується...<i class=&#92;\'fa fa-refresh fa-spin&#92;\'></i></div>';
$_['text_seolang_loading_main_without'] = '<div style="color: #008000">Завантажується...<i class="fa fa-refresh fa-spin"></i></div>';
$_['text_seolang_faq'] = '';
$_['text_seolang_separator'] = ' > ';
$_['text_seolang_status_on'] = 'увімкнено';
$_['text_seolang_status_off'] = 'вимкнено';
$_['text_seolang_seolang_status_on'] = $_['text_seolang_title'] . ' <span style="margin-left: 6px; color: #eeffee;"> '.$_['text_seolang_status_on'] .'</span>';
$_['text_seolang_seolang_status_off'] = $_['text_seolang_title'] . ' <span style="margin-left: 6px; color: #fccccc;"> '.$_['text_seolang_status_off'] .'</span>';
$_['text_seolang_ocmod_refresh'] = 'Оновити&nbsp;модифікатори';
$_['text_seolang_close'] = 'Закрити';
$_['text_seolang_loading_small'] = '<div style=&#92;\'color: #008000; &#92;\'>Завантажується...<i class=&#92;\'fa fa-refresh fa-spin&#92;\'></i></div>';
$_['text_seolang_loading'] = '<div>Завантажується...<i class="fa fa-refresh fa-spin"></i></div>';
$_['text_seolang_loading_seolang'] = '<div>Завантажується...<i class="fa fa-refresh fa-spin"></i></div>';
$_['text_seolang_update_text'] = 'Натисніть на кнопку.<br>Ви оновили або встановили модуль';
$_['text_seolang_module'] = 'Модулі';
$_['text_seolang_add'] = 'Додати';
$_['text_seolang_action'] = 'Дія:';
$_['text_seolang_success'] = 'Модуль успішно оновлено!';
$_['text_seolang_content_top'] = 'Зміст шапки';
$_['text_seolang_content_bottom'] = 'Зміст підвалу';
$_['text_seolang_column_left'] = 'Ліва колонка';
$_['text_seolang_column_right'] = 'Права колонка';
$_['text_seolang_what_lastest'] = 'Останні записи';
$_['text_seolang_select_all'] = 'Виділити всі';
$_['text_seolang_unselect_all'] = 'Зняти виділення';
$_['text_seolang_sort_order'] = 'Порядок';
$_['text_seolang_further'] = '...';
$_['text_seolang_error'] = 'Помилка';
$_['text_seolang_layout_all'] = 'Всі';
$_['text_seolang_enabled'] = 'Увімкнуто';
$_['text_seolang_disabled'] = 'Вимкнуто';
$_['text_seolang_multi_empty'] = 'Зайдіть в таб "Установка та оновлення" та натисніть кнопку "Створення та оновлення даних для модуля (при установці та оновлення модуля)"';
$_['text_seolang_install_ok'] = 'Дані успішно оновлено';
$_['text_seolang_install_already'] = 'Дані присутні';

$_['text_seolang_check_ver'] = 'Перевірити нову версію';
$_['text_seolang_error_server_connect'] = 'Помилка з`єднання з сервером';
$_['text_seolang_server_date_state'] = 'За станом на';
$_['text_seolang_current_version_text'] = '<div style="color: #306793;">Ваша поточна версія</div>';
$_['text_seolang_last_version_text'] = '<div style="color: #306793;">Остання версія</div>';
$_['text_seolang_update_yes'] = '<div style="color: red;">Рекомендується оновити модуль</div>';
$_['text_seolang_update_no'] = '<div style="color: green;">Оновлення не потрібно, у вас остання версія модуля</div>';
$_['text_seolang_error_text_seolang_server_connect'] = 'Помилка з`єднання з сервером';
$_['text_seolang_update_version_begin'] = "<div style='background: #F7FFF2; width: auto; border: 1px solid #E2EDDC; padding: 10px;'>Остання доступна версія модуля: <span style='font-size: 21px;'>";
$_['text_seolang_update_version_end'] = "</span></div>";
$_['text_seolang_new_version'] = "<div style='background: #FFCFCE; border: 2px solid red; padding: 10px;'>Встановлена версія модуля: <b><span style='color: red;'>" . $_['seolang_version'] . "</span></b><br>"."Остання версія модуля: <span style='color: green;'><b>";
$_['text_seolang_new_version_end'] = '</b></span><br>Рекомендується: <span style="color: green;"><b>оновіть модуль до останньої версії</b></span></div>';

$_['text_seolang_group_reg'] = 'Зареєстровані';
$_['text_seolang_group_order'] = 'Ті, що купили товар в магазині';
$_['text_seolang_group_order_this'] = 'Ті, що купили "цей" товар в магазині';
$_['text_seolang_group_all'] = 'Всі групи покупців';


$_['seolang_ocas'] = $_['ocmod_seolang_link'] . '/index.php?route=record/ver';

/* Add backup */
$_['entry_lm_backup'] = 'Налаштування <br><span style="color: green;">зберегти</span>';
$_['entry_lm_restore'] = 'Налаштування <br><span style="color: green;">відновити</span>';

$_['text_lm_url_backup'] = 'Зберегти';
$_['text_lm_url_restore'] = 'Відновити';

$_['text_lm_backup_success'] = '<span style="color: green;">Налаштування збережені</span>';
$_['text_lm_restore_success'] = '<span style="color: green">Налаштування відновені</span>';

$_['text_lm_backup_fail'] = 'Не вдалося зберегти налаштування';
$_['text_lm_restore_fail'] = 'Не вдалося відновити налаштування';

$_['text_lm_backup_access'] = '<span style="color: red;">У вас нема прав доступу</span>';
$_['text_lm_restore_access'] = '<span style="color: red;">У вас нема прав доступу</span>';

$_['text_lm_settings_no_format'] = '<span style="color: red;">Не вірний формат налаштувань</span>';
$_['text_lm_json_error'] = '<span style="color: red;">Помилка декодування JSON</span>';
$_['text_lm_error_filetype'] = '<span style="color: red;">Не вірний тип файлу</span>';
/* backup */

/* Menu */
$_['entry_seolang_seolang_options'] = 'Налаштування віджетів<br>' . $_['seolang_model_settings'];
$_['text_seolang_seolang_options'] = 'Налаштування';

$_['entry_seolang_langmark_options'] = 'Налаштування<br>' . $_['seolang_model_settings'];
$_['text_seolang_langmark_options'] = 'Налаштування';


$_['entry_seolang_seolang_adapter'] = 'Адаптер<br>перемикача мов';
$_['text_seolang_seolang_adapter'] = 'Адаптація';

$_['text_seolang_widgets'] = 'Віджети';


/* Menu */

/* Icons */
$_['ocmod_seolang_name_15'] = $_['seolang_model'].' 15';
$_['ocmod_seolang_icons_name'] = $_['seolang_model'] . " CSS";
$_['ocmod_seolang_icons_mod'] = $_['seolang_model_code'] . '_icons';
$_['ocmod_seolang_icons_html'] = $_['ocmod_seolang_icons_name'] . ' модифікатор успішно встановлено';
/* Icons */

$_['text_seolang_ocmod_none'] = $_['text_seolang_ocmod'] . ' не встановлено';



$_['text_seolang_device'] = 'Пристрої';
$_['text_seolang_device_all'] = 'Усі пристрої';
$_['text_seolang_device_comp'] = 'Комп&#39ютери';
$_['text_seolang_device_mob'] = 'Мобільні пристрої';
$_['text_seolang_device_smart'] = 'Смартфони';
$_['text_seolang_device_pad'] = 'Планшети';