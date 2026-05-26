<?php
class ControllerExtensionModuleReviews extends Controller {
    public function index($setting) {
        $this->language->load('extension/module/reviews');

        $this->load->model('catalog/product');

			$data['position'] = isset($setting['position']) ? $setting['position'] : '';
			
		$this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/css/owl.carousel.min.css');
		//$this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/css/owl.carousel.tabs.css');
		$this->document->addScript('catalog/view/javascript/jquery/owl-carousel/js/owl.carousel.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.theme.default.css');

        $this->load->model('tool/image');

        $this->load->model('catalog/reviews');
        $this->load->model('catalog/category');
        
        $data['module'] = 'reviews';

        $data['module_header'] = $setting['module_header'][$this->config->get('config_language_id')];

        $data['reviews'] = array();

        $limit = $setting['limit'] > 0 ? $setting['limit'] : 4;

        $text_limit = $setting['text_limit'] > 0 ? $setting['text_limit'] : 60;

        //if ($setting['category_sensitive'] && !empty($this->request->get['path'])){
            $categories = explode('_', $this->request->get['path']);
            $category_id = (int) array_pop($categories);
       // } else {
           // $category_id = 0;
       // }
$category_info = $this->model_catalog_category->getCategory($category_id);
$data['category_name'] = $category_info['name'];
       // if ($setting['order_type'] == 'last') {
            //$results = $this->model_catalog_reviews->getLatestReviews($limit, $category_id);
       // } else {
            $results = $this->model_catalog_reviews->getRandomReviews($limit, $category_id);
      //  }

        foreach ($results as $result) {
            if ($this->config->get('config_review_status')) {
                $rating = $result['rating'];
            } else {
                $rating = false;
            }

            $product_id = false;
            $product = false;
            $prod_thumb = false;
            $prod_name = false;
            $prod_model = false;
            $prod_href = false;

            if ($result['product_id']) {
                $product = $this->model_catalog_product->getProduct($result['product_id']);
                if ($product['image']) {
                    $prod_thumb = $this->model_tool_image->resize($product['image'], $setting['width'], $setting['height']);
                }
				else {
					$prod_thumb = $this->model_tool_image->resize('no_image.jpg', $setting['width'], $setting['height']);
				}
                $product_id = $product['product_id'];
                $prod_name = $product['name'];
                $prod_model = $product['model'];
                $prod_href = $this->url->link('product/product', 'product_id=' . $product['product_id'],true);
            }

            $data['reviews'][] = array(
                'review_id'   => $result['review_id'],
                'rating'      => $rating,
                'description' => mb_substr($result['text'], 0, $text_limit,'utf-8') . ' ...',
                'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id'],true),
                'author'      => $result['author'],
                'product_id'  => $product_id,
                'prod_thumb'  => $prod_thumb,
                'prod_name'   => $prod_name,
                'prod_model'  => $prod_model,
                'prod_href'   => $prod_href
            );
        }

        $data['link_all_reviews'] = $this->url->link('product/reviews');
        $data['text_all_reviews'] = $this->language->get('text_all_reviews');
        $data['show_all_button']  = 1;

        
		return $this->load->view('extension/module/reviews_s', $data);

    }
}