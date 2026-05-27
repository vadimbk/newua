<?php
class ControllerProductCategory extends Controller {
private $seoOptimizedPages = ["/dreli-shurupoverty","/tokovye-kleshchi","/analizatory-spektra","/betonosmesiteli/obem/140-l","/akkum-nozhovki","/ruchnoj-instrument","/otvertki","/kraskopulty-elektricheskie","/akkumuljatornyj-pylesos","/lobziki","/betonosmesiteli","/elektropily/tip/pila-po-metallu","/oscillografy","/specialnye","/akkumuljatory","/akkumuljatornyj-instrument","/instrumenty","/elektroinstrument","/multimetry-1/peaktech","/multymetry/peaktech","/nabory-instrumentov","/detektory","/tiski","/anemometry","/svarochnoe-oborudovanie","/kleevye-pistolety","/pirometry","/teplovizory","/gajkoverty","/izmeritelnye-pribory","/mashiny-shlifovalnye-polirovalnye","/steplery-mexanicheskie","/elektropily","/frezery","/obzhimnoy-instrument","/dozimetry","/otbojnye-molotki","/ampermetry","/generatory-signalov","/miksery-elektricheskie","/cable-tester","/perforatory","/dreli-perforatory","/steplery","/dlya-snyatiya-izolyacii","/pily","/shlifmashiny","/zarjadnye-ustrojstva","/pylesosy-stroitelnye","/megaommetry","/multymetry","/multimetry-1"];

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
use Helper;			

			public function octAllCategories () {
				//$this->load->language('octemplates/product/octallcategories');

				$this->load->model('catalog/category');
				$this->load->model('catalog/product');

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $this->load->language('extension/module/timer');
    $data['text_timer_on_products_page'] = $this->language->get('text_timer_on_products_page');
    
    $timer_settings = $this->config->get('timer_general_settings');
    /* Bulk Specials Editor */
    
				$this->load->model('tool/image');
				
				$data['breadcrumbs'] = [];

				$data['breadcrumbs'][] = [
					'text' => $this->language->get('text_home'),
					'href' => $this->url->link('common/home')
				];
				
				$data['breadcrumbs'][] = [
					'text' => $this->language->get('text_oct_all_categories'),
					'href' => $this->url->link('octemplates/product/octallcategories', '', true)
				];
				
				//$oct_data['breadcrumbs'] = $data['breadcrumbs'];

				//$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
				

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
				$this->document->setTitle($this->language->get('text_oct_all_categories'));
				//$this->document->setDescription($category_info['meta_description']);
				//$this->document->setKeywords($category_info['meta_keyword']);
				
				$data['categories'] = [];
				
				if(isset($this->request->server['HTTP_ACCEPT']) && strpos($this->request->server['HTTP_ACCEPT'], 'webp')) {
					$oct_webP = 1 . '-' . $this->session->data['currency'];
				} else {
					$oct_webP = 0 . '-' . $this->session->data['currency'];
				}
				
				$result_all_categories = $this->cache->get('octemplates.all_categories.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . $oct_webP);
				
				if (!$result_all_categories) {
					foreach ($this->model_catalog_category->getCategories() as $category) {
						$filter_data_main = [
							'filter_category_id' => $category['category_id'],
							'filter_sub_category' => true
						];
						
				        // Level 2
				        $children_data = [];
				        
				        $children = $this->model_catalog_category->getCategories($category['category_id']);
				        
				        foreach ($children as $child) {
				            $filter_data = array(
				                'filter_category_id' => $child['category_id'],
				                'filter_sub_category' => true
				            );
				            
				            // Level 3
				            $children_data_2 = [];
				            $children_2      = $this->model_catalog_category->getCategories($child['category_id']);
				            
				            foreach ($children_2 as $child_2) {
				                $filter_data2 = [
				                    'filter_category_id' => $child_2['category_id'],
				                    'filter_sub_category' => true
				                ];
				                
				                /*
				                $children_3 = $this->model_catalog_category->getCategories($child_2['category_id']);
				                
				                $children_data_3 = [];
				                
				                foreach ($children_3 as $child_3) {
					                $filter_data3 = [
						                'filter_category_id'  => $child_3['category_id'],
						                'filter_sub_category' => true
					                ];
					                
					                $children_data_3[] = [
						                'category_id' => $child_3['category_id'],
						                'count_products' => ($this->config->get('config_product_count') ? $this->model_catalog_product->getTotalProducts($filter_data3) : ''),
						                'name'  => $child_3['name'],
						                'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child_2['category_id'] . '_' . $child_3['category_id'], true)
					                ];
				                }
				                */
				                
				                $children_data_2[] = [
				                    //'children' => $children_data_3,
				                    'category_id' => $child_2['category_id'],
				                    'count_products' => ($this->config->get('config_product_count') ? $this->model_catalog_product->getTotalProducts($filter_data2) : ''),
				                    'name' => $child_2['name'],
				                    'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child_2['category_id'], true)
				                ];
				            }
				            
				            $children_data[] = [
				                'children' => $children_data_2,
				                'count_products' => ($this->config->get('config_product_count') ? $this->model_catalog_product->getTotalProducts($filter_data) : ''),
				                'name' => $child['name'],
				                'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'], true)
				            ];
				        }
				        
				        // Level 1
				        $data['categories'][] = [
				            'name' => $category['name'],
				            'count_products' => ($this->config->get('config_product_count') ? $this->model_catalog_product->getTotalProducts($filter_data_main) : ''),
				            'thumb' => $category['image'] ? $this->model_tool_image->resize($category['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')) : $this->model_tool_image->resize('no-thumb.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')),
				            'children' => $children_data,
				            'href' => $this->url->link('product/category', 'path=' . $category['category_id'], true)
				        ];
					}
					
