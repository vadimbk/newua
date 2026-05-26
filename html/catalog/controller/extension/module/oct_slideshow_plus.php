<?php
/**************************************************************/
/*	@copyright	OCTemplates 2016-2019						  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**************************************************************/

class ControllerExtensionModuleOctSlideshowPlus extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$this->load->model('octemplates/module/oct_slideshow_plus');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/theme/oct_ultrastore/stylesheet/owl.carousel.min.css');
		$this->document->addScript('catalog/view/theme/oct_ultrastore/js/owl.carousel.min.js');
		
		$data['oct_slideshows_plus'] = [];
		
		$results = $this->model_octemplates_module_oct_slideshow_plus->getSlideshow($setting['slideshow_id']);
		
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE.$result['image'])) {

				$data['pag_background']				= $result['pag_background'];
				$data['pag_background_active']		= $result['pag_background_active'];
				$data['status_additional_banners']	= $result['status_additional_banners'];
				$data['position_banners']			= $result['position_banners'];
					
				$data['oct_slideshows_plus'][] = [
					'title'                  => $result['title'],
					'button'                 => $result['button'],
					'link'                   => ($result['link'] == '#' or empty($result['link'])) ? 'javascript:;' : $result['link'],
					'background_color'       => $result['background_color'],
					'title_color'            => $result['title_color'],
					'text_color'             => $result['text_color'],
					'button_color'           => $result['button_color'],
					'button_background'      => $result['button_background'],
					'button_color_hover'     => $result['button_color_hover'],
					'button_background_hover' => $result['button_background_hover'],
					'description'            => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
					'image'                  => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				];
			}
		}

		$result_banners = $this->model_octemplates_module_oct_slideshow_plus->getSlideshowBanners($setting['slideshow_id']);
		
		$data['oct_slideshows_plus_banners'] = [
            'b1_image'						=> $this->model_tool_image->resize($result_banners['b1_image'], $setting['dop_width'], $setting['dop_height']),
            'b1_button_background'			=> $result_banners['b1_button_background'],
            'b1_button_background_hover'	=> $result_banners['b1_button_background_hover'],
            'b1_button_color'				=> $result_banners['b1_button_color'],
            'b1_button_color_hover'			=> $result_banners['b1_button_color_hover'],
            'b1_title_background'			=> $result_banners['b1_title_background'],
            'b1_title_color'				=> $result_banners['b1_title_color'],
            'b2_image'						=> $this->model_tool_image->resize($result_banners['b2_image'], $setting['dop_width'], $setting['dop_height']),
            'b2_button_background'			=> $result_banners['b2_button_background'],
            'b2_button_background_hover'	=> $result_banners['b2_button_background_hover'],
            'b2_button_color'				=> $result_banners['b2_button_color'],
            'b2_button_color_hover'			=> $result_banners['b2_button_color_hover'],
            'b2_title_background'			=> $result_banners['b2_title_background'],
            'b2_title_color'				=> $result_banners['b2_title_color'],
            'b1_banner_title'				=> $result_banners['b1_banner_title'],
            'b1_banner_button'				=> $result_banners['b1_banner_button'],
            'b1_banner_link'				=> ($result_banners['b1_banner_link'] == '#' or empty($result_banners['b1_banner_link'])) ? 'javascript:;' : $result_banners['b1_banner_link'],
            'b2_banner_title'				=> $result_banners['b2_banner_title'],
            'b2_banner_button'				=> $result_banners['b2_banner_button'],
			'b2_banner_link'				=> ($result_banners['b2_banner_link'] == '#' or empty($result_banners['b2_banner_link'])) ? 'javascript:;' : $result_banners['b2_banner_link']
		];
		
		$data['module'] = $module++;
		
		$data['slideshow_id']                         = $setting['slideshow_id'];
		
		return $this->load->view('octemplates/module/oct_slideshow_plus', $data);
	}
}