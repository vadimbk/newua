<?php
class ModelExtensionModuleSPAutoSeoFaq extends Model {
	public function getFaq($id, $type = false) {
		$faq = [];
		if ($type) {
			$products = '';
			if (strpos($type, 'products') !== false) {
				$type = strstr($type, '_', true);
				$products = '_products';
			}
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq sf LEFT JOIN " . DB_PREFIX . "seo_faq_description sfd ON (sf.faq_id = sfd.faq_id AND sfd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE sf." . $type . "_id = '" . (int)$id . "' AND sf.type = '" . $type . $products . "' AND sfd.question <> '' AND sfd.answer <> '' ORDER BY sf.sort_order ASC");
			$faq = $query->rows;
		}
		return $faq;
	}
	
	public function getOcfilterFaq($id) {
		$faq = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq sf LEFT JOIN " . DB_PREFIX . "seo_faq_description sfd ON (sf.faq_id = sfd.faq_id AND sfd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE sf.category_id = '" . (int)$id . "' AND sf.type = 'ocfilter' AND sfd.question <> '' AND sfd.answer <> '' ORDER BY sf.sort_order ASC");
		$faq = $query->rows;
		return $faq;
	}
	
	public function getOctCategoryFaq($id) {
		$faq = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq sf LEFT JOIN " . DB_PREFIX . "seo_faq_description sfd ON (sf.faq_id = sfd.faq_id AND sfd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE sf.category_id = '" . (int)$id . "' AND sf.type = 'oct_blog_category' AND sfd.question <> '' AND sfd.answer <> '' ORDER BY sf.sort_order ASC");
		$faq = $query->rows;
		return $faq; 
	}
	
	public function getOctArticleFaq($id) {
		$faq = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq sf LEFT JOIN " . DB_PREFIX . "seo_faq_description sfd ON (sf.faq_id = sfd.faq_id AND sfd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE sf.category_id = '" . (int)$id . "' AND sf.type = 'oct_blog_article' AND sfd.question <> '' AND sfd.answer <> '' ORDER BY sf.sort_order ASC");
		$faq = $query->rows;
		return $faq; 
	}
	
	public function getBlogCategoryFaq($id) {
		$faq = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq sf LEFT JOIN " . DB_PREFIX . "seo_faq_description sfd ON (sf.faq_id = sfd.faq_id AND sfd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE sf.category_id = '" . (int)$id . "' AND sf.type = 'blog_category' AND sfd.question <> '' AND sfd.answer <> '' ORDER BY sf.sort_order ASC");
		$faq = $query->rows;
		return $faq;
	}
	
	public function getBlogArticleFaq($id) {
		$faq = [];
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_faq sf LEFT JOIN " . DB_PREFIX . "seo_faq_description sfd ON (sf.faq_id = sfd.faq_id AND sfd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE sf.category_id = '" . (int)$id . "' AND sf.type = 'blog_article' AND sfd.question <> '' AND sfd.answer <> '' ORDER BY sf.sort_order ASC");
		$faq = $query->rows;
		return $faq;
	}
	
	public function getHomeFaq() {
		$data['faq'] = [];
		$replace_from = $replace_to = [];
		$home_faq = $this->config->get('sp_auto_seo_faq_home_faq');
		$all_faq = [];
		if (!empty($home_faq)) {
			foreach ($home_faq as $faq_all) {
				if (!empty($faq_all['faq_data'][$this->config->get('config_language_id')])) {
					$all_faq[] = [
						'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['question'])),
						'answer' => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['answer'])),
						'sort_order' => $faq_all['sort_order']
					];
				} 
			}
			usort($all_faq, function ($a, $b) { return $a['sort_order'] == $b['sort_order'] ? 0 : ($a['sort_order'] < $b['sort_order'] ? -1 : 1); });
			$data['faq'] = array_merge($data['faq'], $all_faq);
		}
		$faq_data['faq_title'] = $this->config->get('sp_auto_seo_faq_home_title')[$this->config->get('config_language_id')];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getInformationFaq($information_info = [], $data = []) {
		if (empty($information_info) || empty($data)) return '';
		$data['faq'] = [];
	
		$replace_from = [
			'{information_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$information_info['title'],
			$information_info['meta_title'],
			$data['heading_title'],
			date('m'),
			date('Y')
		];
		
		$information_faq = $this->config->get('sp_auto_seo_faq_information_faq');
		$all_faq = [];
		if (!empty($information_faq)) {
			foreach ($information_faq as $faq_all) {
				if (!empty($faq_all['faq_data'][$this->config->get('config_language_id')])) {
					$all_faq[] = [
						'question'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['question'])),
						'answer'     => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['answer'])),
						'sort_order' => $faq_all['sort_order']
					];
				} 
			}
			usort($all_faq, function ($a, $b) { return $a['sort_order'] == $b['sort_order'] ? 0 : ($a['sort_order'] < $b['sort_order'] ? -1 : 1); });
			$data['faq'] = array_merge($data['faq'], $all_faq);
		}
		
		$data['faq_title'] = str_replace($replace_from, $replace_to, $this->config->get('sp_auto_seo_faq_information_title')[$this->config->get('config_language_id')]);
		$faq_results = $this->getFaq($information_info['information_id'], 'information');
		if ($faq_results) {
			foreach ($faq_results as $faq_result) {
				$data['faq'][] = [
					'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
					'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
				];
			}
		}
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getOcstoreBlogCategoryFaq($category_info = [], $data = []) {
		if (empty($category_info) || empty($data)) return '';
		$data['faq'] = [];
		$this->load->language('extension/module/sp_auto_seo_faq');
		
		$replace_from = [
			'{category_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$category_info['name'],
			$category_info['meta_title'],
			$data['heading_title'],
			date('m'),
			date('Y')
		];
		
		$data['faq_title'] = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_blog_category_title')));

		$faq_results = $this->getBlogCategoryFaq($category_info['blog_category_id']);
		if ($faq_results) {
			foreach ($faq_results as $faq_result) {
				$data['faq'][] = [
					'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
					'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
				];
			}
		}
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getOcstoreBlogArticleFaq($article_info = [], $data = []) {
		if (empty($article_info) || empty($data)) return '';
		$data['faq'] = [];
		$this->load->language('extension/module/sp_auto_seo_faq');
		
		$replace_from = [
			'{article_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$article_info['name'],
			$article_info['meta_title'],
			$data['heading_title'],
			date('m'),
			date('Y')
		];
		
		$data['faq_title'] = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_blog_article_title')));

		$faq_results = $this->getBlogArticleFaq($article_info['article_id']);
		if ($faq_results) {
			foreach ($faq_results as $faq_result) {
				$data['faq'][] = [
					'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
					'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
				];
			}
		}
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
		
	public function getOctBlogCategoryFaq($blog_category_info, $blogcategory_id) {
		$data['faq'] = [];
		$this->load->language('extension/module/sp_auto_seo_faq');
	
		$replace_from = [
			'{blog_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$blog_category_info['name'],
			$blog_category_info['meta_title'],
			$blog_category_info['meta_h1'],
			date('m'),
			date('Y')
		];
		
		$data['faq_title'] = str_replace($replace_from, $replace_to, $this->language->get('blog_category_question'));
		
		$this->load->model('extension/module/sp_auto_seo_faq');
		$faq_results = $this->getOctCategoryFaq($blog_category_info['blogcategory_id']);
		if ($faq_results) {
			foreach ($faq_results as $faq_result) {
				$data['faq'][] = [
					'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
					'answer' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
				];	
			}
		}
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getOctBlogArticleFaq($article_info) {
		$data['faq'] = [];
		$this->load->language('extension/module/sp_auto_seo_faq');
	
		$replace_from = [
			'{blog_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$article_info['name'],
			$article_info['meta_title'],
			$article_info['name'],
			date('m'),
			date('Y')
		];
		
		$data['faq_title'] = str_replace($replace_from, $replace_to, $this->language->get('blog_article_question'));
		
		$this->load->model('extension/module/sp_auto_seo_faq');
		$faq_results = $this->getOctArticleFaq($article_info['blogarticle_id']);
		if ($faq_results) {
			foreach ($faq_results as $faq_result) {
				$data['faq'][] = [
					'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
					'answer' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
				];	
			}
		}
		
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}

	public function getManufacturerFaq($manufacturer_info = [], $data = [], $page = 1) {
		if (empty($manufacturer_info) || empty($data)) return '';
		$data['faq'] = [];
		
		$filter_vier_check = (isset($this->request->get['manufs']) || isset($this->request->get['attrb']) || isset($this->request->get['optv']) || isset($this->request->get['qnts']) || isset($this->request->get['nows']) || isset($this->request->get['psp']) || isset($this->request->get['prs']));
		$ocfilter_check = isset($this->request->get['filter_ocfilter']) || isset($this->request->get['ocf']) || isset($this->request->get['ocfilter_page_id']);
		$mfp_check = isset($this->request->get['mfp']);
		$all_filter_check = !$filter_vier_check && !$ocfilter_check && !$mfp_check;
		$faq_cache_status = $this->config->get('sp_auto_seo_faq_cache_status');
		$first_page = ($page == 1 || $this->config->get('sp_auto_seo_faq_not_first_page'));
		
		$replace_from = [
			'{manufacturer_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$manufacturer_info['name'],
			!empty($manufacturer_info['meta_title']) ? $manufacturer_info['meta_title'] : '',
			$data['heading_title'],
			date('m'),
			date('Y')
		];

		$data['faq_title'] = str_replace($replace_from, $replace_to, $this->config->get('sp_auto_seo_faq_manufacturer_title')[$this->config->get('config_language_id')]);

		if ($first_page && $all_filter_check) {
			$this->load->model('catalog/product');
			$this->load->language('extension/module/sp_auto_seo_faq');
			
			$faq_cache = false;
			if ($faq_cache_status) {
				$faq_cache = $this->cache->get('faq.man.' . $manufacturer_info['manufacturer_id']);
			}
			if ($faq_cache && !empty($faq_cache[$this->config->get('config_language_id')])) {
				$data['faq'] = $faq_cache[$this->config->get('config_language_id')];
			} else {
				$faq_results = $this->getFaq($manufacturer_info['manufacturer_id'], 'manufacturer');
				if ($faq_results) {
					foreach ($faq_results as $faq_result) {
						$data['faq'][] = [
							'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
							'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
						];	
					} 
				}
				$manufacturer_faq = $this->config->get('sp_auto_seo_faq_manufacturer_faq');
				$all_faq = [];
				if (!empty($manufacturer_faq)) {
					foreach ($manufacturer_faq as $faq_all) {
						if (!empty($faq_all['faq_data'][$this->config->get('config_language_id')])) {
							$all_faq[] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['question'])),
								'answer' => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['answer'])),
								'sort_order' => $faq_all['sort_order']
							];
						} 
					}
					usort($all_faq, function ($a, $b) { return $a['sort_order'] == $b['sort_order'] ? 0 : ($a['sort_order'] < $b['sort_order'] ? -1 : 1); });
					$data['faq'] = array_merge($data['faq'], $all_faq);
				}
				if ($this->config->get('sp_auto_seo_faq_m_latest_status') && !empty($this->config->get('sp_auto_seo_faq_m_latest_title')[$this->config->get('config_language_id')])) {
					$latest_products = [];
					$filter_data = [
						'filter_manufacturer_id' => $manufacturer_info['manufacturer_id'],
						'sort'       => 'p.date_added',
						'order'      => 'DESC',
						'start'      => 0,
						'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
						'limit'      => $this->config->get('sp_auto_seo_faq_m_latest_limit') ? $this->config->get('sp_auto_seo_faq_m_latest_limit') : 10 
					];
					$faq_results = $this->model_catalog_product->getProducts($filter_data);
					if ($faq_results) {
						foreach ($faq_results as $faq_result) {
							$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							if ((float)$faq_result['special']) {
								$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}
							$latest_products[] = [
								'name'  => $faq_result['name'],
								'price' => $special ? $special : $price,
								'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
							];
						}
					
						if ($latest_products) {
							//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_latest_m_products')));
							$faq = '';
							$faq .= '<ul>';
							foreach ($latest_products as $product){
								$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
							}
							$faq .= '</ul>';
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_latest_title')[$this->config->get('config_language_id')])),
								'answer'   => html_entity_decode($faq)
							];
						}
					}
				}
				
				if ($this->config->get('sp_auto_seo_faq_m_special_status') && !empty($this->config->get('sp_auto_seo_faq_m_special_title')[$this->config->get('config_language_id')])) {
					$special_products = [];
					$filter_data = [
						'filter_manufacturer_id' => $manufacturer_info['manufacturer_id'],
						'sort'         => 'p.date_added',
						'order'        => 'DESC',
						'start'        => 0,
						'only_stock'   => $this->config->get('sp_auto_seo_faq_only_stock'),
						'only_special' => 1,
						'limit'        => $this->config->get('sp_auto_seo_faq_m_special_limit') ? $this->config->get('sp_auto_seo_faq_m_special_limit') : 10
					];
					$faq_results = $this->model_catalog_product->getProducts($filter_data);
					if ($faq_results) {
						foreach ($faq_results as $faq_result) {
							$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							if ((float)$faq_result['special']) {
								$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}
							$special_products[] = [
								'name'   => $faq_result['name'],
								'price'  => $price,
								'special'  => $special,
								'href'   => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
							];
						}
					
						if ($special_products) {
							//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_special_m_products')));
							$faq = '';
							$faq .= '<ul>';
							foreach ($special_products as $product){
								$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['special'] . '/<span style="text-decoration:line-through">' . $product['price'] . '</span></a></li>';
							}
							$faq .= '</ul>';
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_special_title')[$this->config->get('config_language_id')])),
								'answer'   => html_entity_decode($faq)
							];
						}
					}
				}
				
				if ($this->config->get('sp_auto_seo_faq_m_bestseller_status') && !empty($this->config->get('sp_auto_seo_faq_m_bestseller_title')[$this->config->get('config_language_id')])) {
					$bestseller_products = [];
					$faq_results = $this->getBestProductsFromManufacturer($manufacturer_info['manufacturer_id'], $this->config->get('sp_auto_seo_faq_m_bestseller_limit'));
					if ($faq_results) {
						foreach ($faq_results as $faq_result) {
							$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							if ((float)$faq_result['special']) {
								$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}
							$bestseller_products[] = [
								'name'  => $faq_result['name'],
								'price' => $special ? $special : $price,
								'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
							];
						} 
						if ($bestseller_products) {
							//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_bestseller_m_products')));
							$faq = '';
							$faq .= '<ul>';
							foreach ($bestseller_products as $product){
								$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
							}
							$faq .= '</ul>';
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_bestseller_title')[$this->config->get('config_language_id')])),
								'answer'   => html_entity_decode($faq)
							];
						}
					}
				}
				
