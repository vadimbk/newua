<?php
class ControllerExtensionModuleOCDepartment extends Controller {
  private $setting = array();

  private $parent_id = 0;
  private $category_id = 0;
  private $location = '';
  private $url_params = '';
  private $catnovinki = 812;
  private $path = '';
  private $manufacturer_id = 0;
  private $manufacturer_info;
  private $search = '';
  private $description = false;
  private $ocfilter;

  protected function init($setting) {
    $this->load->model('extension/module/ocdepartment');

    $this->setting = $setting;

    $this->url_params = $this->getURLParams();

    if (isset($this->request->get['manufacturer_id'])) {
      $this->manufacturer_id = $this->request->get['manufacturer_id'];
    }

    if (isset($this->request->get['search'])) {
      $this->search = urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
    }

    if (isset($this->request->get['description'])) {
      $this->description = $this->request->get['description'];
    }

    if (isset($this->request->get['path'])) {
      $parts = explode('_', (string)$this->request->get['path']);
    } else {
      $parts = array();
    }

    $parts = array_filter($parts, 'strlen');

    if ($parts) {
      $this->category_id = array_pop($parts);
    } else if (isset($this->request->get['category_id'])) {
      // When search with category
      $this->category_id = $this->request->get['category_id'];
    } else if (isset($this->request->get['filter_category_id'])) {
      // In product/special
      $this->category_id = $this->request->get['filter_category_id'];
    } else {
      $this->category_id = 0;
    }

    if ($parts) {
      $this->path = implode('_', $parts);
    }

    if ($parts) {
      $this->parent_id = array_pop($parts);
    } else {
      $this->parent_id = 0;
    }

    // Manufacturer info as heading
    if ($this->manufacturer_id) {
      $this->load->model('catalog/manufacturer');

      $this->manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->manufacturer_id);
    } else {
      $this->manufacturer_info = false;
    }

    // Get current location page
    $this->location = '';

