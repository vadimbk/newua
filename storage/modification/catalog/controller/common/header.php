<?php
class ControllerCommonHeader extends Controller {
private $seoOptimizedPages = ["/dreli-shurupoverty","/tokovye-kleshchi","/analizatory-spektra","/betonosmesiteli/obem/140-l","/akkum-nozhovki","/ruchnoj-instrument","/otvertki","/kraskopulty-elektricheskie","/akkumuljatornyj-pylesos","/lobziki","/betonosmesiteli","/elektropily/tip/pila-po-metallu","/oscillografy","/specialnye","/akkumuljatory","/akkumuljatornyj-instrument","/instrumenty","/elektroinstrument","/multimetry-1/peaktech","/multymetry/peaktech","/nabory-instrumentov","/detektory","/tiski","/anemometry","/svarochnoe-oborudovanie","/kleevye-pistolety","/pirometry","/teplovizory","/gajkoverty","/izmeritelnye-pribory","/mashiny-shlifovalnye-polirovalnye","/steplery-mexanicheskie","/elektropily","/frezery","/obzhimnoy-instrument","/dozimetry","/otbojnye-molotki","/ampermetry","/generatory-signalov","/miksery-elektricheskie","/cable-tester","/perforatory","/dreli-perforatory","/steplery","/dlya-snyatiya-izolyacii","/pily","/shlifmashiny","/zarjadnye-ustrojstva","/pylesosy-stroitelnye","/megaommetry","/multymetry","/multimetry-1"];

private $seoTitleCities = [
    'Харькове',
    'Одессе',
    'Днепре',
    'Запорожье',
    'Львове',
    'Кривом Роге',
    'Николаеве',
    'Виннице',
    'Херсоне',
    'Чернигове',
    'Полтаве',
    'Черкассах',
    'Хмельницком',
    'Сумах',
    'Житомире',
    'Черновцах',
    'Ровно',
    'Каменском',
    'Кропивницком',
    'Ивано-Франковске',
    'Кременчуге',
    'Тернополе',
    'Луцке',
    'Ужгороде',
];

private $seoTitleCitiesUkr = [
    'Харькові',
    'Одесі',
    'Дніпрі',
    'Запоріжжі',
    'Львові',
    'Кривому Розі',
    'Миколаєві',
    'Винниці',
    'Херсоні',
    'Чернігові',
    'Полтаві',
    'Черкасах',
    'Хмельницькому',
    'Сумах',
    'Житомирі',
    'Чернівцях',
    'Рівно',
    'Каменському',
    'Кропивницькому',
    'Івано-Франківську',
    'Кременчузі',
    'Тернополі',
    'Луцьку',
    'Ужгороді',
];

private $seoDescriptionCities = [
    'Харьков',
    'Одесса',
    'Днепр',
    'Запорожье',
    'Львов',
    'Кривой Рог',
    'Николаев',
    'Винница',
    'Херсон',
    'Чернигов',
    'Полтава',
    'Черкассы',
    'Хмельницкий',
    'Сумы',
    'Житомир',
    'Черновцы',
    'Ровно',
    'Каменское',
    'Кропивницкий',
    'Ивано-Франковск',
    'Кременчуг',
    'Тернополь',
    'Луцк',
    'Ужгород',
];

private $seoDescriptionCitiesUkr = [
    'Харьків',
    'Одеса',
    'Дніпро',
    'Запоріжжя',
    'Львів',
    'Кривий Ріг',
    'Миколаїв',
    'Вінниця',
    'Херсон',
    'Чернігів',
    'Полтава',
    'Черкаси',
    'Хмельницький',
    'Суми',
    'Житомир',
    'Чернівці',
    'Рівно',
    'Каменське',
    'Кропивницький',
    'Івано-Франківськ',
    'Кременчуг',
    'Тернопіль',
    'Луцьк',
    'Ужгород',
];

private function seoMeta($whatReturn = 'title')
{
    $defaultTitle = $this->document->getTitle();
    $title = $defaultTitle;

    $defaultDescription = $this->document->getDescription();
    $description= $defaultDescription;

    /**
     * Рандомный 1 город для title
     */
    $cityTitle = '';
    if (!empty($this->seoTitleCities)) {
        $cityTitle .= $this->seoTitleCities[array_rand($this->seoTitleCities, 1)];
    }
	
	$cityTitleUkr = '';
    if (!empty($this->seoTitleCitiesUkr)) {
        $cityTitleUkr .= $this->seoTitleCitiesUkr[array_rand($this->seoTitleCitiesUkr, 1)];
    }


    /**
     * Рандомный список из 2 городов для title
     */
    $citiesTitle = '';
    if (!empty($this->seoTitleCities)) {
        foreach (array_rand($this->seoTitleCities, 2) as $keyCity) {
            $citiesTitle .= $this->seoTitleCities[$keyCity] . ', ';
        }
    }
    $citiesTitle = rtrim($citiesTitle, ', ');
	
	$citiesTitleUkr = '';
    if (!empty($this->seoTitleCitiesUkr)) {
        foreach (array_rand($this->seoTitleCitiesUkr, 2) as $keyCityUkr) {
            $citiesTitleUkr .= $this->seoTitleCitiesUkr[$keyCityUkr] . ', ';
        }
    }
    $citiesTitleUkr = rtrim($citiesTitleUkr, ', ');

    /**
     * Рандомный список городов из 2-3 для description
     */
    $citiesDescription = '';
    if (!empty($this->seoDescriptionCities)) {
        foreach (array_rand($this->seoDescriptionCities,rand(2,3)) as $keyCity) {
            $citiesDescription .= $this->seoDescriptionCities[$keyCity] . ', ';
        }
    }
    $citiesDescription = rtrim($citiesDescription, ', ');
	
	$citiesDescriptionUkr = '';
    if (!empty($this->seoDescriptionCitiesUkr)) {
        foreach (array_rand($this->seoDescriptionCitiesUkr,rand(2,3)) as $keyCityUkr) {
            $citiesDescriptionUkr .= $this->seoDescriptionCitiesUkr[$keyCityUkr] . ', ';
        }
    }
    $citiesDescriptionUkr = rtrim($citiesDescriptionUkr, ', ');

    $route = $this->request->get['route'];
    if (!empty($route)) {
        /**
         * Страница товара product/product
         */
        if (
            $route == 'product/product'
            and $route !== 'error/not_found'
        ) {
            if (!empty($this->request->get['product_id'])) {
                $this->load->model('catalog/product');

                $producInfo = $this->model_catalog_product->getProduct($this->request->get['product_id']);
                if (!empty($producInfo)) {
                    /**
                     * Получаем цену на товар
                     */
                    if ($this->customer->isLogged() or !$this->config->get('config_customer_price')) {
                        $producInfoPrice = (!empty($producInfo['special'])) ? $producInfo['special'] : $producInfo['price'];
                        $price = $this->currency->format(
                            $this->tax->calculate(
                                $producInfoPrice,
                                $producInfo['tax_class_id'],
                                $this->config->get('config_tax')
                            ),
                            $this->session->data['currency']
                        );
                    } else {
                        $price = false;
                    }

                    /**
                     * Формируем новый тайтл для карточки товара
                     */
                    if (!empty($producInfo['name'])) {
                        $title = trim($producInfo['name']);
                        $description = $title;
if ($this->config->get('config_language_id')==2){
                        if (
                            !$price
                            or $price !== '0.0 грн'
                        ) {
                            $title .= " купить по выгодной цене {$price}";
                            $description .= " по выгодной цене {$price}";
                        } else {
                            $title .= " купить электротехнику в Украине";
                            $description .= " купить электротехнику";
                        }

                        $title .= " - радиомагазин Radio-Shop | Код товара: {$producInfo['sku']}";

                        $description .= " ⭐ Быстрая доставка ✓ Обмен/Возврат ✓ Переходите!";
                        //$description .= "продажа в интернет магазине Radio-Shop | {$producInfo['sku']}";
}
if ($this->config->get('config_language_id')==3){
                        if (
                            !$price
                            or $price !== '0.0 грн'
                        ) {
                            $title .= " купити за вигідною ціною {$price}";
                            $description .= " за вигідною ціною {$price}";
                        } else {
                            $title .= " купити електротехніку в Україні";
                            $description .= " купити електротехніку";
                        }

                        $title .= " - радіомагазин Radio-Shop | Код товару: {$producInfo['sku']}";

                        $description .= " ⭐ Швидка доставка ✓ Обмін/Повернення ✓ Переходьте! ";
                        //$description .= "продаж в інтернет магазині Radio-Shop | {$producInfo['sku']}";
}
					}					
                }
            }
        }

        /**
         * Страница категории product/category
         * Страница категории Акции product/special - дальше
         */
        elseif (
            $route == 'product/category'
            //or $route == 'product/special'
            and $route !== 'error/not_found'
        ) {
            if (!empty($this->request->get['path'])) {
                $this->load->model('catalog/category');
                $explodedCategoriesId = explode('_', $this->request->get['path']);
                if (count($explodedCategoriesId) > 1) {
                    $categoryInfo = $this->model_catalog_category->getCategory(end($explodedCategoriesId));
                } else {
                    $categoryInfo = $this->model_catalog_category->getCategory($this->request->get['path']);
                }

                if (!empty($categoryInfo)) {
                    $h1 = trim($categoryInfo['name']);

                    /**
                     * 1.Формулы генерации H1 для категорий/подкатегорий + фильтр (Измерительные приборы)
                     */

                    /**
                     * для блока фильтров "Производитель", применяем и для всех подразделов
                     * [Название раздела] + (Значение фильтра)
                     *
                     * root cat
                     * Измерительные приборы
                     * id = 60
                     * slug izmeritelnye-pribory
                     */
                    if(
                        $this->getCategoryInRange('60', $this->request->get['path'])
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], 'm:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для блока фильтров "Тип", подраздела "Весы"
                     * [Название раздела] + (Значение фильтра)
                     *
                     * root cat
                     * Весы
                     * id = 86
                     */
                    elseif(
                        $this->request->get['path'] == '86'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30145:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для остальных блоков фильтров во всех подразделах
                     * [Название раздела] + {Название блока фильтров}: (Значение фильтра)
                     *
                     * root cat
                     * id = 60
                     */
                    elseif(
                        $this->request->get['path'] == '88'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '10007:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для блока фильтров "Тип", подраздела "Аксессуары"
                     * (Значение фильтра)
                     *
                     * root cat
                     * Аксессуары
                     * id = 88
                     */
                    elseif(
                        $this->getCategoryInRange('60', $this->request->get['path'])
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(true);
                    }

                    /**
                     * 1.Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Измерительные приборы)
                     */

                    /**
                     * 2.Формулы генерации H1 для категорий/подкатегорий + фильтр (Аккумуляторный инструмент)
                     */

                    /**
                     * Добавляем слово "Аккумуляторные" перед Н1 страницы, применяем для подразделов
                     * "Перфораторы" 447,
                     * "Пилы" 448,
                     * "Шлифмашины" 451,
                     * "Лобзики" 449,
                     * "Ножовки" 450
                     * "Дрели (шуруповерты)" 446
                     * Аккумуляторные [H1 страницы]
                     *
                     * root cat
                     * Измерительные приборы
                     * id = 60
                     * slug izmeritelnye-pribory
                     */
                    elseif(
                        $this->request->get['path'] == '447'
                        or $this->request->get['path'] == '448'
                        or $this->request->get['path'] == '451'
                        or $this->request->get['path'] == '449'
                        or $this->request->get['path'] == '450'
                        or $this->request->get['path'] == '446'
                        and $this->request->get['path'] !== '445'
                    ) {
                        $h1 = 'Аккумуляторные ' . $h1 . ' ' . $this->getFiltersDataTitle(true);
                    }

                    /**
                     * 2.Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Аккумуляторный инструмент)
                     */

                    /**
                     * 3.Формулы генерации H1 для категорий/подкатегорий + фильтр (Автоинструмент)
                     */
                    /**
                     * Для подраздела "Гидравлика", блока фильтров "Виды оборудования"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 245
                     */
                    elseif(
                        $this->request->get['path'] == '245'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30261:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Рукоятки, воротки, переходники, карданы", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 61_241_246
                     */
                    elseif(
                        $this->request->get['path'] == '61_241_246'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30266:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Головки торцевые", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 251
                     */
                    elseif(
                        $this->request->get['path'] == '251'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30259:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Инструмент для смазки", блока фильтров "Оборудование для смазки"
                     * (Значение фильтра) для смазки
                     *
                     * root cat
                     * id = 252
                     */
                    elseif(
                        $this->request->get['path'] == '252'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30263:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false) . ' для смазки';
                    }
                    /**
                     * Для подраздела "Съемники, обжимки", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 253
                     */
                    elseif(
                        $this->request->get['path'] == '253'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30264:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 3.Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Автоинструмент)
                     */

                    /**
                     * 4.Формулы генерации H1 для категорий/подкатегорий + фильтр (Электроинструмент)
                     */

                    /**
                     * Для подраздела "Дрели, перфораторы", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 263
                     */
                    elseif(
                        $this->request->get['path'] == '263'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30260:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Шуруповерты, отвертки", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 264
                     */
                    elseif(
                        $this->request->get['path'] == '264'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30233:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Электрорубанки, электролобзики", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 266
                     */
                    elseif(
                        $this->request->get['path'] == '266'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30265:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Электропилы", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 267
                     */
                    elseif(
                        $this->request->get['path'] == '267'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30236:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Пылесосы строительные", блоков фильтров "Объем" и "Мощность"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 273
                     */
                    elseif(
                        $this->request->get['path'] == '273'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30242:') !== false
                        or strpos($this->request->get['filter_ocfilter'], '30286:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Степлеры", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 275
                     */
                    elseif(
                        $this->request->get['path'] == '275'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30246:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 4.Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Электроинструмент)
                     */

                    /**
                     * 5. Формулы генерации H1 для категорий/подкатегорий + фильтр (Расходные материалы для инстурмента)
                     */
                    /**
                     * Для подраздела "Биты и наборы бит", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 61_262_518
                     */
                    elseif(
                        $this->request->get['path'] == '61_262_518'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30280:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Биты и наборы бит", блока фильтров "Тип биты" и "Длина"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 61_262_518
                     */
                    elseif(
                        $this->request->get['path'] == '61_262_518'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30281:') !== false
                        or strpos($this->request->get['filter_ocfilter'], '30282:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 5. Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Расходные материалы для инстурмента)
                     */

                    /**
                     * 6. Формулы генерации H1 для категорий/подкатегорий + фильтр (Ручной инструмент)
                     */

                    /**
                     * Для подраздела "Ножи", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 459
                     */
                    elseif(
                        $this->request->get['path'] == '459'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30258:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Инструменты для снятия изоляции", блока фильтров "Тип инструмента"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 89
                     */
                    elseif(
                        $this->request->get['path'] == '89'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30250:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Обжимка (обжимной инструмент)", блока фильтров "Тип обжимаемого наконечника"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 77
                     */
                    elseif(
                        $this->request->get['path'] == '77'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30249:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для подраздела "Степлеры механические", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 360
                     */
                    elseif(
                        $this->request->get['path'] == '360'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30231:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Плоскогубцы и длинногубцы", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 90
                     */
                    elseif(
                        $this->request->get['path'] == '90'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30251:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Отвертки", блока фильтров "Тип отверток и отверточных насадок"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 93
                     */
                    elseif(
                        $this->request->get['path'] == '93'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30255:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для подраздела "Тиски", блока фильтров "Тип"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 94
                     */
                    elseif(
                        $this->request->get['path'] == '94'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30256:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для блока фильтров "Тип" подраздела "Клеевые пистолеты",
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 95
                     */
                    elseif(
                        $this->request->get['path'] == '95'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30257:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 6. Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Ручной инструмент)
                     */

                    /**
                     * 7. Формулы генерации H1 для категорий/подкатегорий + фильтр (Паяльное оборудование)
                     */

                    /**
                     * Для подраздела "Паяльные станции", блока фильтров "ТМаксимальная мощность паяльника"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 100
                     */
                    elseif(
                        $this->request->get['path'] == '100'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30136:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "Паяльники", блока фильтров "Мощность"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 101
                     */
                    elseif(
                        $this->request->get['path'] == '101'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30156:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для подраздела "Аксессуары", добавляем к названию раздела "для паяльного оборудования"
                     * [H1 страницы] для паяльного оборудования
                     *
                     * root cat
                     * id = 103
                     */
                    elseif(
                        $this->request->get['path'] == '103'
                    ) {
                        $h1 .= ' для паяльного оборудования ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 7. Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Паяльное оборудование)
                     */

                    /**
                     * 8. Формулы генерации H1 для категорий/подкатегорий + фильтр (Светодиоды и LED-продукция)
                     */

                    /**
                     * Для подраздела "LED светильники", блока фильтров "Тип"
                     * (Значение фильтра)
                     *
                     * root cat
                     * id = 474
                     */
                    elseif(
                        $this->request->get['path'] == '474'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30271:') !== false
                    ) {
                        $h1 = $this->getFiltersDataTitle(false);
                    }
                    /**
                     * Для подраздела "LED светильники", блока фильтров "Мощность", "Напряжение питания"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 474
                     */
                    elseif(
                        $this->request->get['path'] == '474'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30272:') !== false
                        or strpos($this->request->get['filter_ocfilter'], '30276:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для подраздела "Прожекторы", блока фильтров "Световой поток", "Мощность"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 115
                     */
                    elseif(
                        $this->request->get['path'] == '115'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30277:') !== false
                        or strpos($this->request->get['filter_ocfilter'], '30278:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * Для подраздела "Промышленные блоки питания", блока фильтров "Сила тока", "Напряжение", "Мощность"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 128
                     */
                    elseif(
                        $this->request->get['path'] == '128'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30290:') !== false
                        or strpos($this->request->get['filter_ocfilter'], '30288:') !== false
                        or strpos($this->request->get['filter_ocfilter'], '30287:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 8. Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Светодиоды и LED-продукция)
                     */

                    /**
                     * 9. Формулы генерации H1 для категорий/подкатегорий + фильтр (Разъемы)
                     */

                    /**
                     * Для подраздела "Клеммы", блока фильтров "Размер клеммы"
                     * [Название подраздела] + (Значение фильтра)
                     *
                     * root cat
                     * id = 146
                     */
                    elseif(
                        $this->request->get['path'] == '146'
                        and !empty($this->request->get['filter_ocfilter'])
                        and strpos($this->request->get['filter_ocfilter'], '30229:') !== false
                    ) {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }
                    /**
                     * 9. Конец Формулы генерации H1 для категорий/подкатегорий + фильтр (Разъемы)
                     */

                    /**
                     * 10. Формулы генерации H1 для категорий/подкатегорий + фильтры
                     */
                    /**
                     * Учитывать формулы генерации H1, составленные выше
                     *
                     * настроенные закрытые сочетания фильтров в noindex, nofollow
                     * после 1-2-ух зажатых фильтров оставляем прежними
                     *
                     * Н1 страницы с 1 зажатым фильтром + значение второго фильтра с условием если оно есть
                     */
                    else {
                        $h1 .= ' ' . $this->getFiltersDataTitle(false);
                    }

                    /**
                     * 10. Конец Формулы генерации H1 для категорий/подкатегорий + фильтры
                     */

                    /**
                     * Title для страницы пагинации листинга товаров
                     */
                    if (!empty($this->request->get['page']) and $this->config->get('config_language_id')==2) {
                        $h1 = $h1 . " - страница №{$this->request->get['page']}";
                        $title = $h1 . " - купить электротехнику в Киеве, {$cityTitle} и по Украине, цена в интернет магазине Radio-Shop";
                        $description = null;
                    }
					if (!empty($this->request->get['page']) and $this->config->get('config_language_id')==3) {
					    $h1 = $h1 . " - сторінка №{$this->request->get['page']}";                        
						$title = $h1 . " - купити електротехніку в Києві, {$cityTitleUkr} і по Україні, ціна в інтернет магазині Radio-Shop";
                        $description = null;
					}
                    $h1 = trim($h1);
						
					/**
                     * Формулы генерации Title для категорий (с фильтрами включительно)
                     *
                     * Учитываем формулы генерации H1
                     * (не формировать для страниц пагинации и 404)
                     * Добавляем 2 случайных города из выделенного списка для Title
                     * В качестве категорий подразумеваются следующие страницы https://prnt.sc/u2qk5y
                     *
                     * для главных категорий parent_id = 0
                     *
                     * [H1 страницы] - купить в Киеве,
                     * [2 случайных города в родительном падеже] и по Украине,
                     * цена в интернет магазине радиоэлектроники Radio-Shop
                     */
                    if (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] == '0'
						and $this->config->get('config_language_id')==2
                    ) {
                        $title = $h1 . " - купить в Киеве, {$citiesTitle} и по Украине, цена в интернет магазине радиоэлектроники Radio-Shop";
                    }
					
					if (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] == '0'
						and $this->config->get('config_language_id')==3
                    ) {
                        $title = $h1 . " - купити в Києві, {$citiesTitleUkr} і по Україні, ціна в інтернет магазині радіоелектроніки Radio-Shop";
                    }

                    /**
                     * Формулы генерации Title для подкатегорий (с фильтрами включительно)
                     *
                     * Учитываем формулы генерации H1
                     * (не формировать для страниц пагинации и 404)
                     * Добавляем 2 случайных города из выделенного списка для Title
                     *  В качестве подкатегорий подразумеваются следующие страницы https://prnt.sc/u2qnxe
                     *
                     * для дочерних категорий parent_id !== 0
                     *
                     * [H1 страницы] - купить в Киеве,
                     * [2 случайных города в родительном падеже] и по Украине,
                     * выгодная цена на [название категории] (главной)
                     * в интернет магазине радиотоваров Radio-Shop
                     */
                    if (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] !== '0'
						and $this->config->get('config_language_id')==2
                    ) {
                        $rootCategory = $this->getRootCategoryName($categoryInfo['category_id']);
						$title = $h1 . " - купить в Киеве, {$citiesTitle} и по Украине, выгодная цена на {$rootCategory} в интернет магазине радиоэлектроники Radio-Shop";
                    }
					
					if (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] !== '0'
						and $this->config->get('config_language_id')==3
                    ) {
                        $rootCategory = $this->getRootCategoryName($categoryInfo['category_id']);
						$title = $h1 . " - купити в Києві, {$citiesTitleUkr} і по Україні, вигідна ціна на {$rootCategory} в інтернет магазині радіоелектроніки Radio-Shop";
                        
                    }

                    /**
                     * Формулы генерации Description для категорий/подкатегорий 
                     *
                     * Учитываем формулы генерации
                     * H1 (не формировать для страниц пагинации и 404)
                     * Добавляем 2-3 случайных города из выделенного списка для Description, 
                     * применяем для разделов "Измерительные приборы" и "Инструменты"
                     * Для страниц категорий значение (название категории) оставляем пустым
                     *
                     * [H1 страницы] по лучшей цене, 
                     * купить радиотовары в Киеве, 
                     * доставка в [2-3 случайных города] и вся Украина, 
                     * заказать (название категории) в интернет магазине Radio-Shop
                     */
                    if (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] !== '0'
						and $this->config->get('config_language_id')==2
                    ) {
                        $rootCategory = $this->getRootCategoryName($categoryInfo['category_id']);
                        $description = $h1 . " по лучшей цене, купить радиотовары в Киеве, доставка в {$citiesDescription} и вся Украина, заказать {$rootCategory} в интернет магазине Radio-Shop";
                    } elseif (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] == '0'
						and $this->config->get('config_language_id')==2
                    ) {
                        $description = $h1 . " по лучшей цене, купить радиотовары в Киеве, доставка в {$citiesDescription} и вся Украина, заказать в интернет магазине Radio-Shop";
                    }
					
					
					if (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] !== '0'
						and $this->config->get('config_language_id')==3
                    ) {
                        $rootCategory = $this->getRootCategoryName($categoryInfo['category_id']);
                        $description = $h1 . " за найкращою ціною, купити радіотовари в Києві, доставка в {$citiesDescriptionUkr} і вся Україна, замовити {$rootCategory} в інтернет магазині Radio-Shop";
                    } elseif (
                        empty($this->request->get['page'])
                        and $categoryInfo['parent_id'] == '0'
						and $this->config->get('config_language_id')==3
                    ) {
                        $description = $h1 . " за найкращою ціною, купити радіотовари в Києві, доставка в {$citiesDescriptionUkr} і вся Україна, замовити в інтернет магазині Radio-Shop";
                    }

                    /**
                     * Ищем оптимизированнные под сео страницы
                     * если находим, заменяем тайтл
                     */
                    $needleOptimizeSlug = '';
                    if (
                        !empty($this->request->get['_route_'])
                        and !empty($this->request->server['REQUEST_URI'])
                    ) {
                        $needleOptimizeSlug = str_replace('/' . $this->request->get['_route_'] . '/' ,'',$this->request->server['REQUEST_URI']);

                        $queryOcfilterPageOptimize = $this
                            ->db
                            ->query("
                          SELECT DISTINCT * FROM " . DB_PREFIX . "ocfilter_page 
                            WHERE category_id = '" . (int)$this->request->get['path'] . "'
                                AND params = '" . $needleOptimizeSlug . "'"
                            );

                        $result = $queryOcfilterPageOptimize->row;
                        if (!empty($result)) {
                            $queryOcfilterPageOptimizeData = $this
                                ->db
                                ->query("
                                    SELECT DISTINCT * FROM " . DB_PREFIX . "ocfilter_page_description 
                                        WHERE ocfilter_page_id = '" . (int)$result['ocfilter_page_id'] . "'"
                                );

                            if(!empty($queryOcfilterPageOptimizeData->row)) {
                                $optimizedTitle = $queryOcfilterPageOptimizeData->row['meta_title'];
                                $optimizedDescription = !empty($queryOcfilterPageOptimizeData->row['meta_description']) ? $queryOcfilterPageOptimizeData->row['meta_description'] : $queryOcfilterPageOptimizeData->row['description'];
                                $title = (empty($optimizedTitle)) ? $title : $optimizedTitle;
                                $description = (empty($optimizedDescription)) ? $description : $optimizedDescription;
                            }
                        }
                    }

                }
            }
        }

        /**
         * Страница поиска product/search
         */
        elseif (
            $route == 'product/search'
            and $route !== 'error/not_found'
        ) {
            $title = $defaultTitle;
        }

        /**
         * Служебные страницы
         * information/information
         * information/contact
         * information/company
         */
        elseif (
            $route == 'information/information'
            or $route == 'information/contact'
            or $route == 'information/company'
            and $route !== 'error/not_found'
        ) {
		
			if ($this->config->get('config_language_id')==2){
            $title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
            $description = null;
			}	
		
			if ($this->config->get('config_language_id')==3){
            $title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
            $description = null;
			}			
        }
		
		/* Title для страниц Блога и пагинации */
			elseif ($route == 'octemplates/blog/oct_bloglatest' and empty($this->request->get['page'])){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
				$description = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				$description = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				}
			}	
			
			elseif ($route == 'octemplates/blog/oct_bloglatest' and !empty($this->request->get['page'])){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - страница № ' . $this->request->get['page'] . ' - интернет магазин электротехнических товаров Radio-Shop';
				$description = $defaultTitle . ' - страница № ' . $this->request->get['page'] . ' - интернет магазин электротехнических товаров Radio-Shop';
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - сторінка № ' . $this->request->get['page'] . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				$description = $defaultTitle . ' - сторінка № ' . $this->request->get['page'] . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				}
			}	
											
		/* Title для страниц Блога и пагинации */
		
		
		/* Title для страниц Акции и пагинации */
			elseif ($route == 'product/special' and empty($this->request->get['page'])){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
				//$description = null;
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				//$description = null;
				}
			}	
			
			elseif ($route == 'product/special' and !empty($this->request->get['page'])){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - страница № ' . $this->request->get['page'] . ' - интернет магазин электротехнических товаров Radio-Shop';
				//$description = null;
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - сторінка № ' . $this->request->get['page'] . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				//$description = null;
				}
			}	
											
		/* Title для страниц Акции и пагинации */
		
		/* Title для страницы Оформления заказа */
			elseif ($route == 'checkout/simplecheckout'){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
				//$description = null;
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				//$description = null;
				}
			}	
											
		/* Title для страницы Оформления заказа */
		
		
		/* Title для страницы Карты сайта */
			elseif ($route == 'information/sitemap'){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
				//$description = null;
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				//$description = null;
				}
			}	
											
		/* Title для страницы Карты сайта */
		
		
				/* Title для страниц Производителей */
			elseif ($route == 'product/manufacturer/info'){
				if ($this->config->get('config_language_id')==2){
				$title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
				//$description = null;
				}
				if ($this->config->get('config_language_id')==3){
				$title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
				//$description = null;
				}
			}	
											
		/* Title для страниц Производителей */
		

        /**
         * Блог octemplates/blog/oct_blogcategory
         */
        elseif (
            $route == 'octemplates/blog/oct_blogcategory'
            and $route !== 'error/not_found'
        ) {
		
			if ($this->config->get('config_language_id')==2){		
            $title = $defaultTitle . ' - интернет магазин электротехнических товаров Radio-Shop';
            $description = $defaultDescription;
			}

			if ($this->config->get('config_language_id')==3){		
            $title = $defaultTitle . ' - інтернет магазин електротехнічних товарів Radio-Shop';
            $description = $defaultDescription;
			}			
        }

        /**
         * 404 error/not_found
         */
        elseif ($route == 'error/not_found') {
            $title = $defaultTitle . ' - интернет магазин электротехники Radio-Shop';
            $description = null;
        }
    }

    // Получаем значение нужно ли использовать мета теги из админки по умолчанию
    $is_default_meta = $this->getDefaultMetaTags();
    /**
     * Для страниц у которых уже есть оптимизация, не использовать формулы
     */
    if (in_array($_SERVER['REQUEST_URI'], $this->seoOptimizedPages) || $is_default_meta) {
        $title = $defaultTitle;
        $description = $defaultDescription;
    }
	
	/* Для страниц у которых уже есть оптимизация в админ-панели и пагинация, не использовать формулы, но добавляем страница № */
	if (in_array($_SERVER['REQUEST_URI'], $this->seoOptimizedPages) || $is_default_meta and !empty($this->request->get['page'])) {
		if ($this->config->get('config_language_id')==2){
			$title = $defaultTitle . ' - страница № ' . $this->request->get['page'];
			$description = $defaultDescription;
		}	
		if ($this->config->get('config_language_id')==3){
			$title = $defaultTitle . ' - сторінка № ' . $this->request->get['page'];
			$description = $defaultDescription;
		}			
    }
	/* Для страниц у которых уже есть оптимизация в админ-панели и пагинация, не использовать формулы, но добавляем страница № */

    if ($whatReturn == 'title') {
        return $title;
    } elseif ($whatReturn == 'description') {
        return $description;
    }
}

private function getRootCategoryName($categoryId)
{
    $this->load->model('catalog/category');
    $categoryInfo = $this->model_catalog_category->getCategory($categoryId);
    if($categoryInfo['parent_id'] == '0') {
        return $categoryInfo['name'];
    } else {
        $categoryInfo = $this->model_catalog_category->getCategory($categoryInfo['parent_id']);
        if($categoryInfo['parent_id'] == '0') {
            return $categoryInfo['name'];
        } else {
            $categoryInfo = $this->model_catalog_category->getCategory($categoryInfo['parent_id']);
            return $categoryInfo['name'];
        }
    }
}
/**
 * Возвращает Название атрибута и название опции атрибута, которые зажаты на данный момент
 * если передать $onlyNameOption = false, то вернет только название опции атрибута
 *
 * @param bool $onlyNameOption
 * @return string
 */
private function getFiltersDataTitle($onlyNameOption = true)
{
    $titleOption = '';
    if (!empty($this->request->get['filter_ocfilter'])) {
        $this->load->model('extension/module/ocfilter');

        $explodedFilters = explode(';', $this->request->get['filter_ocfilter']);

        if (!empty($this->ocfilter->getOCFilterOptions())) {
            $currentFiltersAvailable = $this->ocfilter->getOCFilterOptions();
            $mappingFilterName = $this->mapFilter($currentFiltersAvailable);
        }

        foreach ($explodedFilters as $filter) {
            $filterValues = explode(':', $filter);
            if ($onlyNameOption) {
                $titleOption .= $mappingFilterName[$filterValues[0]]['name'] . ': ';
            }
            $explodedManufacturer = explode(',', $filterValues[1]);
            foreach ($explodedManufacturer as $manufacturer) {
                $mappingFilterOptionName = $this->mapFilter($mappingFilterName[$filterValues[0]]['values']);
                $titleOption .= $mappingFilterOptionName[$manufacturer]['name'] . ', ';
            }
            $titleOption = trim($titleOption, ', ') . ' ';
        }

        $titleOption = trim(preg_replace('#\s\s#is', ' ', $titleOption));
    }

    return trim($titleOption);
}

/**
 * Является ли $currentCategoryId дочерней категорией или родительской
 * @param $needleRootCategory
 * @param $currentCategoryId
 */
private function getCategoryInRange($needleRootCategory, $currentCategoryId)
{
    $this->load->model('catalog/category');
    $categoryInfo = $this->model_catalog_category->getCategory($currentCategoryId);
    if(
        $currentCategoryId !== $needleRootCategory
        and $categoryInfo['parent_id'] !== '0'
    ) {
        if (
            $categoryInfo['parent_id'] !== '0'
            and $categoryInfo['parent_id'] !== $needleRootCategory
            and $categoryInfo['category_id'] !== $needleRootCategory
        ) {
            $this->getCategoryInRange($needleRootCategory, $categoryInfo['parent_id']);
        } else {
            /**
             * является дочерней у родителя ' . $needleRootCategory ;
             */
            return true;
        }

    }

    if ($currentCategoryId == $needleRootCategory) {
        /**
         * является родителем
         */
        return true;
    }

    return false;
}

/**
 * @param $array
 * @return array
 */
private function mapFilter($array)
{
    $newArray = [];

    foreach ($array as $option) {
        $newArray[reset($option)] = $option;
    }

    return $newArray;
}
private function getDefaultMetaTags() {
				    // определяем тип страницы (товар, категория, посадочная страница)
				    if(isset($this->request->get['route'])) {

				        $route = $this->request->get['route'];

				        if (!empty($route)) {
				            if ($route == 'product/product' && $route !== 'error/not_found') {
				                $query = $this->db->query("SELECT default_meta FROM ".DB_PREFIX."product_description WHERE product_id = '".(int)$this->request->get['product_id']."' AND language_id = '".$this->config->get('config_language_id')."'");
				            } elseif($route == 'product/category' && $route !== 'error/not_found') {

				                if($page_info = $this->ocfilter->getPageInfo()) {
				                    $query = $this->db->query("SELECT default_meta FROM ".DB_PREFIX."ocfilter_page_description WHERE ocfilter_page_id = '".(int)$page_info['ocfilter_page_id']."' AND language_id = '".$this->config->get('config_language_id')."'");
				                } else {

				                    if (isset($this->request->get['path'])) {                       

				                        $parts = explode('_', (string)$this->request->get['path']);

				                        $category_id = (int)array_pop($parts);

				                        $query = $this->db->query("SELECT default_meta FROM ".DB_PREFIX."category_description WHERE category_id = '".(int)$category_id."' AND language_id = '".$this->config->get('config_language_id')."'");
				                        
				                    } 
				                    
				                }
				                
				            }

				            return isset($query->row['default_meta']) ? $query->row['default_meta'] : 0;
				        }
				    }
				    
				}

			public function octBreadcrumbs($data) {
				$data['oct_ultrastore_data'] = $this->config->get('theme_oct_ultrastore_data');


				$data = array_merge($data, $this->load->controller('extension/module/fx/header', $data)); // Full IndeX
			
				return $this->load->view('octemplates/module/oct_breadcrumbs', $data);
			}
			

        // start: OCdevWizard In Stock Alert
        public function ocdw_in_stock_alert_js_create($data) {
          if ($data) {
            $models = ['tool/image'];

            foreach ($models as $model) {
              $this->load->model($model);
            }

            $script = (file_exists(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert/source.ocdw')) ? file_get_contents(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert/source.ocdw') : '';

            $find = [
              '{_name}',
              '{_code}',
              '{popup_background_type}',
              '{popup_close_on_content_click}',
              '{popup_close_on_bg_click}',
              '{popup_close_btn_inside}',
              '{popup_close_on_escape_key}',
              '{popup_align_top}',
              '{popup_animation_type}',
              '{loader_color}',
              '{style_background}',
              '{style_color}',
              '{background_opacity}',
              '{main_product_id_selector}',
              '{replace_button}',
              '{button_location}',
              '{button_class_global}',
              '{replace_button_product_page}',
              '{button_location_product_page}',
              '{button_class_product_page}',
              '{display_type}',
              '{sidebar_type}',
              '{icon}',
              '{add_function_selector}',
              '{add_id_selector}'
            ];

            $replace = [
              'in_stock_alert',
              'ocdw_in_stock_alert',
              $data['popup_background_type'],
              $data['popup_close_on_content_click'],
              $data['popup_close_on_bg_click'],
              $data['popup_close_btn_inside'],
              $data['popup_close_on_escape_key'],
              $data['popup_align_top'],
              $data['popup_animation_type'],
              $data['loader_color'],
              $data['style_background'],
              $data['style_color'],
              $data['background_opacity'],
              $data['main_product_id_selector'],
              $data['replace_button'],
              $data['button_location'],
              $data['button_class_global'],
              $data['replace_button_product_page'],
              $data['button_location_product_page'],
              $data['button_class_product_page'],
              $data['display_type'],
              $data['sidebar_type'],
              (($data['call_button_view_status'] == 2 && $data['call_button_icon']) ? $this->model_tool_image->resize($data['call_button_icon'],$data['call_button_icon_width'],$data['call_button_icon_height']) : ''),
              json_encode(explode(',',$data['add_function_selector'])),
              json_encode(explode(',',$data['add_id_selector']))
            ];

            $script = str_replace($find, $replace, $script);

            if ($data['minify_main_js']) {
              $script = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $script);
              if ($data['minify_main_js'] == 1) {
                $script = str_replace(array("\r\n", "\r"), "\n", $script);
              } else {
                $script = str_replace(array("\r\n", "\r"), "", $script);
              }
              $script = preg_replace('/[^\S\n]+/', ' ', $script);
              $script = str_replace(array(" \n", "\n "), "\n", $script);
              $script = preg_replace('/\n+/', "\n", $script);
              $script = str_replace(': ', ':', $script);
              $script = preg_replace(array('(( )+{)','({( )+)'), '{', $script);
              $script = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $script);
              $script = preg_replace(array('(;( )+)','(( )+;)'), ';', $script);
              $script = str_replace(array(' {',' }','{ ','; '),array('{','}','{',';'), $script);
            }

            if (is_dir(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert')) {
              if (!file_exists(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert/main.js')) {
                file_put_contents(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert/main.js', $script);
              }
            } else {
              mkdir(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert', 0755);
              file_put_contents(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert/main.js', $script);
            }
          }
        }
        // end: OCdevWizard In Stock Alert
      
	public function index() {

$this->document->addStyle('catalog/view/theme/default/stylesheet/promotion.css');
$this->document->addStyle('catalog/view/javascript/jquery/timeTo/timeTo.css');			
$this->document->addScript('catalog/view/javascript/jquery/timeTo/jquery.timeTo.js');
$this->language->load('extension/module/promotion_link');
$data['promotion_link'] = '<a href="' . $this->url->link('extension/module/promotion/category') . '">' . $this->language->get('text_promotion_link') . '</a
>';
                        
      

        // start: OCdevWizard In Stock Alert
        $this->load->model('extension/ocdevwizard/helper');

        $ocdw_in_stock_alert_form_data = $this->model_extension_ocdevwizard_helper->getSettingData('in_stock_alert_form_data', (int)$this->config->get('config_store_id'));

        if (isset($ocdw_in_stock_alert_form_data['activate']) && $ocdw_in_stock_alert_form_data['activate']) {
          $this->document->addStyle("catalog/view/theme/default/stylesheet/ocdevwizard/in_stock_alert/stylesheet.css");

          $this->load->model('extension/ocdevwizard/in_stock_alert');

          $language_id = $this->model_extension_ocdevwizard_in_stock_alert->getLanguageIdByCode($this->session->data['language']);

          if (isset($ocdw_in_stock_alert_form_data['direction_type'][$language_id]) && $ocdw_in_stock_alert_form_data['direction_type'][$language_id] == '2') {
            $this->document->addStyle("catalog/view/theme/default/stylesheet/ocdevwizard/in_stock_alert/stylesheet_rtl.css");
          }

          $this->ocdw_in_stock_alert_js_create($ocdw_in_stock_alert_form_data);

          if (file_exists(DIR_APPLICATION.'view/javascript/ocdevwizard/in_stock_alert/main.js')) {
            $this->document->addScript("catalog/view/javascript/ocdevwizard/in_stock_alert/main.js?v=".$ocdw_in_stock_alert_form_data['front_module_version']);
          }
        }
        // end: OCdevWizard In Stock Alert
      
		$data['ee_js_position'] = $this->config->get('module_ee_tracking_js_position');
		$data['ee_js_version'] = $this->config->get('module_ee_tracking_js_version');

			$data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');
			
			$data['oct_lang_id'] = (int)$this->config->get('config_language_id');


			if (isset($data['oct_ultrastore_data']['contact_open'][(int)$this->config->get('config_language_id')])){
				$oct_contact_opens = explode(PHP_EOL, $data['oct_ultrastore_data']['contact_open'][(int)$this->config->get('config_language_id')]);

				foreach ($oct_contact_opens as $oct_contact_open) {
					if (!empty($oct_contact_open)) {
						$data['oct_contact_opens'][] = $oct_contact_open;
					}
				}
			}

			$oct_contact_telephones = explode(PHP_EOL, $data['oct_ultrastore_data']['contact_telephone']);

			foreach ($oct_contact_telephones as $oct_contact_telephone) {
				if (!empty($oct_contact_telephone)) {
					$data['oct_contact_telephones'][] = $oct_contact_telephone;
				}
			}

			if (isset($oct_ultrastore_data['contact_map']) && !empty($oct_ultrastore_data['contact_map'])) {
				$data['contact_map'] = html_entity_decode($oct_ultrastore_data['contact_map'], ENT_QUOTES, 'UTF-8');
			}

			if ((isset($this->request->get['route']) && $this->request->get['route'] == 'common/home') || $this->request->server['REQUEST_URI'] == '/') {
				$data['oct_home'] = true;
			}

			$this->load->model('catalog/information');

			$data['mobile_informations'] = [];

			if (isset($oct_ultrastore_data['mobile_information_links']) && !empty($oct_ultrastore_data['mobile_information_links'])) {
				foreach ($oct_ultrastore_data['mobile_information_links'] as $information_id) {
					$information_info = $this->model_catalog_information->getInformation($information_id);

					if ($information_info) {
						$data['mobile_informations'][] = array(
							'title' => $information_info['title'],
							'href'  => $this->url->link('information/information', 'information_id=' . $information_id, true)
						);
					}
				}
			} else {
				foreach ($this->model_catalog_information->getInformations() as $result) {
					$data['mobile_informations'][] = array(
						'title' => $result['title'],
						'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
					);
				}
			}

			$data['header_informations'] = [];

			if (isset($oct_ultrastore_data['header_information_links']) && !empty($oct_ultrastore_data['header_information_links'])) {
				foreach ($oct_ultrastore_data['header_information_links'] as $information_id) {
					$information_info = $this->model_catalog_information->getInformation($information_id);

					if ($information_info) {
						$data['header_informations'][] = array(
							'title' => $information_info['title'],
							'href'  => $this->url->link('information/information', 'information_id=' . $information_id, true)
						);
					}
				}
			}
			

			$data['oct_popup_call_phone_status'] = $this->config->get('oct_popup_call_phone_status');
			
	// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {

            if (!$this->config->get('analytics_' . $analytic['code'] . '_position')) {
			
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));

            }
			
			}
		}
		
		$data['route'] = $this->request->get['route'];

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}


			if ($this->config->get('analytics_oct_analytics_google_status') && $this->config->get('analytics_oct_analytics_google_webmaster_code')) {
				$data['oct_analytics_google_webmaster_code'] = html_entity_decode($this->config->get('analytics_oct_analytics_google_webmaster_code'), ENT_QUOTES, 'UTF-8');
			}
	
			if ($this->config->get('analytics_oct_analytics_yandex_status') && $this->config->get('analytics_oct_analytics_yandex_webmaster_code')) {
				$data['oct_analytics_yandex_webmaster_code'] = html_entity_decode($this->config->get('analytics_oct_analytics_yandex_webmaster_code'), ENT_QUOTES, 'UTF-8');
			}
			
		$data['title'] = $this->seoMeta('title');

		// remarketing all in one 
		$this->load->model('tool/remarketing');
		if ($this->config->get('remarketing_status')) {
			$data['remarketing_head'] = $this->load->controller('common/remarketing/header');
		}
		if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot()) {
            $data['remarketing_body'] = $this->load->controller('common/remarketing/body');
			$data['ecommerce_currency'] = $this->config->get('remarketing_ecommerce_currency');	
			$data['ecommerce_ga4_identifier'] = $this->config->get('remarketing_ecommerce_ga4_identifier');
			$this->model_tool_remarketing->getCid();  
			$this->model_tool_remarketing->trackUtm();  
			$this->document->addScript('catalog/view/javascript/sp_remarketing.js');
		}
			

		$data['base'] = $server;
		$data['description'] = $this->seoMeta('description');
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		
			$this->load->model('octemplates/widgets/oct_minify');
				
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/bootstrap-reboot.min.css');
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/bootstrap.min.css');
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/fontawesome-free-5.6.1-web/css/all.css');
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/owl.carousel.min.css');
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/fonts.css');
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/main.css');
			$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/responsive.css');

			if (file_exists(DIR_TEMPLATE.'oct_ultrastore/stylesheet/dynamic_stylesheet.css')) {
				$file_size = filesize(DIR_TEMPLATE.'oct_ultrastore/stylesheet/dynamic_stylesheet.css');

				if ($file_size) {
					$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/stylesheet/dynamic_stylesheet.css');
				}
			}
			
			$data['styles'] = $this->model_octemplates_widgets_oct_minify->octMinifyCss($this->document->getOctStyles());
			
		$data['scripts'] = $this->document->getScripts('header');

$data['robots'] = $this->document->getRobots();		
			

    
        // OCFilter start
        if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/category') {
            if (isset($this->request->get['filter_ocfilter'])) {
                $counter = 0;
    
                $explodedFilters = explode(';', $this->request->get['filter_ocfilter']);
				if (count($explodedFilters) > 1)
					$data['noindex'] = true;
                foreach ($explodedFilters as $filter) {
                    $filterValues = explode(':', $filter);
					if ($filterValues[0] == 'p')
						$data['noindex'] = true;
                    //производители
                    if ($filterValues[0] == 'm') {
                        $explodedManufacturer = explode(',', $filterValues[1]);
                        if (count($explodedManufacturer) >= 2 ) {
                            $data['noindex'] = true;
                        }
                        foreach ($explodedManufacturer as $manufacturer) {
    //                        $counter++;
                        }
                    } else {
                        if (!empty($filterValues[1])) {
                            $explodedOption = explode(',', $filterValues[1]);
                            if (is_array($explodedOption) && count($explodedOption) > 1) {
                                foreach ($explodedOption as $optionFilter) {
                                    $counter++;
                                }
                            } else {
                                $counter++;
                            }
                        } else {
                            $counter++;
                        }
                    }
                }
    
                if ($counter > 1) {
                    $data['noindex'] = true;
                }
            }
        } else {
            $data['noindex'] = $this->document->isNoindex();
        }
        // OCFilter end
      
    
    
      

			$this->document->addOctScript('catalog/view/theme/oct_ultrastore/js/jquery-3.3.1.min.js');
			$this->document->addOctScript('catalog/view/theme/oct_ultrastore/js/popper.min.js');
			$this->document->addOctScript('catalog/view/theme/oct_ultrastore/js/bootstrap.min.js');
			$this->document->addOctScript('catalog/view/theme/oct_ultrastore/js/main.js');
			$this->document->addOctScript('catalog/view/theme/oct_ultrastore/js/bootstrap-notify/bootstrap-notify.js');
			$this->document->addOctScript('catalog/view/theme/oct_ultrastore/js/common.js');

			if ($this->config->get('theme_oct_ultrastore_lazyload')) {
				$this->document->addOctStyle('catalog/view/theme/oct_ultrastore/js/lazyload/jquery.lazyload.min.js');
			}
			
			$data['scripts'] = $this->model_octemplates_widgets_oct_minify->octMinifyJs($this->document->getOctScripts());
			
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}


			$data['oct_bar_data'] = $oct_bar_data = $this->config->get('theme_oct_ultrastore_bar_data');

			if (isset($oct_bar_data['status']) && $oct_bar_data['status']) {
				$data['bar_position'] = isset($oct_bar_data['position']) ? $oct_bar_data['position'] : 'left';

				if (isset($oct_bar_data['show_cart']) && $oct_bar_data['show_cart']) {
					$data['cart_total_bar'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
				}

				if (isset($oct_bar_data['show_wishlist']) && $oct_bar_data['show_wishlist']) {
					$data['wishlist_link'] = $this->url->link('account/wishlist','', true);
					if ($this->customer->isLogged()) {
						$this->load->model('account/wishlist');
						
						$data['wishlist_total'] = $this->model_account_wishlist->getTotalWishlist();
					} else {
						$data['wishlist_total'] = (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0);
					}
				}

				if (isset($oct_bar_data['show_compare']) && $oct_bar_data['show_compare']) {
					$data['compare_link'] = $this->url->link('product/compare','', true);
					$data['compare_total'] = (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);
				}
			}
			
		$this->load->language('common/header');

			$data['oct_popup_cart_status'] = $this->config->get('theme_oct_ultrastore_popup_cart_status');
			

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		

			$megamenu_setting = $this->config->get('megamenu_setting');
			
			if($megamenu_setting['status']=='1'){
				$data['megamenu_status']=true;
				$data['menuvh'] = $this->load->controller('common/menuvh');
			} else { 
				$data['megamenu_status']=false;
			}
			

				// For page specific og tags
				if (isset($this->request->get['route'])) {
					if (isset($this->request->get['product_id'])) {
						$class = '-' . $this->request->get['product_id'];
						$this->document->addOGMeta('property="og:type"', 'product');
					} elseif (isset($this->request->get['path'])) {
						$class = '-' . $this->request->get['path'];
					} elseif (isset($this->request->get['manufacturer_id'])) {
						$class = '-' . $this->request->get['manufacturer_id'];
					} elseif (isset($this->request->get['information_id'])) {
						$class = '-' . $this->request->get['information_id'];
						$this->document->addOGMeta('property="og:type"', 'article');
					} else {
						$class = '';
					}
					$data['class'] = str_replace('/', '-', $this->request->get['route']) . $class;
				} else {
					$data['class'] = 'common-home';
					$this->document->addOGMeta('property="og:type"', 'website');
				}
				$this->load->model('tool/image');
				$data['logo_meta'] = str_replace(' ', '%20', $this->model_tool_image->resize($this->config->get('config_logo'), 300, 300));
				$data['ogmeta'] = $this->document->getOGMeta();
                
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');

			$data['cart_mobile'] = $this->load->controller('common/cart/mobile');
			
		$data['cart'] = $this->load->controller('common/cart');

                $data['advtags'] = $this->load->controller('extension/analytics/advtags');
            
		
			if ($this->config->get('oct_megamenu_status')) {
				$data['menu'] = $this->load->controller('octemplates/module/oct_megamenu');
			} else {
				$data['menu'] = $this->load->controller('common/menu', ['deff' => 1]);
			}
			
		
		$data['last_order_id'] = isset($this->session->data['last_order_id']) ? $this->session->data['last_order_id'] : '';

		return $this->load->view('common/header', $data);
	}
	
	
		public	function generateGIN() {
    $bytes = random_bytes(16); // 16 байт = 128 біт
    $hex = bin2hex($bytes); // Конвертуємо байти у шістнадцятковий рядок
    $formattedHex = sprintf(
        "%s-%s-%s-%s-%s",
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4), 
        substr($hex, 20, 12)
    );
    return $formattedHex;
}


}