				if ($this->config->get('sp_auto_seo_faq_m_min_price_status') && !empty($this->config->get('sp_auto_seo_faq_m_min_price_title')[$this->config->get('config_language_id')])) {
					$min_price_products = [];
					$filter_data = [
						'filter_manufacturer_id' => $manufacturer_info['manufacturer_id'],
						'sort'       => 'p.price',
						'order'      => 'ASC',
						'start'      => 0,
						'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
						'limit'      => $this->config->get('sp_auto_seo_faq_m_min_price_limit') ? $this->config->get('sp_auto_seo_faq_m_min_price_limit') : 10
					];
					$faq_results = $this->model_catalog_product->getProducts($filter_data);;
					if ($faq_results) {
						foreach ($faq_results as $faq_result) {
							$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							if ((float)$faq_result['special']) {
								$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}
							$min_price_products[] = [
								'name'   => $faq_result['name'],
								'price'  => $special ? $special : $price,
								'href'   => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
							];
						}
						if ($min_price_products) {
							//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_min_price_m_products')));
							$faq = '';
							$faq .= '<ul>';
							foreach ($min_price_products as $product){
								$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
							}
							$faq .= '</ul>';
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_min_price_title')[$this->config->get('config_language_id')])),
								'answer'   => html_entity_decode($faq)
							];
						}
					}
				}
				
				if ($this->config->get('sp_auto_seo_faq_m_max_price_status') && !empty($this->config->get('sp_auto_seo_faq_m_max_price_title')[$this->config->get('config_language_id')])) {
					$max_price_products = [];
					$filter_data = [
						'filter_manufacturer_id' => $manufacturer_info['manufacturer_id'],
						'sort'       => 'p.price',
						'order'      => 'DESC',
						'start'      => 0,
						'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
						'limit'      => $this->config->get('sp_auto_seo_faq_m_max_price_limit') ? $this->config->get('sp_auto_seo_faq_m_max_price_limit') : 10
					];
					$faq_results = $this->model_catalog_product->getProducts($filter_data);
					if ($faq_results) {
						foreach ($faq_results as $faq_result) {
							$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							if ((float)$faq_result['special']) {
								$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}
							$max_price_products[] = [
								'name'  => $faq_result['name'],
								'price' => $special ? $special : $price,
								'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
							];
						}
						if ($max_price_products) {
							//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_max_price_m_products')));
							$faq = '';
							$faq .= '<ul>';
							foreach ($max_price_products as $product) {
								$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
							}
							$faq .= '</ul>';
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_max_price_title')[$this->config->get('config_language_id')])),
								'answer'   => html_entity_decode($faq)
							];
						}
					}
				}
		
				if ($this->config->get('sp_auto_seo_faq_m_viewed_status') && !empty($this->config->get('sp_auto_seo_faq_m_viewed_title')[$this->config->get('config_language_id')])) {
					$viewed_products = [];
					$filter_data = [
						'filter_manufacturer_id' => $manufacturer_info['manufacturer_id'],
						'sort'       => 'p.viewed',
						'order'      => 'DESC',
						'start'      => 0,
						'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
						'limit'      => $this->config->get('sp_auto_seo_faq_m_viewed_limit') ? $this->config->get('sp_auto_seo_faq_m_viewed_limit') : 10
					];
					$faq_results = $this->model_catalog_product->getProducts($filter_data);
					if ($faq_results) {
						foreach ($faq_results as $faq_result) {
							$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							if ((float)$faq_result['special']) {
								$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}
							$viewed_products[] = [
								'name'  => $faq_result['name'],
								'price' => $special ? $special : $price,
								'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
							];
						}
						if ($viewed_products) {
							//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_viewed_m_products')));
							$faq = '';
							$faq .= '<ul>';
							foreach ($viewed_products as $product) {
								$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
							}
							$faq .= '</ul>';
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_viewed_title')[$this->config->get('config_language_id')])),
								'answer'   => html_entity_decode($faq)
							];
						}
					}
				}
				
				if ($this->config->get('sp_auto_seo_faq_m_price_from_to_status') && !empty($this->config->get('sp_auto_seo_faq_m_price_from_to_title')[$this->config->get('config_language_id')])) {
					$min_price = $this->getMinPriceFromManufacturer($manufacturer_info['manufacturer_id']);
					$min_price_text = $this->currency->format($min_price, $this->session->data['currency']);
					$max_price = $this->getMaxPriceFromManufacturer($manufacturer_info['manufacturer_id']);
					$max_price_text = $this->currency->format($max_price, $this->session->data['currency']);
					$avg_price = $this->currency->format(($min_price + $max_price)/2, $this->session->data['currency']);
					$faq = html_entity_decode(str_replace($replace_from, $replace_to, sprintf($this->language->get('text_price_m_from_to'), $avg_price, $min_price_text, $max_price_text)));
					if ($min_price > 0 && $max_price > 0) {
						$data['faq'][] = [
							'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_m_price_from_to_title')[$this->config->get('config_language_id')])),
							'answer' => html_entity_decode($faq),
						];
					}
				}
				
				if ($faq_cache_status) {
					$faq_cache = [];
					$faq_cache[$this->config->get('config_language_id')] = $data['faq'];
					$this->cache->set('faq.man.' . $manufacturer_info['manufacturer_id'], $faq_cache);
				}
			}
			
			if (!empty($this->request->get['ocfilter_page_id'])) {
				$data['faq'] = [];
				$faq_results = $this->getOcfilterFaq($this->request->get['ocfilter_page_id']);
				if ($faq_results) {
					foreach ($faq_results as $faq_result) {
						$data['faq'][] = [
							'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
							'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
						];
					}
				}
			}
		}
		
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getCategoryFaq($category_info = [], $data = [], $page = 1) {
		if (empty($category_info) || empty($data)) return '';
		$data['faq'] = [];
		$filter_vier_check = (isset($this->request->get['manufs']) || isset($this->request->get['attrb']) || isset($this->request->get['optv']) || isset($this->request->get['qnts']) || isset($this->request->get['nows']) || isset($this->request->get['psp']) || isset($this->request->get['prs']));
		$ocfilter_check = isset($this->request->get['filter_ocfilter']) || isset($this->request->get['ocf']) || isset($this->request->get['ocfilter_page_id']);
		$mfp_check = isset($this->request->get['mfp']);
		$all_filter_check = !$filter_vier_check && !$ocfilter_check && !$mfp_check;
		$faq_cache_status = $this->config->get('sp_auto_seo_faq_cache_status');
		$first_page = ($page == 1 || $this->config->get('sp_auto_seo_faq_not_first_page'));
		
		$replace_from = [
			'{category_name}',
			'{meta_title}',
			'{heading_title}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$category_info['name'],
			$category_info['meta_title'],
			$data['heading_title'],
			date('m'),
			date('Y')
		];
		
		$data['faq_title'] = str_replace($replace_from, $replace_to, $this->config->get('sp_auto_seo_faq_category_title')[$this->config->get('config_language_id')]);
		
		if ($first_page) {
			$this->load->model('catalog/product');
			$this->load->language('extension/module/sp_auto_seo_faq');
		
			if ($all_filter_check) {
				$faq_cache = false;
				if ($faq_cache_status) {
					$faq_cache = $this->cache->get('faq.cat.' . $category_info['category_id']);
				}
				if ($faq_cache && !empty($faq_cache[$this->config->get('config_language_id')])) {
					$data['faq'] = $faq_cache[$this->config->get('config_language_id')];
				} else {
					$faq_results = $this->getFaq($category_info['category_id'], 'category');
					if ($faq_results) {
						$current_route = $this->request->server['REQUEST_URI'];
						foreach ($faq_results as $faq_result) {
							if (!empty($faq_result['link']) && $current_route != $faq_result['link']) continue;
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
								'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
							];	
						}
					}
					
					$category_faq = $this->config->get('sp_auto_seo_faq_category_faq');
					$all_faq = [];
					if (!empty($category_faq)) {
						foreach ($category_faq as $faq_all) {
							if (!empty($faq_all['faq_data'][$this->config->get('config_language_id')])) {
								$all_faq[] = [
									'question'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['question'])),
									'answer'     => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['answer'])),
									'sort_order' => $faq_all['sort_order']
								];
							} 
						}
						usort($all_faq, function ($a, $b) { return $a['sort_order'] == $b['sort_order'] ? 0 : ($a['sort_order'] < $b['sort_order'] ? -1 : 1); });
						$data['faq'] = array_merge($data['faq'], $all_faq);
					}

					if ($this->config->get('sp_auto_seo_faq_latest_status') && !empty($this->config->get('sp_auto_seo_faq_latest_title')[$this->config->get('config_language_id')])) {
						$latest_products = [];
						$filter_data = [
							'filter_category_id' => $category_info['category_id'],
							'sort'       => 'p.date_added',
							'order'      => 'DESC',
							'start'      => 0,
							'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
							'limit'      => $this->config->get('sp_auto_seo_faq_latest_limit') ? $this->config->get('sp_auto_seo_faq_latest_limit') : 10
						];
						$faq_results = $this->model_catalog_product->getProducts($filter_data);
						if ($faq_results) {
							foreach ($faq_results as $faq_result) {
								$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								if ((float)$faq_result['special']) {
									$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}
								$latest_products[] = [
									'name'  => $faq_result['name'],
									'price' => $special ? $special : $price,
									'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
								];		 
							}
						
							if ($latest_products) {
								//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_latest_products')));
								$faq = '';
								$faq .= '<ul>';
								foreach ($latest_products as $product){
									$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
								}
								$faq .= '</ul>';
								$data['faq'][] = [
									'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_latest_title')[$this->config->get('config_language_id')])),
									'answer'   => html_entity_decode($faq)
								];
							}
						}
					}
					
					if ($this->config->get('sp_auto_seo_faq_special_status') && !empty($this->config->get('sp_auto_seo_faq_special_title')[$this->config->get('config_language_id')])) {
						$special_products = [];
						$filter_data = [
							'filter_category_id' => $category_info['category_id'],
							'sort'         => 'p.date_added',
							'order'        => 'DESC',
							'start'        => 0,
							'only_stock'   => $this->config->get('sp_auto_seo_faq_only_stock'),
							'only_special' => 1,
							'limit'        => $this->config->get('sp_auto_seo_faq_special_limit') ? $this->config->get('sp_auto_seo_faq_special_limit') : 10
						];
						$faq_results = $this->model_catalog_product->getProducts($filter_data);
						if ($faq_results) {
							foreach ($faq_results as $faq_result) {
								$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								if ((float)$faq_result['special']) {
									$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}
								$special_products[] = [
									'name'    => $faq_result['name'],
									'price'   => $price,
									'special' => $special ,
									'href'    => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
								];
							}

							if ($special_products) {
								//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_special_products')));
								$faq = '';
								$faq .= '<ul>';
								foreach ($special_products as $product){
									$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['special'] . '/<span style="text-decoration:line-through">' . $product['price'] . '</span></a></li>';
								}
								$faq .= '</ul>';
								$data['faq'][] = [
									'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_special_title')[$this->config->get('config_language_id')])),
									'answer'   => html_entity_decode($faq)
								];
							}
						}
					}
				
					if ($this->config->get('sp_auto_seo_faq_bestseller_status') && !empty($this->config->get('sp_auto_seo_faq_bestseller_title')[$this->config->get('config_language_id')])) {
						$bestseller_products = [];
						$faq_results = $this->getBestProductsFromCategory($category_info['category_id'], $this->config->get('sp_auto_seo_faq_bestseller_limit'));
						if ($faq_results) {
							foreach ($faq_results as $faq_result) {
								$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								if ((float)$faq_result['special']) {
									$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}
								$bestseller_products[] = [
									'name'   => $faq_result['name'],
									'price'  => $special ? $special : $price,
									'href'   => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
								];
							} 
							if ($bestseller_products) {
								//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_bestseller_products')));
								$faq = '';
								$faq .= '<ul>';
								foreach ($bestseller_products as $product){
									$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
								}
								$faq .= '</ul>';
								$data['faq'][] = [
									'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_bestseller_title')[$this->config->get('config_language_id')])),
									'answer'   => html_entity_decode($faq)
								];
							}
						}
					}
					
					if ($this->config->get('sp_auto_seo_faq_min_price_status') && !empty($this->config->get('sp_auto_seo_faq_min_price_title')[$this->config->get('config_language_id')])) {
						$min_price_products = [];
						$filter_data = [
							'filter_category_id' => $category_info['category_id'],
							'sort'       => 'p.price',
							'order'      => 'ASC',
							'start'      => 0,
							'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
							'limit'      => $this->config->get('sp_auto_seo_faq_min_price_limit') ? $this->config->get('sp_auto_seo_faq_min_price_limit') : 10
						];
						$faq_results = $this->model_catalog_product->getProducts($filter_data);
						if ($faq_results) {
							foreach ($faq_results as $faq_result) {
								$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								if ((float)$faq_result['special']) {
									$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}
								$min_price_products[] = [
									'name'  => $faq_result['name'],
									'price' => $special ? $special : $price,
									'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
								];
							}
							if ($min_price_products) {
								//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_min_price_products')));
								$faq = '';
								$faq .= '<ul>';
								foreach ($min_price_products as $product){
									$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
								}
								$faq .= '</ul>';
								$data['faq'][] = [
									'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_min_price_title')[$this->config->get('config_language_id')])),
									'answer'   => html_entity_decode($faq)
								];
							}
						}
					}
					
					if ($this->config->get('sp_auto_seo_faq_max_price_status') && !empty($this->config->get('sp_auto_seo_faq_max_price_title')[$this->config->get('config_language_id')])) {
						$max_price_products = [];
						$filter_data = [
							'filter_category_id' => $category_info['category_id'],
							'sort'       => 'p.price',
							'order'      => 'DESC',
							'start'      => 0,
							'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
							'limit'      => $this->config->get('sp_auto_seo_faq_max_price_limit') ? $this->config->get('sp_auto_seo_faq_max_price_limit') : 10
						];
						$faq_results = $this->model_catalog_product->getProducts($filter_data);
						if ($faq_results) {
							foreach ($faq_results as $faq_result) {
								$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								if ((float)$faq_result['special']) {
									$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}
								$max_price_products[] = [
									'name'  => $faq_result['name'],
									'price' => $special ? $special : $price,
									'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
								];		 
							}
							if ($max_price_products) {
								//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_max_price_products')));
								$faq = '';
								$faq .= '<ul>';
								foreach ($max_price_products as $product) {
									$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
								}
								$faq .= '</ul>';
								$data['faq'][] = [
									'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_max_price_title')[$this->config->get('config_language_id')])),
									'answer'   => html_entity_decode($faq)
								];
							}
						}
					}

					if ($this->config->get('sp_auto_seo_faq_viewed_status') && !empty($this->config->get('sp_auto_seo_faq_viewed_title')[$this->config->get('config_language_id')])) {
						$viewed_products = [];
						$filter_data = [
							'filter_category_id' => $category_info['category_id'],
							'sort'       => 'p.viewed',
							'order'      => 'DESC',
							'start'      => 0,
							'only_stock' => $this->config->get('sp_auto_seo_faq_only_stock'),
							'limit'      => $this->config->get('sp_auto_seo_faq_viewed_limit') ? $this->config->get('sp_auto_seo_faq_viewed_limit') : 10
						];
						$faq_results = $this->model_catalog_product->getProducts($filter_data);
						if ($faq_results) {
							foreach ($faq_results as $faq_result) {
								$price = $this->currency->format($this->tax->calculate($faq_result['price'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								if ((float)$faq_result['special']) {
									$special = $this->currency->format($this->tax->calculate($faq_result['special'], $faq_result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}
								$viewed_products[] = [
									'name'  => $faq_result['name'],
									'price' => $special ? $special : $price,
									'href'  => $this->url->link('product/product', 'product_id=' . $faq_result['product_id'])
								];
							}
							if ($viewed_products) {
								//$faq = html_entity_decode(str_replace($replace_from, $replace_to, $this->language->get('text_viewed_products')));
								$faq = '';
								$faq .= '<ul>';
								foreach ($viewed_products as $product) {
									$faq .= '<li><a href="' . $product['href'] . '">' . $product['name'] . ' - ' . $product['price'] . '</a></li>';
								}
								$faq .= '</ul>';
								$data['faq'][] = [
									'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_viewed_title')[$this->config->get('config_language_id')])),
									'answer'   => html_entity_decode($faq)
								];
							}
						}
					}
					
					if ($this->config->get('sp_auto_seo_faq_price_from_to_status') && !empty($this->config->get('sp_auto_seo_faq_price_from_to_title')[$this->config->get('config_language_id')])) {
						$min_price = $this->getMinPriceFromCategory($category_info['category_id']);
						$min_price_text = $this->currency->format($min_price, $this->session->data['currency']);
						$max_price = $this->getMaxPriceFromCategory($category_info['category_id']);
						$max_price_text = $this->currency->format($max_price, $this->session->data['currency']);
						$avg_price = $this->currency->format(($min_price + $max_price)/2, $this->session->data['currency']);
						$faq = html_entity_decode(str_replace($replace_from, $replace_to, sprintf($this->language->get('text_price_from_to'), $avg_price, $min_price_text, $max_price_text)));
						if ($min_price > 0 && $max_price > 0) {
							$data['faq'][] = [
								'question' => str_replace($replace_from, $replace_to, html_entity_decode($this->config->get('sp_auto_seo_faq_price_from_to_title')[$this->config->get('config_language_id')])),
								'answer' => html_entity_decode($faq),
							];	
						}
					}
					
					if ($faq_cache_status) {
						$faq_cache = [];
						$faq_cache[$this->config->get('config_language_id')] = $data['faq'];
						$this->cache->set('faq.cat.' . $category_info['category_id'], $faq_cache);
					}
				}
			}
			
			if (!empty($this->request->get['ocfilter_page_id'])) {
				$data['faq'] = [];
				$faq_results = $this->getOcfilterFaq($this->request->get['ocfilter_page_id']);
				if ($faq_results) {
					foreach ($faq_results as $faq_result) {
						$data['faq'][] = [
							'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
							'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
						];	
					}
				}
			}
		}

		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getProductFaq($product_info = [], $data = [], $category_info = []) {
		if (empty($product_info) || empty($data)) return '';
		
		$data['faq'] = [];
		$replace_from = [
			'{product_name}',
			'{product_price}',
			'{heading_title}',
			'{meta_title}',
			'{manufacturer}',
			'{model}',
			'{sku}',
			'{month}',
			'{year}'
		];
		
		$replace_to = [
			$product_info['name'],
			$this->currency->format($this->tax->calculate($product_info['special'] ? $product_info['special'] : $product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
			$data['heading_title'],
			$product_info['meta_title'],
			$product_info['manufacturer'],
			$product_info['model'],
			$product_info['sku'],
			date('m'),
			date('Y')
		]; 

		$data['faq_title'] = str_replace($replace_from, $replace_to, $this->config->get('sp_auto_seo_faq_product_title')[$this->config->get('config_language_id')]);

		$this->load->language('extension/module/sp_auto_seo_faq');
		
		$faq_results = $this->getFaq($product_info['product_id'], 'product');
		if ($faq_results) {
			foreach ($faq_results as $faq_result) {
				$data['faq'][] = [
					'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
					'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
				];	
			}
		}
		
		$product_faq = $this->config->get('sp_auto_seo_faq_product_faq');
		$all_faq = [];
		if (!empty($product_faq)) {
			foreach ($product_faq as $faq_all) {
				if (!empty($faq_all['faq_data'][$this->config->get('config_language_id')])) {
					$all_faq[] = [
						'question'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['question'])),
						'answer'     => str_replace($replace_from, $replace_to, html_entity_decode($faq_all['faq_data'][$this->config->get('config_language_id')]['answer'])),
						'sort_order' => $faq_all['sort_order']
					];
				} 
			}
			usort($all_faq, function ($a, $b) { return $a['sort_order'] == $b['sort_order'] ? 0 : ($a['sort_order'] < $b['sort_order'] ? -1 : 1); });
			$data['faq'] = array_merge($data['faq'], $all_faq);
		}
		if (!empty($category_info)) {
			$replace_category_from = [
				'{category_name}',
				'{meta_title}',
				'{month}',
				'{year}'
			];
			
			$replace_category_to = [
				$category_info['name'],
				$category_info['meta_title'],
				date('m'),
				date('Y')
			];
			if ($this->config->get('sp_auto_seo_faq_show_in_product')) {
				$faq_results = $this->getFaq($category_info['category_id'], 'category'); 
				if ($faq_results) {
					foreach ($faq_results as $faq_result) {
						$data['faq'][] = [
							'question' => str_replace($replace_category_from, $replace_category_to, html_entity_decode($faq_result['question'])),
							'answer'   => str_replace($replace_category_from, $replace_category_to, html_entity_decode($faq_result['answer']))
						];
					}
				}
			}
			$faq_results = $this->getFaq($category_info['category_id'], 'category_products'); 
			
			if ($faq_results) {
				foreach ($faq_results as $faq_result) {
					$data['faq'][] = [
						'question' => str_replace($replace_category_from, $replace_category_to, html_entity_decode($faq_result['question'])),
						'answer'   => str_replace($replace_category_from, $replace_category_to, html_entity_decode($faq_result['answer']))
					];	
				}
			}
		}
		
		if ($product_info['manufacturer_id']) {
			$faq_results = $this->getFaq($product_info['manufacturer_id'], 'manufacturer_products');
			if ($faq_results) {
				foreach ($faq_results as $faq_result) {
					$data['faq'][] = [
						'question' => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['question'])),
						'answer'   => str_replace($replace_from, $replace_to, html_entity_decode($faq_result['answer']))
					];
				}
			}
		}
		
		$faq_data['faq_title'] = $data['faq_title'];
		$faq_data['faq'] = $data['faq'];
		$data['faq_output'] = $this->load->controller('extension/module/sp_auto_seo_faq', $faq_data);
		return $data['faq_output'];
	}
	
	public function getBestProductsFromCategory($category_id, $limit = 10) {
		$this->load->model('catalog/product');
		$product_data = $this->cache->get('product.best.' . (int)$this->config->get('config_language_id') . '.' . (int)$category_id . '.' . (int)$limit);
		if (!$product_data) {
			$product_data = [];
			$sql = "SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.quantity > 0 AND p.date_available <= NOW() AND p2c.category_id = '" . (int)$category_id . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit;
			$query = $this->db->query($sql);
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
			}
			$this->cache->set('product.best.' . (int)$this->config->get('config_language_id') . '.' . (int)$category_id . '.' . (int)$limit, $product_data);

		}
		return $product_data;
	}
	
	public function getBestProductsFromManufacturer($manufacturer_id, $limit = 10) {
		$this->load->model('catalog/product');
		$product_data = $this->cache->get('product.bestm.' . (int)$this->config->get('config_language_id') . '.' . (int)$manufacturer_id . '.' . (int)$limit);
		if (!$product_data) {
			$product_data = [];
			$sql = "SELECT op.product_id, COUNT(*) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.quantity > 0 AND p.date_available <= NOW() AND p.manufacturer_id = '" . (int)$manufacturer_id . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit;
			$query = $this->db->query($sql);
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
			}
			$this->cache->set('product.bestm.' . (int)$this->config->get('config_language_id') . '.' . (int)$manufacturer_id . '.' . (int)$limit, $product_data);
		}
		return $product_data;
	}
	
	public function getMinPriceFromCategory($category_id) {
		$sql = 'SELECT MIN(' . DB_PREFIX . 'product.price) FROM ' . DB_PREFIX . 'product LEFT JOIN ' . DB_PREFIX . 'product_to_category ON ' . DB_PREFIX . 'product.product_id = ' . DB_PREFIX . 'product_to_category.product_id WHERE ' . DB_PREFIX . 'product_to_category.category_id = ' . (int)$category_id . ' AND status = 1 AND quantity > 0 AND price > 0 AND date_available <= NOW() ';
		$query = $this->db->query($sql);
		$price = $query->row['MIN(' . DB_PREFIX . 'product.price)'];
		if ($price != null) {
			return $price;
		} else {
			return 0;
		}
	}
	
	public function getMaxPriceFromCategory($category_id) {
		$sql = 'SELECT MAX(' . DB_PREFIX . 'product.price) FROM ' . DB_PREFIX . 'product LEFT JOIN ' . DB_PREFIX . 'product_to_category ON ' . DB_PREFIX . 'product.product_id = ' . DB_PREFIX . 'product_to_category.product_id WHERE ' . DB_PREFIX . 'product_to_category.category_id = ' . (int)$category_id . ' AND status = 1 AND quantity > 0 AND date_available <= NOW() ';
		$query = $this->db->query($sql);
		$price = $query->row['MAX(' . DB_PREFIX . 'product.price)'];
		if ($price != null) {
			return $price;
		} else {
			return 0;
		}
	}
	
	public function getMinPriceFromManufacturer($manufacturer_id) {
		$sql = 'SELECT MIN(' . DB_PREFIX . 'product.price) FROM ' . DB_PREFIX . 'product WHERE manufacturer_id = ' . (int)$manufacturer_id . ' AND status = 1 AND quantity > 0 AND price > 0 AND date_available <= NOW() ';
		$query = $this->db->query($sql);
		$price = $query->row['MIN(' . DB_PREFIX . 'product.price)'];
		if ($price != null) {
			return $price;
		} else {
			return 0;
		}
	}
	
	public function getMaxPriceFromManufacturer($manufacturer_id) {
		$sql = 'SELECT MAX(' . DB_PREFIX . 'product.price) FROM ' . DB_PREFIX . 'product WHERE manufacturer_id = ' . (int)$manufacturer_id . ' AND status = 1 AND quantity > 0 AND price > 0 AND date_available <= NOW() ';
		$query = $this->db->query($sql);
		$price = $query->row['MAX(' . DB_PREFIX . 'product.price)'];
		if ($price != null) {
			return $price;
		} else {
			return 0;
		}
	}
}