    if (!isset($this->request->get['path'])) {
      if ($this->manufacturer_info) {
        $this->location = 'manufacturer';
      } else if (trim($this->search) && utf8_strlen($this->search) > 2) {
        $this->location = 'search';
      } else if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/special') {
        $this->location = 'special';
      }
    } else {
      $this->location = 'category';
    }

    if ($this->location == 'manufacturer' && $this->isOCFInstalled() && !$this->registry->has('ocfilter')) {
      $this->ocfilter = new OCFilter();
    }
  }

  public function index($setting = array()) {
    $this->init($setting);

    $data = $this->load->language('extension/module/ocdepartment');

    // Parent category as heading
    $data['parent'] = array();

    if ($this->parent_id) {
      $parent_info = $this->model_catalog_category->getCategory($this->parent_id);

      if ($parent_info) {
        $data['parent'] = array(
          'name' => $parent_info['name'],
          'href' => $this->url->link('product/category', 'path=' . str_replace('_' . $this->category_id, '', $this->path))
        );
      }
    }


    if ($this->manufacturer_info) {
      $data['manufacturer'] = $this->manufacturer_info['name'];
    } else {
      $data['manufacturer'] = '';
    }

    // Category results
    if ($this->location == 'manufacturer') {
      $categories = $this->model_extension_module_ocdepartment->getManufacturerCategories($this->manufacturer_id);
    } else if ($this->location == 'search') {
      $categories = $this->model_extension_module_ocdepartment->getProductSearchCategories($this->search, $this->description);
    } else if ($this->location == 'special') {
      $categories = $this->model_extension_module_ocdepartment->getSpecialCategories();
	 
    } else if ($this->location == 'category') {
		
		/*if($this->category_id == 777)
				$categories = $this->model_extension_module_ocdepartment->getSpecialCategories();
			else*/
				if($this->category_id == 812){
				
				$categories = $this->model_extension_module_ocdepartment->getCategoriesNovinki($this->category_id);
			}
			else{}
      
	  /*if (!$categories) {
        $categories = $this->model_extension_module_ocdepartment->getCategories($this->parent_id);
      }*/
	 
	 
    } else {
      //$categories = array();
    }
	
    if ($categories) {
      // Temporary category list with parent_id > 0 key
      /*
	  $_categories = array();

      foreach ($categories as $key => $category) {
        if ($category['parent_id'] > 0) {
          if (isset($category['level']) && isset($category['max_level']) && $category['level'] == $category['max_level']) {
            if (!isset($_categories[$category['parent_id']])) {
              $_categories[$category['parent_id']] = array();
            }

            $_categories[$category['parent_id']][] = $category;

            unset($categories[$key]);
          }
        }
      }

      // Set children and remove from temp
      foreach ($categories as $key => $category) {
        if (isset($_categories[$category['category_id']])) {
          $children = $_categories[$category['category_id']];

          unset($_categories[$category['category_id']]);
		  print_r($category);
		  die();
        } else {
          $children = array();
        }

        $categories[$key]['children'] = $children;
      }

      // Move unused child categories to primary array
	  
      if ($_categories) {
        foreach ($_categories as $parent_id => $children) {
        	$categories = array_merge($categories, $children);
        }
      }
	  */
	
	//$nestedCategories = $this->buildCategoryTree($categories, 0);
	 
	 $nestedCategories = 	$this->buildCategoryTree2($categories);
	 
	 //die();
      $path = $this->path;

      if ($path) {
        $path .= '_';
      }

      $data['categories'] = array();
	 
	  
	  foreach ($nestedCategories as $category) {
        if ((int)$category['category_id'] == $this->catnovinki)
			continue;
		$children_data = array();
		$children_active = false;
        if (isset($category['children'])) {
          foreach ($category['children'] as $child) {
			  
			   $children_data2 = array();
				$children_active2 = false;
				$total2 = 0;
				if (isset($child['children'])) {
				  
				  foreach ($child['children'] as $child2) {
					if ($setting['show_total'] && isset($child2['total']) && $child2['total'] > 0) {
						$total = $child2['total'];
					} else {
					  $total = '';
					}
					if ((int)$this->category_id !== $this->catnovinki)
						$total = $this->model_extension_module_ocdepartment->getTotalProductSpecials(array('filter_category_id' => $child2['category_id'],'filter_sub_category' => true));
					
					if ((int)$this->category_id !== $this->catnovinki && isset($this->request->get['filter_category_id']))
						if((int)$child2['category_id'] == (int)$this->request->get['filter_category_id'])
							$children_active2 = true;
					else
						if($child2['category_id'] == $this->category_id)
							$children_active2 = true;
					
					$total2 += (int)$total;
					
					$children_data2[] = array(
					  'category_id' => $child2['category_id'],
					  'name' => $child2['name'],
					  'total' => $total,
					  'active' => ($child2['category_id'] == $this->category_id),
					  'href' => $this->getCategoryLink($child2, $path . $child['category_id'] . '_' . $child2['category_id'])
					);
					
					
				  }
				}
				
				
            if ($setting['show_total'] && isset($child['total']) && $child['total'] > 0) {
            	$total = $child['total'];
            } else {
              $total = '';
            }
			if ((int)$this->category_id !== $this->catnovinki)
				$total = $this->model_extension_module_ocdepartment->getTotalProductSpecials(array('filter_category_id' => $child['category_id'],'filter_sub_category' => true));
			
			if (isset($this->request->get['filter_category_id']))
				if($child['category_id'] == $this->request->get['filter_category_id'])
					$children_active = true;
			else
				if($child['category_id'] == $this->category_id)
					$children_active = true;
				
            if (isset($this->request->get['filter_category_id']))
				$children_data[] = array(
				  'category_id' => $child['category_id'],
				  'children'    => $children_data2,
				  'children_active'    => $children_active2,
				  'name' => $child['name'],
				  //'total' => ((int)$total - $total2),
				  'total' => $total,
				  'active' => ($child['category_id'] == $this->request->get['filter_category_id']),
				  'href' => $this->getCategoryLink($child, $path . $category['category_id'] . '_' . $child['category_id'])
				);
			else
				$children_data[] = array(
				  'category_id' => $child['category_id'],
				  'children'    => $children_data2,
				  'children_active'    => $children_active2,
				  'name' => $child['name'],
				  //'total' => ((int)$total - $total2),
				  'total' => $total,
				  'active' => ($child['category_id'] == $this->category_id),
				  'href' => $this->getCategoryLink($child, $path . $category['category_id'] . '_' . $child['category_id'])
				);
			
          }
        }

        if ($setting['show_total'] && isset($category['total']) && $category['total'] > 0 && !$children_data) {
        	$total = $category['total'];
        } else {
          $total = '';
        }
		if (isset($this->request->get['filter_category_id']))
			$data['categories'][] = array(
			  'category_id' => $category['category_id'],
			  'name'        => $category['name'],
			  'total'       => (int)$category['total'],
			  'children'    => $children_data,
			  'children_active'    => $children_active,
			  'active'      => ($category['category_id'] == $this->request->get['filter_category_id']),
			  'href'        => $this->getCategoryLink($category, $path)
			);
		else
			$data['categories'][] = array(
			  'category_id' => $category['category_id'],
			  'name'        => $category['name'],
			  'total'       => (int)$category['total'],
			  'children'    => $children_data,
			  'children_active'    => $children_active,
			  'active'      => ($category['category_id'] == $this->category_id),
			  'href'        => $this->getCategoryLink($category, $path)
			);
      }
	 
	  $data['location'] = $this->location;

      $data['collapse_parent'] = $this->setting['collapse_parent'];
      $data['collapse_parent_limit'] = $this->setting['collapse_parent_limit'];
      $data['collapse_child'] = $this->setting['collapse_child'];
      $data['collapse_child_limit'] = $this->setting['collapse_child_limit'];

  		if (file_exists(DIR_TEMPLATE . $this->config->get($this->config->get('config_theme') . '_directory') . '/stylesheet/ocdepartment.css')) {
  			$this->document->addStyle('catalog/view/theme/' . $this->config->get($this->config->get('config_theme') . '_directory') . '/stylesheet/ocdepartment.css');
  		} else {
  			$this->document->addStyle('catalog/view/theme/default/stylesheet/ocdepartment.css');
      }

      return $this->load->view('extension/module/ocdepartment/module', $data);
    }
  }

  protected function getURLParams() {
    $url = '';

    if (isset($this->request->get['manufacturer_id'])) {
      if ($this->config->get('ocfilter_status') || $this->config->get('module_ocfilter_status')) {
        $url .= '&filter_ocfilter=m:' . $this->request->get['manufacturer_id'];
      } else {
        $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
      }
    }

    if (isset($this->request->get['search'])) {
      $url .= '&search=' . urlencode(html_entity_decode($this->request->get['search'], ENT_QUOTES, 'UTF-8'));
    }

    if (isset($this->request->get['description'])) {
      $url .= '&description=' . $this->request->get['description'];
    }

    return $url;
  }

  protected function getCategoryLink($category_info, $path) {
	//  print_r('this->category_id='.$this->category_id);
    if ($this->location == 'special' OR (int)$this->category_id == 812) {
      /* fixed darkedhart */
	  $filter_ocfilter = "";
	  
	  if (isset($this->request->get['filter_ocfilter']))
		$filter_ocfilter = "filter_ocfilter=".$this->request->get['filter_ocfilter']."&";
	  if((int)$this->category_id == 812){
		  if(isset($this->session->data['language']) &&  $this->session->data['language'] == 'uk-ua')
			  $fixedlink = HTTPS_SERVER . 'uk/novinki?'.$filter_ocfilter . 'filter_category_id=' . $category_info['category_id'] . $this->url_params;
		  else
				$fixedlink = HTTPS_SERVER . 'novinki?'.$filter_ocfilter . 'filter_category_id=' . $category_info['category_id'] . $this->url_params;
		}
	  else{
		  if(isset($this->session->data['language']) &&  $this->session->data['language'] == 'uk-ua')
			  $fixedlink = HTTPS_SERVER . 'uk/special?'.$filter_ocfilter . 'filter_category_id=' . $category_info['category_id'] . $this->url_params;
		  else
			   $fixedlink = HTTPS_SERVER . 'special?'.$filter_ocfilter . 'filter_category_id=' . $category_info['category_id'] . $this->url_params;
	  }
	  //$fixedlink = str_replace("special/special", "special", $fixedlink);
	  return $fixedlink;
	  /* fixed darkedhart */
	  
	  
    } else if ($this->location == 'search') {
      return $this->url->link('product/search', 'category_id=' . $category_info['category_id'] . '&sub_category=true' .  $this->url_params);
    } else if ($this->location == 'category' || $this->setting['link_to'] == 'category') {
      if (isset($category_info['path'])) {
        $_path = $category_info['path'];
      } else {
        if ($this->location == 'category') {
        	$_path = $path . $category_info['category_id'];
        } else {
        	$_path = $category_info['category_id'];
        }
      }

      return $this->OCFRewrite($this->url->link('product/category', 'path=' . $_path . $this->url_params));
    } else if ($this->location == 'manufacturer') {
      return $this->url->link('product/manufacturer/info', 'filter_category_id=' . $category_info['category_id'] . $this->url_params);
    }
  }
	protected function buildCategoryTree($categories, $parentId = 0) {
		$branch = [];
			
		foreach ($categories as $key => $category) {
			print_r($category['name'].' parent_id' . ' parentId='.$parentId."<br>");
			if ($category['parent_id'] == $parentId) {
				// Убираем категорию, чтобы уменьшить число итераций
				unset($categories[$key]);

				// Рекурсивно находим дочерние категории
				$children = $this->buildCategoryTree($categories, $category['category_id']);

				if ($children) {
					$category['children'] = $children;
				} else {
					$category['children'] = [];
				}

				$branch[] = $category;
			}
		}
		return $branch;
	}
	protected function buildCategoryTree2($categories) {
		$tree = []; // Финальное дерево
		$references = []; // Ссылки на все категории

		// Сначала преобразуем массив категорий для быстрого доступа
		foreach ($categories as $category) {
			$category['children'] = []; // Инициализируем массив детей
			$references[$category['category_id']] = $category;
		}

		// Строим дерево
		foreach ($references as $id => &$category) {
			if ($category['parent_id'] == 0) {
				// Категория без родителя — это корень
				$tree[$id] = &$category;
			} elseif (isset($references[$category['parent_id']])) {
				// Если родитель существует, добавляем в его children
				$references[$category['parent_id']]['children'][$id] = &$category;
			} else {
				// Родитель не найден, добавляем в корень
				$tree[$id] = &$category;
			}
		}

		return $tree;
	}
	public function getCatNovinkiId() { 
		return $this->catnovinki;
	}
  protected function isOCFInstalled() {
    return ($this->config->get('ocfilter_status') || $this->config->get('module_ocfilter_status'));
  }

  protected function OCFRewrite($link) {
    if ($this->location == 'manufacturer' && $this->isOCFInstalled() && !$this->registry->has('ocfilter')) {
      $link = $this->ocfilter->rewrite($link);
    }

    return $link;
  }
}