					$result_all_categories = $data['categories'];

					$this->cache->set('octemplates.all_categories.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . $oct_webP, $result_all_categories);
				}
				
				$data['categories'] = $result_all_categories;
				
				$data['column_left'] = $this->load->controller('common/column_left');
				$data['column_right'] = $this->load->controller('common/column_right');
				$data['content_top'] = $this->load->controller('common/content_top');
				$data['content_bottom'] = $this->load->controller('common/content_bottom');
				$data['footer'] = $this->load->controller('common/footer');

// Full IndeX →
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = isset($category_info['name']) ? $category_info['name'] : (isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '');

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// ←  Full IndeX
			

    /* Bulk Specials Editor */
    if($this->total_timers > 0) {
      # Loading custom styles for timer 
      $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

      $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
      $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
      $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

      $lang = mb_strtolower($this->language->get('code'));

      if ($lang !== 'en') {
          $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
      }
    }
    /* Bulk Specials Editor */
    
				$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			
	
				$this->response->setOutput($this->load->view('octemplates/product/oct_all_categories', $data));
			}
			

    /* Bulk Specials Editor */
    private $total_timers = 0;
    /* Bulk Specials Editor */
    
	public function index() {

			$data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');
			
			if (isset($oct_ultrastore_data['category_view_sort_oder']) && $oct_ultrastore_data['category_view_sort_oder']) {
				$oct_ultrastore_sort_data = $this->config->get('theme_oct_ultrastore_sort_data');
				
				if (isset($oct_ultrastore_sort_data['deff_sort']) && $oct_ultrastore_sort_data['deff_sort']) {
					$sort_order = explode('-', $oct_ultrastore_sort_data['deff_sort']);
				}
			}
			
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $this->load->language('extension/module/timer');
    $data['text_timer_on_products_page'] = $this->language->get('text_timer_on_products_page');
    
    $timer_settings = $this->config->get('timer_general_settings');
    /* Bulk Specials Editor */
    

		$this->load->model('tool/image');
		
		$this->load->model('extension/module/ocfilter');										  
		$data['text_page'] = $this->language->get('text_page');

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			
			$sort = (isset($sort_order) && !empty($sort_order) && isset($sort_order[0])) ? $sort_order[0] : 'p.sort_order';
			
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			
			$order = (isset($sort_order) && !empty($sort_order) && isset($sort_order[1])) ? $sort_order[1] : 'ASC';
			
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}


		// OCFilter start
    if (isset($this->request->get['filter_ocfilter'])) {
      $filter_ocfilter = $this->request->get['filter_ocfilter'];
    } else {
      $filter_ocfilter = '';
    }
		// OCFilter end
      
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}
		
		
		//Fix Category breadscrumbs FULL with SEO_PRO
    $pathway = $this->model_catalog_category->getCategoryPath($category_id);

    if($pathway){
       foreach ($pathway as $way) {
       $category_way = $this->model_catalog_category->getCategory($way['path_id']);
          $data['breadcrumbs'][] = array(
            'text'      => $category_way['name'],
            'href'      => $this->url->link('product/category', 'path=' . $way['path_id'] . $url)
           );    
       }
    }

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
			$this->document->setTitle($category_info['meta_title']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = $category_info['name'];
			$data['posad_title'] = $category_info['name'];

			$data['category_id'] = $category_info['category_id'];			


			if ($this->config->get('theme_oct_ultrastore_seo_title_status')) {
				$oct_seo_title_data = $this->config->get('theme_oct_ultrastore_seo_title_data');
				
				if ((isset($oct_seo_title_data['category']['title_status']) && $oct_seo_title_data['category']['title_status']) && (isset($oct_seo_title_data['category']['title'][$this->config->get('config_language_id')]) && !empty($oct_seo_title_data['category']['title'][$this->config->get('config_language_id')]))) {
					$oct_replace = [
						'[name]' => strip_tags(html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8')),
						'[store]' => $this->config->get('config_name')
					];
					
					$oct_seo_title = str_replace(array_keys($oct_replace), array_values($oct_replace), $oct_seo_title_data['category']['title'][$this->config->get('config_language_id')]);
					
					if ((isset($oct_seo_title_data['category']['title_empty']) && $oct_seo_title_data['category']['title_empty']) && empty($category_info['meta_title'])) {

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
						$this->document->setTitle($oct_seo_title);
					} elseif (!isset($oct_seo_title_data['category']['title_empty'])) {

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
						$this->document->setTitle($oct_seo_title);
					}
				}
				
				if ((isset($oct_seo_title_data['category']['description_status']) && $oct_seo_title_data['category']['description_status']) && (isset($oct_seo_title_data['category']['description'][$this->config->get('config_language_id')]) && !empty($oct_seo_title_data['category']['description'][$this->config->get('config_language_id')]))) {
					$oct_replace = [
						'[name]' => strip_tags(html_entity_decode($category_info['name'], ENT_QUOTES, 'UTF-8')),
						'[store]' => $this->config->get('config_name')
					];
					
					$oct_seo_description = str_replace(array_keys($oct_replace), array_values($oct_replace), $oct_seo_title_data['category']['description'][$this->config->get('config_language_id')]);
					
					if ((isset($oct_seo_title_data['category']['description_empty']) && $oct_seo_title_data['category']['description_empty']) && empty($category_info['meta_description'])) {
						$this->document->setDescription($oct_seo_description);
					} elseif (!isset($oct_seo_title_data['category']['description_empty'])) {
						$this->document->setDescription($oct_seo_description);
					}
				}
			}
			
$data['heading_title_new'] = $data['heading_title'];

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
         * "Ножовки" 450,
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
         * Ищем оптимизированнные под сео страницы
         * если находим, заменяем h1
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
                    $h1 = $queryOcfilterPageOptimizeData->row['title'];
                }
            }
        }

        /**
         * Title для страницы пагинации листинга товаров
         */
        if (!empty($this->request->get['page']) and $this->config->get('config_language_id')==2) {
            $h1 = $h1 . " - страница №{$this->request->get['page']}";
            $description = null;
        }
		if (!empty($this->request->get['page']) and $this->config->get('config_language_id')==3) {
		    $h1 = $h1 . " - сторінка №{$this->request->get['page']}";
            $description = null;
		}

        $data['heading_title_new'] = trim($h1);

        if (in_array($_SERVER['REQUEST_URI'], $this->seoOptimizedPages)) {
            $data['heading_title_new'] = $data['heading_title'];
        }
    }
}
			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

			// Set the last category breadcrumb
			//$data['breadcrumbs'][] = array(
			//	'text' => $category_info['name'],
			//	'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			//);

			

				if ( ($this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width') < 300) || ($this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height') < 300) ) {
				    $this->document->addOGMeta( 'property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($category_info['image'], 300, 300)) );
					$this->document->addOGMeta('property="og:image:width"', '300');
					$this->document->addOGMeta('property="og:image:height"', '300');
				} else { 
				    $this->document->addOGMeta( 'property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'))) );
					$this->document->addOGMeta('property="og:image:width"', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'));
					$this->document->addOGMeta('property="og:image:height"', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
				}
                
			if ($category_info['image'] && (isset($oct_ultrastore_data['category_cat_image']) && $oct_ultrastore_data['category_cat_image'])) {
			
				$data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
			} else {

		    	$this->document->addOGMeta( 'property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($this->config->get('config_logo'), 300, 300)) );
				$this->document->addOGMeta('property="og:image:width"', '300');
				$this->document->addOGMeta('property="og:image:height"', '300');
                
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');

			if (!isset($oct_ultrastore_data['category_desc_in_page']) && $page > 1) {
				$data['description'] = false;
				$data['thumb'] = false;
			} else {
				$data['description'] = str_replace("<img", "<img class='img-fluid'", html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'));
			}
			
			$data['compare'] = $this->url->link('product/compare');

				  $data['video'] = $category_info['video'];
				

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['categories'] = array();

			if(isset($oct_ultrastore_data['category_view_subcats']) && $oct_ultrastore_data['category_view_subcats'] == 'on'){
			


			if(isset($this->request->server['HTTP_ACCEPT']) && strpos($this->request->server['HTTP_ACCEPT'], 'webp')) {
				$oct_webP = 1 . '-' . $this->session->data['currency'];
			} else {
				$oct_webP = 0 . '-' . $this->session->data['currency'];
			}

			//$oct_categories = $this->cache->get('octemplates.sub_categories.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$category_id . '.' . $oct_webP);

			if (!$oct_categories) {
			
			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {

			if ($result['image'] && file_exists(DIR_IMAGE.$result['image'])) {
				$cat_image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_height'));
			} else {
				$cat_image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_height'));
			}
			
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),

			'image' => $cat_image,
			
					/*'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)*/
					'href'  => $this->url->link('product/category', 'path=' .  $result['category_id'] . $url)
				);

				$oct_categories = $data['categories'];

				//$this->cache->set('octemplates.sub_categories.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$category_id . '.' . $oct_webP, $oct_categories);
			}

			$data['categories'] = $oct_categories;
			
			}


			}
			
			$data['products'] = array();

			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			

	        $oct_ultrastore_data_atributes = $this->config->get('theme_oct_ultrastore_data_atributes');
			

			$filter_data = array(
				'filter_category_id' => $category_id,

			'filter_sub_category' => (isset($oct_ultrastore_data['category_subcat_products']) && $oct_ultrastore_data['category_subcat_products'] == 'on') ? true : false,
			
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
			/* fixed by darkedhart */
			require_once DIR_APPLICATION . 'controller/extension/module/ocdepartment.php';
			// Создайте объект контроллера
			$ocdepartment = new ControllerExtensionModuleOCDepartment($this->registry);
			if ((int)$category_id == (int)$ocdepartment->getCatNovinkiId() && isset($this->request->get['filter_category_id']))
				$filter_data['filter_category_id2'] = $this->request->get['filter_category_id'];
			/* / fixed by darkedhart */
			

            $filter_data = $this->load->controller('extension/module/fx/m_filter', $filter_data); // Full IndeX
			

  		// OCFilter start
  		$filter_data['filter_ocfilter'] = $filter_ocfilter;

      if (isset($this->request->get['filter_ocfilter']) && $this->config->get('module_ocfilter_sub_category') && empty($filter_data['filter_sub_category'])) {
      	$filter_data['filter_sub_category'] = true;

        $data['categories'] = array();
      }
  		// OCFilter end
      
			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			/** EET Module */
			if (isset($page) && isset($limit)) {
				$ee_position = ($page - 1) * $limit + 1;
			} else {
				$ee_position = 1;
			}
			$data['ee_tracking'] = $this->config->get('module_ee_tracking_status');
			if ($data['ee_tracking'] && $results) {
				$data['ee_impression'] = $this->config->get('module_ee_tracking_impression_status');
				$data['ee_impression_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_impression_log') : false;
				$data['ee_click'] = $this->config->get('module_ee_tracking_click_status');
				$data['ee_cart'] = $this->config->get('module_ee_tracking_cart_status');
				$data['ee_ga_callback'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_ga_callback') : 0;
				$data['ee_generate_cid'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_generate_cid') : 0;
				$ee_class_array = preg_split('/(?=[A-Z])/', get_class($this));
				$data['ee_type'] = strtolower(array_pop($ee_class_array));
				$ee_data = array('type' => $data['ee_type']);
				$ee_data['position'] = $ee_position;
				foreach ($results as $item) {
					$ee_data['products'][] = $item['product_id'];
				}
				$data['ee_impression_data'] = json_encode($ee_data);
			}
			/** EET Module */
            

			$oct_product_stickers = [];
			$data['sticker_colors'] = [];
			
			if ($this->config->get('oct_stickers_status')) {
				$oct_stickers = $this->config->get('oct_stickers_data');
				
				$data['oct_sticker_you_save'] = false;
				
				if ($oct_stickers) {
					$data['oct_sticker_you_save'] = isset($oct_stickers['stickers']['special']['persent']) ? true : false;
				}
				
				$this->load->model('octemplates/stickers/oct_stickers');
			}
			

			foreach ($results as $result) {

			if ($result['image'] && file_exists(DIR_IMAGE.$result['image'])) {
				$cat_image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_height'));
			} else {
				$cat_image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_sub_category_height'));
			}
			
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				
    /* Bulk Specials Editor */
    $timer = false;

    if ((float)$result['special']) {
      if ($timer_exist && isset($timer_settings['timer_category_page_status'])) {
        $timer = $result['timer'];

        $result['date_end'] = ($hours_days && isset($result['datetime_end'])) ? $result['datetime_end'] : $result['date_end'];

        $special_date_diff   = $this->model_extension_module_timer->getSpecialDateDiff($result['date_end']);
        $percentage_discount = $this->model_extension_module_timer->calculateTotalDiscount($result['price'], $result['special']);

        $this->total_timers++;
      } else {
        $timer = false;
      }
    /* Bulk Specials Editor */
    
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				
				


			/*if ($result['quantity'] <= 0) {
				$data['stock'] = $result['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $result['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}*/
			



			$oct_atributes = false;
				
			if (isset($oct_ultrastore_data_atributes) && $oct_ultrastore_data_atributes) {
				$limit_attr  = $this->config->get('theme_oct_ultrastore_data_cat_atr_limit') ? $this->config->get('theme_oct_ultrastore_data_cat_atr_limit') : 5;
				
				$oct_atributes = $this->model_catalog_product->getOctProductAttributes($result['product_id'], $limit_attr);
			}
			

				if ($result['quantity'] <= 0) {
					$stock = $result['stock_status'];
				} else {
					$stock = false;
				}

				$can_buy = true;

				if ($result['quantity'] <= 0 && !$this->config->get('config_stock_checkout')) {
					$can_buy = false;
				} elseif ($result['quantity'] <= 0 && $this->config->get('config_stock_checkout')) {
					$can_buy = true;
				}
			

			if (isset($oct_stickers) && $oct_stickers) {
				$oct_stickers_data = $this->model_octemplates_stickers_oct_stickers->getOCTStickers($result);
				
				$oct_product_stickers = [];
				
				if (isset($oct_stickers_data) && $oct_stickers_data) {
					$oct_product_stickers = $oct_stickers_data['stickers'];
					$data['sticker_colors'][] = $oct_stickers_data['sticker_colors'];
				}
			}
			

           if(!empty($result['product_id'])){
            $AvailArray = Array(
                'quantity' => $result['quantity'],
                'stock_status_id' => $result['stock_status_id'],
                'product_id' => $result['product_id'],
                );
            } else if(!empty($product_info['product_id'])){
             $AvailArray = Array(
                'quantity' => $product_info['quantity'],
                'stock_status_id' => $product_info['stock_status_id'],
                'product_id' => $product_info['product_id'],
                );
            } else if(!empty($product['product_id'])){
            $AvailArray = Array(
                'quantity' => $product['quantity'],
                'stock_status_id' => $product['stock_status_id'],
                'product_id' => $product['product_id'],
                );
            } else {
            $AvailArray = false;
            }


           if($AvailArray) {
                $avail_product_quantity =  $this->load->controller('extension/module/avail/GetProductStatus',$AvailArray);
           }  else {
               $avail_product_quantity = false;
           }
        

$this->load->model('extension/module/promotion');
$promotions = $this->model_extension_module_promotion->getHTMLProductPromotions($result['product_id']);
      
				$data['products'][] = array(	

'statuses'    => $result['statuses']['category'],
'stickers'    => $result['statuses']['category_stickers'],        
      

'promotion'   => $promotions['category'],
      
					'ee_position' => isset($ee_position) ? $ee_position++ : '',
 'avail_product_quantity'	  => $avail_product_quantity,

	 'manufacturer'    => !empty($result['manufacturer']) ? $result['manufacturer'] : '',
	 'model'           => $result['model'],
	 'google_price'    => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_google_currency'), '', false),
	 'facebook_price'  => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_facebook_currency'), '', false),
	 'ecommerce_price' => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_ecommerce_currency'), '', false),
	 'tiktok_price'    => $this->currency->format($result['special'] ? $result['special'] : $result['price'], $this->config->get('remarketing_tiktok_currency'), '', false),
	  
					'product_id'  => $result['product_id'],

			'oct_stickers'  => $oct_product_stickers,
			'you_save'	  	=> $result['you_save'],
			
					'thumb'       => $image,

			'oct_atributes'       => $oct_atributes,
			
					'name'        => $result['name'],
					
			'description' => (isset($oct_ultrastore_data['category_product_desc']) && $oct_ultrastore_data['category_product_desc'] == 'on') ? utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..' : false,
			
					'price'       => $price,

    /* Bulk Specials Editor */
    'special_date_diff'  => $timer == 1 ? $special_date_diff : '',
    'percentage_discount'=> $timer == 1 ? $percentage_discount : '',
    'timer'              => $timer,
    /* Bulk Specials Editor */
    
					'sku'         => $result['sku'],
					/*'stock'		  => $result['stock'],*/
					/*'stock'       => $result['quantity'],*/
					'stock_status_id' => $result['stock_status_id'],								 
					'stock_status'=> $result['stock_status'],
					'quantity' 	  => $result['quantity'],
					'special'     => $special,

					'stock'     => $stock,
					'can_buy'   => $can_buy,
			
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => 
			$this->config->get('config_review_status') ? $result['rating'] : false,
			'oct_model'	  => $this->config->get('theme_oct_ultrastore_data_model') ? $result['model'] : '',
			'reviews'	  => $result['reviews'],
			'quantity'	  => $result['quantity'] <= 0 ? true : false,
			
					/*'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url)*/
					'href'        =>$this->url->link('product/product', '&product_id=' . $result['product_id'])
				);
			}
/*ocfilter-popular*/

$data['options']              = $this->ocfilter->getOCFilterOptions();

/*ocfilter-popular*/

			$url = '';


      // OCFilter start
			if (isset($this->request->get['filter_ocfilter'])) {
				$url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter'];
			}
      // OCFilter end
      
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['sorts'] = array();

			/*$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);*/
			
			$data['sorts'][] = array(
					'text'  => $this->language->get('text_bestseller'),
					'value' => 'bestseller-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=bestseller&order=DESC' . $url)
			);
			
			$data['sorts'][] = array(
					'text'  => $this->language->get('text_special'),
					'value' => 'special-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=special&order=DESC' . $url)
			);
			

			
			$data['sorts'][] = array(
					'text'  => $this->language->get('text_review'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			/*if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}*/

			/*$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);*/

			if ((isset($oct_ultrastore_sort_data) && !empty($oct_ultrastore_sort_data)) && (isset($oct_ultrastore_sort_data['sort']) && !empty($oct_ultrastore_sort_data['sort']))) {
				$data['sorts'] = [];
				
				foreach ($oct_ultrastore_sort_data['sort'] as $oct_sort) {
					$sort_order = explode('-', $oct_sort);
					
					$sort_name = str_replace(['.','-'], ['_', '_'], $oct_sort);
					
					if (!$this->config->get('config_review_status') && $sort_order[0] == 'rating') {
						continue;
					}
					
					$data['sorts'][] = array(
						'text'  => $this->language->get('text_' . $sort_name),
						'value' => $oct_sort,
						'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=' . $sort_order[0] . '&order='. $sort_order[1] . $url)
					);
				}
			}
			

			$url = '';


      // OCFilter start
			if (isset($this->request->get['filter_ocfilter'])) {
				$url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter'];
			}
      // OCFilter end
      
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';


      // OCFilter start
			if (isset($this->request->get['filter_ocfilter'])) {
				$url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter'];
			}
      // OCFilter end
      
			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}


			//begin_devos_attribute_ext
      		$this->load->model('catalog/devos_attribute_ext');
      		$data['products'] = $this->model_catalog_devos_attribute_ext->daeCatalog($data['products'], array('category_id' => $category_id));
      		//end_devos_attribute_ext
      

		    	$this->document->addOGMeta('property="og:url"', $this->url->link('product/category', 'path=' . $category_info['category_id'] . ( ($page != 1) ? '&page='. $page : '' ), true) );
                
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');


		if ($this->config->get('sp_auto_seo_faq_status')) {
			$this->load->model('extension/module/sp_auto_seo_faq');
			$data['faq_output'] = $this->model_extension_module_sp_auto_seo_faq->getCategoryFaq($category_info, $data, $page);
		}
		
			$data['pagination'] = $pagination->render();

	  // remarketing all in one  
	      $this->load->model('tool/remarketing');
	      if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot() && !isset($filter_gr)) {
		  	  if (empty($data['heading_title'])) $data['heading_title'] = $this->language->get('heading_title');
		  	  $data = array_merge($data, $this->model_tool_remarketing->processCategory((!empty($category_info) ? $category_info : []), $data['heading_title'], $data['products']));
	      }  
	  

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. $page), 'canonical');
			}
			
			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
			}


			if (isset($data['sticker_colors']) && $data['sticker_colors']) {
				$oct_color_stickers = [];
				
				foreach ($data['sticker_colors'] as $sticker_colors) {
					foreach ($sticker_colors as $key=>$sticker_color) {
						$oct_color_stickers[$key] = $sticker_color;
					}
				}
				
				$data['sticker_colors'] = $oct_color_stickers;
			}
			
			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

      // OCFilter Start
      if ($this->ocfilter->getParams()) {
        if (isset($product_total) && !$product_total) {
      	  $this->response->redirect($this->url->link('product/category', 'path=' . $this->request->get['path']));
        }

        $data['description'] = '';

        if (isset($data['description_bottom'])) {
          $data['description_bottom'] = '';
        }


			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
        $this->document->setTitle($this->ocfilter->getPageMetaTitle($this->document->getTitle()));
			  $this->document->setDescription($this->ocfilter->getPageMetaDescription($this->document->getDescription()));
        $this->document->setKeywords($this->ocfilter->getPageMetaKeywords($this->document->getKeywords()));

        $data['heading_title'] = $data['seo_h1'] = $this->ocfilter->getPageHeadingTitle($data['heading_title']);

				  $data['video_ocfilter'] = $this->ocfilter->getVideo();
				

        if (isset($data['description_bottom'])) {
          $data['description_bottom'] = $this->ocfilter->getPageDescription();
        } else {
          $data['description'] = $this->ocfilter->getPageDescription();
        }

        if (!trim(strip_tags(html_entity_decode($data['description'], ENT_QUOTES, 'UTF-8')))) {

		    	$this->document->addOGMeta( 'property="og:image"', str_replace(' ', '%20', $this->model_tool_image->resize($this->config->get('config_logo'), 300, 300)) );
				$this->document->addOGMeta('property="og:image:width"', '300');
				$this->document->addOGMeta('property="og:image:height"', '300');
                
        	$data['thumb'] = '';
        }

        $breadcrumb = $this->ocfilter->getPageBreadCrumb();

        if ($breadcrumb) {
  			  $data['breadcrumbs'][] = $breadcrumb;
        }

        $this->document->deleteLink('canonical');
        $this->document->deleteLink('prev');
        $this->document->deleteLink('next');

        if ($page > 1) {
          $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'], true), 'canonical');
        }

  			if ($page == 2) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'], true), 'prev');
  			} else if ($page > 2) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'] . '&page=' . ($page - 1), true), 'prev');
  			}

  			if ($limit && ceil($product_total / $limit) > $page) {
  			  $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&filter_ocfilter=' . $this->request->get['filter_ocfilter'] . '&page=' . ($page + 1), true), 'next');
  			}
      }
      // OCFilter End
      

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');

