<?php
/**********************************************************/
/*	@copyright	OCTemplates 2015-2019.					  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**********************************************************/

class ControllerOCTemplatesBlogOCTBlogArticle extends Controller {
	private $error = [];

	public function index() {
		if (!$this->config->get('oct_blogsettings_status')) {
			$this->response->redirect($this->url->link('common/home', '', true));
		}

		$oct_blogsettings_data = $this->config->get('oct_blogsettings_data');

		$this->load->language('octemplates/blog/oct_blogarticle');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		/*$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_blog_home'),
			'href' => $this->url->link('octemplates/blog/oct_bloglatest')
		);*/


$lang = $this->session->data['language']; // 

if ($lang == 'uk-ua') {
    $seo_lang = 'uk';
} else {
    $seo_lang = ''; // 
}

$blog_url = $seo_lang ? '/' . $seo_lang . '/blog' : '/blog';

$data['breadcrumbs'][] = array(
    'text' => $this->language->get('text_blog_home'),
    'href' => $blog_url
);

		$this->load->model('octemplates/blog/oct_blogcategory');

		if (isset($this->request->get['blog_path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['blog_path']);

			$blogcategory_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_octemplates_blog_oct_blogcategory->getBlogCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = [
						'text' => $category_info['name'],
						'href' => $this->url->link('octemplates/blog/oct_blogcategory', 'blog_path=' . $path)
					];
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_octemplates_blog_oct_blogcategory->getBlogCategory($blogcategory_id);

			if ($category_info) {
				$url = '';

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

				$data['breadcrumbs'][] = [
					'text' => $category_info['name'],
					'href' => $this->url->link('octemplates/blog/oct_blogcategory', 'blog_path=' . $this->request->get['blog_path'] . $url)
				];
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			];
		}

		if (isset($this->request->get['blogarticle_id'])) {
			$blogarticle_id = (int)$this->request->get['blogarticle_id'];
		} else {
			$blogarticle_id = 0;
		}
		
		$data['canonical'] = $this->url->link('octemplates/blog/oct_blogarticle', 'blogarticle_id=' . $this->request->get['blogarticle_id']);

		$this->load->model('octemplates/blog/oct_blogarticle');

		$article_info = $this->model_octemplates_blog_oct_blogarticle->getArticle($blogarticle_id);

		if ($article_info) {
			$url = '';

			if (isset($this->request->get['blog_path'])) {
				$url .= '&blog_path=' . $this->request->get['blog_path'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text' => $article_info['name'],
				'href' => $this->url->link('octemplates/blog/oct_blogarticle', $url . '&blogarticle_id=' . $this->request->get['blogarticle_id'])
			];

			$this->document->setTitle($article_info['meta_title']);
			$this->document->setDescription($article_info['meta_description']);
			$this->document->setKeywords($article_info['meta_keyword']);
			$this->document->addLink($this->url->link('octemplates/blog/oct_blogarticle', 'blogarticle_id=' . $this->request->get['blogarticle_id']), 'canonical');

			$data['heading_title'] = $article_info['name'];

			$this->load->model('catalog/review');

			$data['blogarticle_id'] = (int)$this->request->get['blogarticle_id'];
			$data['description'] = html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8');
			$data['date_added'] = date($this->language->get('datetime_format_blog'), strtotime($article_info['date_added']));
			
			/*author*/
			$author_info = $this->model_octemplates_blog_oct_blogarticle->getArticleAuthor($blogarticle_id);
			$data['author'] = $author_info['author'];
			$data['blogauthor_id'] = $author_info['blogauthor_id'];
			$data['blogauthor_url'] = $this->model_octemplates_blog_oct_blogarticle->getAuthorSeoUrls($data['blogauthor_id']);
			$data['lang'] = $this->language->get('code');
			//echo $data['lang'];
			if ($data['lang']=='uk'){
				$data['blogauthor_url'] = '/uk/' . $data['blogauthor_url'];
			}
			/*author*/
			$this->load->model('tool/image');

			if ($article_info['image'] && (isset($oct_blogsettings_data['show_main_image']) && $oct_blogsettings_data['show_main_image'])) {
				$data['thumb'] = $this->model_tool_image->resize($article_info['image'], $oct_blogsettings_data['article_width'], $oct_blogsettings_data['article_height'], 'w');
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = [];

			$results = $this->model_octemplates_blog_oct_blogarticle->getArticleImages($this->request->get['blogarticle_id']);

			foreach ($results as $result) {
				$data['images'][] = [
					'thumb' => $this->model_tool_image->resize($result['image'], $oct_blogsettings_data['article_dop_width'], $oct_blogsettings_data['article_dop_height']),
					'popup' => $this->model_tool_image->resize($result['image'], $oct_blogsettings_data['article_width'], $oct_blogsettings_data['article_height'])
				];
			}

			if (!empty($data['images'])) {
				$this->document->addScript('catalog/view/theme/oct_ultrastore/js/fancybox/jquery.fancybox.min.js');
				$this->document->addStyle('catalog/view/theme/oct_ultrastore/js/fancybox/jquery.fancybox.min.css');
			}
			
			$data['review_status'] = false;
			
			if (isset($oct_blogsettings_data['comments']) && $oct_blogsettings_data['comments'] == 'on') {
				$review_status = $this->config->get('config_review_status');

				if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
					$review_guest = true;
				} else {
					$review_guest = false;
				}
				
				if ($review_status && $review_guest) {
					$data['review_status'] = true;
				}
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['comments_total'] = sprintf($this->language->get('text_reviews'), (int)$article_info['comments_total']);
			$data['comments_viewed'] = sprintf($this->language->get('text_viewed'), (int)$article_info['viewed']);

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['articles'] = [];

			$artusle_results = $this->model_octemplates_blog_oct_blogarticle->getArticleRelated($this->request->get['blogarticle_id']);

			foreach ($artusle_results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $oct_blogsettings_data['dop_article_width'], $oct_blogsettings_data['dop_article_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $oct_blogsettings_data['dop_article_width'], $oct_blogsettings_data['dop_article_height']);
				}

				$description = !empty(trim(strip_tags($result['shot_description']))) ? $result['shot_description'] : $result['description'];

				$data['articles'][] = [
					'blogarticle_id'		=> $result['blogarticle_id'],
					'thumb'					=> $image,
					'name'					=> $result['name'],
					'description'			=> utf8_substr(trim(strip_tags(html_entity_decode($description, ENT_QUOTES, 'UTF-8'))), 0, $oct_blogsettings_data['description_length']) . '..',
					'date_added'			=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'href'			        => $this->url->link('octemplates/blog/oct_blogarticle', 'blogarticle_id=' . $result['blogarticle_id'])
				];
			}


			$data['oct_popup_view_status'] = $this->config->get('oct_popup_view_status');
			
			$data['products'] = [];

			$product_results = $this->model_octemplates_blog_oct_blogarticle->getArticleRelatedProduct($this->request->get['blogarticle_id']);


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
			

    /* Bulk Specials Editor */
    $this->load->model('extension/module/timer');
    $timer_exist = $this->model_extension_module_timer->checkExistenceExtension('module', 'timer');
    
    $hours_days = ($hours_days = $this->config->get('hours_and_days_settings')) ? $hours_days['module_status'] : false;

    $this->load->language('extension/module/timer');
    $data['text_timer_on_products_page'] = $this->language->get('text_timer_on_products_page');
    
    $timer_settings = $this->config->get('timer_general_settings');
    /* Bulk Specials Editor */
    
			foreach ($product_results as $result) {

			if (isset($oct_stickers) && $oct_stickers) {
				$oct_stickers_data = $this->model_octemplates_stickers_oct_stickers->getOCTStickers($result);
				
				$oct_product_stickers = [];
				
				if ($oct_stickers_data) {
					$oct_product_stickers = $oct_stickers_data['stickers'];
					$data['sticker_colors'][] = $oct_stickers_data['sticker_colors'];
				}
			}
			
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $oct_blogsettings_data['product_width'], $oct_blogsettings_data['product_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $oct_blogsettings_data['product_width'], $oct_blogsettings_data['product_height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				
    /* Bulk Specials Editor */
    $timer = false;

    if ((float)$result['special']) {
      if ($timer_exist) {
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
			
				$data['products'][] = [
					'product_id'  => $result['product_id'],

			'oct_stickers'  => $oct_product_stickers,
			'you_save'	  	=> $result['you_save'],
			
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,

    /* Bulk Specials Editor */
    'special_date_diff'  => $timer == 1 ? $special_date_diff : '',
    'percentage_discount'=> $timer == 1 ? $percentage_discount : '',
    'timer'              => $timer,
    /* Bulk Specials Editor */
    
					'special'     => $special,

					'stock'     => $stock,
					'can_buy'   => $can_buy,
			
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,

			'reviews'	  => $result['reviews'],
			
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				];
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
			

		if ($this->config->get('sp_auto_seo_faq_status')) {
			$this->load->model('extension/module/sp_auto_seo_faq');
			$data['faq_output'] = $this->model_extension_module_sp_auto_seo_faq->getOctBlogArticleFaq($article_info);
		}
		
			$data['tags'] = [];

			if ($article_info['tag']) {
				$tags = explode(',', $article_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = [
						'tag'  => trim($tag),
						'href' => $this->url->link('octemplates/blog/oct_blogsearch', 'tag=' . trim($tag))
					];
				}
			}

			$this->model_octemplates_blog_oct_blogarticle->updateViewed($this->request->get['blogarticle_id']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');

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
			

			$this->response->setOutput($this->load->view('octemplates/blog/oct_blogarticle', $data));
		} else {
			$url = '';

			if (isset($this->request->get['blog_path'])) {
				$url .= '&blog_path=' . $this->request->get['blog_path'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('octemplates/blog/oct_blogarticle', $url . '&blogarticle_id=' . $blogarticle_id)
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');

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

	public function comment() {
		$this->load->language('octemplates/blog/oct_blogarticle');

		$this->load->model('octemplates/blog/oct_blogcomment');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['comments'] = [];

		$review_total = $this->model_octemplates_blog_oct_blogcomment->getTotalCommentsByArticleId($this->request->get['blogarticle_id']);

		$results = $this->model_octemplates_blog_oct_blogcomment->getCommentsByArticleId($this->request->get['blogarticle_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['comments'][] = [
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'admin_text' => nl2br($result['admin_text']),
				'date_added' => date($this->language->get('datetime_format_blog'), strtotime($result['date_added']))
			];
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('octemplates/blog/oct_blogarticle/comment', 'blogarticle_id=' . $this->request->get['blogarticle_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('octemplates/blog/oct_blogcomment', $data));
	}

	public function write() {
		$this->load->language('octemplates/blog/oct_blogarticle');

		$json = [];

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('octemplates/blog/oct_blogcomment');

				$this->model_octemplates_blog_oct_blogcomment->addComment($this->request->get['blogarticle_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}