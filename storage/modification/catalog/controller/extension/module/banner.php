<?php
class ControllerExtensionModuleBanner extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);


		/** EET Module */
		$ee_position = 1;
		$data['ee_tracking'] = $this->config->get('module_ee_tracking_status');
		if ($data['ee_tracking'] && $results) {
			$data['ee_promotion'] = $this->config->get('module_ee_tracking_promotion_status');
			$data['ee_promotion_log'] = $this->config->get('module_ee_tracking_log') ? $this->config->get('module_ee_tracking_promotion_log') : false;
			$data['ee_ga_callback'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_ga_callback') : 0;
			$data['ee_generate_cid'] = $this->config->get('module_ee_tracking_advanced_settings') ? $this->config->get('module_ee_tracking_generate_cid') : 0;
			$data['ee_data'] = json_encode(array('banner_id' => $setting['banner_id']));
		}
		/** EET Module */
            
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$data['banners'][] = array(
					'ee_banner_id' => $result['banner_id'],
					'ee_position' => isset($ee_position) ? $ee_position++ : '',
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/banner', $data);
	}
}