// Full IndeX →
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = isset($category_info['name']) ? $category_info['name'] : (isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '');

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// ←  Full IndeX
			

    /* Bulk Specials Editor */
    if($this->total_timers > 0) {
      # Loading custom styles for timer 
      $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

      $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
      $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
      $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

      $lang = mb_strtolower($this->language->get('code'));

      if ($lang !== 'en') {
          $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
      }
    }
    /* Bulk Specials Editor */
    
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

// ===== OCT Blog articles for category =====
$this->load->model('catalog/category');
$this->load->model('tool/image');
$data['oct_blogarticles'] = [];

$article_ids = $this->model_catalog_category->getCategoryBlogArticles($category_id);

if ($article_ids) {
    $articles = $this->model_catalog_category->getOctBlogarticlesByIds($article_ids);

foreach ($articles as $article) {
    $image = '';
        if (!empty($article['image']) && is_file(DIR_IMAGE . $article['image'])) {
            $image = $article['image'];
        } elseif (is_file(DIR_IMAGE . 'placeholder.png')) {
            $image = 'placeholder.png';
        } else {
            $image = '';
        }

    $data['oct_blogarticles'][] = [
        'article_id'         => (int)$article['article_id'],
        'name'               => $article['name'],
        // Announce hidden: article text length varies per article and broke
        // the category card grid layout. Re-enable here and in category.twig.
        // 'description'     => html_entity_decode($article['shot_description'] ?? '', ENT_QUOTES, 'UTF-8'),
        'date_added'         => date('d.m.Y', strtotime($article['date_added'])),
        'thumb'              => $this->model_tool_image->resize($image, 300, 200),
        'href'               => $this->url->link('octemplates/blog/oct_blogarticle', 'blogarticle_id=' . (int)$article['article_id'])
    ];
}
}
// =========================================											 
			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/category', $url)
			);


			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');


	        $oct_404_page_status = $this->config->get('oct_404_page_status');
			
	        if ($oct_404_page_status) {
		        $oct_404_page_data = $this->config->get('oct_404_page_data');
		        
	            if (isset($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title']) && !empty($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title'])) {
	                $data['heading_title'] = $oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['title'];

			if (
			isset($this->request->get['limit']) ||
			isset($this->request->get['order'])
			) {
				$this->document->setRobots('noindex,nofollow');
			}
			
			
	                $this->document->setTitle($data['heading_title']);
	            }
				
				$data['oct_404_image'] = '';
				
	            if (isset($oct_404_page_data['image']) && !empty($oct_404_page_data['image'])) {
	                if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
	        			$data['oct_404_image'] = $this->config->get('config_ssl') . 'image/' . $oct_404_page_data['image'];
	        		} else {
	        			$data['oct_404_image'] = $this->config->get('config_url') . 'image/' . $oct_404_page_data['image'];
	        		}
	            }
	            
	            if (isset($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['text']) && !empty($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['text'])) {
	            	$data['text_error'] = html_entity_decode($oct_404_page_data['module_text'][(int)$this->config->get('config_language_id')]['text'], ENT_QUOTES, 'UTF-8');
				}
	        }
			
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');

