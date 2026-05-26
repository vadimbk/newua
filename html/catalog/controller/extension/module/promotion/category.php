<?php

class ControllerExtensionModulePromotionCategory extends Controller {

	public function index() {

		$this->load->model('extension/module/promotion');

		$options = $this->model_extension_module_promotion->getOptions();

		$data = $this->model_extension_module_promotion->getLanguage();

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $options['promotion_category']['promotions'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_promotions'),
			'href' => $this->url->link('extension/module/promotion/category')
		);

		$this->document->setTitle($this->language->get('text_meta_title'));
		$this->document->setDescription($this->language->get('text_meta_description'));
		$this->document->setKeywords($this->language->get('text_meta_keyword'));

		$data['heading_title'] = $this->language->get('text_promotions');

		$data['promotions'] = array();

		$filter_data = array(
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$results = $this->model_extension_module_promotion->getPromotions($filter_data);

		$promotion_total = $this->model_extension_module_promotion->getTotalPromotions($filter_data);

		foreach ($results as $result) {

			if ($result['image_promotion']) {
				$result['thumb'] = $this->model_extension_module_promotion->getThumb($result['image_promotion'], 'promotion_category');
			} elseif ($result['image'] && !empty($options['promotion']['main_image'])) {
				$result['thumb'] = $this->model_extension_module_promotion->getThumb($result['image'], 'promotion_category');
			} else {
				$result['thumb'] = "";
			}

			$result['href'] = $this->url->link('extension/module/promotion/promotion', 'promotion_id=' . $result['promotion_id']);

			if ($result['finished']) {
				$result['name'] = $this->language->get('promotion_finished');
			}

			$data['promotions'][] = $result;
		}

		$pagination = new Pagination();
		$pagination->total = $promotion_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/module/promotion/category', 'page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($promotion_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($promotion_total - $limit)) ? $promotion_total : ((($page - 1) * $limit) + $limit), $promotion_total, ceil($promotion_total / $limit));

		$data['limit'] = $limit;

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->model_extension_module_promotion->getView('promotion_category', $data));
	}

}
