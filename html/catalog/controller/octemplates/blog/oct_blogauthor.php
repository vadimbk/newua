<?php
/**********************************************************/
/*	@copyright	OCTemplates 2015-2019.					  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**********************************************************/

class ControllerOCTemplatesBlogOCTBlogAuthor extends Controller {
	private $error = [];

	public function index() {
		if (!$this->config->get('oct_blogsettings_status')) {
			$this->response->redirect($this->url->link('common/home', '', true));
		}

		$oct_blogsettings_data = $this->config->get('oct_blogsettings_data');

		$this->load->language('octemplates/blog/oct_blogauthor');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_blog_home'),
			'href' => $this->url->link('octemplates/blog/oct_bloglatest')
		);

		$this->load->model('octemplates/blog/oct_blogcategory');

		

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

		if (isset($this->request->get['blogauthor_id'])) {
			$blogauthor_id = (int)$this->request->get['blogauthor_id'];
		} else {
			$blogauthor_id = 0;
		}

		$this->load->model('octemplates/blog/oct_blogauthor');

		$author_info = $this->model_octemplates_blog_oct_blogauthor->getAuthor($blogauthor_id);

		if ($author_info) {
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
				'text' => $author_info['name'],
				'href' => $this->url->link('octemplates/blog/oct_blogauthor', $url . '&blogauthor_id=' . $this->request->get['blogauthor_id'])
			];

			$this->document->setTitle($author_info['meta_title']);
			$this->document->setDescription($author_info['meta_description']);
			$this->document->setKeywords($author_info['meta_keyword']);
			$this->document->addLink($this->url->link('octemplates/blog/oct_blogauthor', 'blogauthor_id=' . $this->request->get['blogauthor_id']), 'canonical');

			$data['author_name'] = $author_info['name'];
			
			$data['author_url'] = $this->url->link('octemplates/blog/oct_blogauthor', $url . '&blogauthor_id=' . $this->request->get['blogauthor_id']);

			//$this->load->model('catalog/review');

			$data['blogauthor_id'] = (int)$this->request->get['blogauthor_id'];
			$data['description'] = html_entity_decode($author_info['description'], ENT_QUOTES, 'UTF-8');
			$data['shot_description'] = html_entity_decode($author_info['shot_description'], ENT_QUOTES, 'UTF-8');
			$data['date_added'] = date($this->language->get('datetime_format_blog'), strtotime($author_info['date_added']));
			$data['youtube'] = $author_info['youtube'];
			$data['instagram'] = $author_info['instagram'];
			$data['facebook'] = $author_info['facebook'];
			$data['linkedin'] = $author_info['linkedin'];

			$this->load->model('tool/image');

			if ($author_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($author_info['image'], 300, 300);
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = [];

			$results = $this->model_octemplates_blog_oct_blogauthor->getAuthorImages($this->request->get['blogauthor_id']);

			foreach ($results as $result) {
				$data['images'][] = [
					'thumb' => $this->model_tool_image->resize($result['image'], 300, 300),
					'popup' => $this->model_tool_image->resize($result['image'], 300, 300)
				];
			}

			if (!empty($data['images'])) {
				$this->document->addScript('catalog/view/theme/oct_ultrastore/js/fancybox/jquery.fancybox.min.js');
				$this->document->addStyle('catalog/view/theme/oct_ultrastore/js/fancybox/jquery.fancybox.min.css');
			}
			
			$data['review_status'] = false;
			
			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['articles'] = [];

			$artusle_results = $this->model_octemplates_blog_oct_blogauthor->getAuthorRelated($this->request->get['blogauthor_id']);
			

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

			$data['products'] = [];

			$product_results = $this->model_octemplates_blog_oct_blogauthor->getAuthorRelatedProduct($this->request->get['blogauthor_id']);

			foreach ($product_results as $result) {
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

				if ((float)$result['special']) {
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

				$data['products'][] = [
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				];
			}

			$data['tags'] = [];

			if ($author_info['tag']) {
				$tags = explode(',', $author_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = [
						'tag'  => trim($tag),
						'href' => $this->url->link('octemplates/blog/oct_blogsearch', 'tag=' . trim($tag))
					];
				}
			}

			$this->model_octemplates_blog_oct_blogauthor->updateViewed($this->request->get['blogauthor_id']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('octemplates/blog/oct_blogauthor', $data));
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
				'href' => $this->url->link('octemplates/blog/oct_blogauthor', $url . '&blogauthor_id=' . $blogauthor_id)
			];

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}



	public function write() {
		$this->load->language('octemplates/blog/oct_blogauthor');

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

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}