// Full IndeX →
			$fx['data'] = $data;
			if (isset($product_total)) $fx['total'] = $product_total;
			$fx['name'] = isset($category_info['name']) ? $category_info['name'] : (isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '');

			$out = $this->load->controller('extension/module/fx', $fx);
			$data = array_merge($data, $out['data']);
// ←  Full IndeX
			

    /* Bulk Specials Editor */
    if($this->total_timers > 0) {
      # Loading custom styles for timer 
      $data['timer_custom_css_styles'] = $this->model_extension_module_timer->getCustomCSSStyles();

      $this->document->addStyle('catalog/view/javascript/timer/css/timer.css');
      $this->document->addScript('catalog/view/javascript/timer/jquery.plugin.min.js');
      $this->document->addScript('catalog/view/javascript/timer/jquery.countdown.min.js');

      $lang = mb_strtolower($this->language->get('code'));

      if ($lang !== 'en') {
          $this->document->addScript('catalog/view/javascript/timer/jquery.countdown-' . $lang . '.js');
      }
    }
    /* Bulk Specials Editor */
    
			$data['header'] = $this->load->controller('common/header');

			$oct_data['breadcrumbs'] = $data['breadcrumbs'];

			$data['oct_breadcrumbs'] = $this->load->controller('common/header/octBreadcrumbs', $oct_data);
			

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
