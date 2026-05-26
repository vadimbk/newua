<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* URL class
*/
class Url {
	private $url;
	private $ssl;

	// SEO langmark vars
	private $lm_registry;
	public $lm_ = true;
	// End of SEO langmark vars
    
	private $rewrite = array();
	
	/**
	 * Constructor
	 *
	 * @param	string	$url
	 * @param	string	$ssl
	 *
 	*/
	public function __construct($url, $ssl = '') {
		// SEO langmark code
			if (!defined('DIR_CATALOG')) {
            	if (is_callable(array($this->lm_registry, 'get'))) {
					$seolang_langmark_settings = $this->lm_registry->get('config')->get('seolang_langmark_settings');
					if (isset($seolang_langmark_settings) && $seolang_langmark_settings && isset($seolang_langmark_settings['langmark_widget_status']) && $seolang_langmark_settings['langmark_widget_status']) {
						if (!is_object($this->model_seolang_seolang)) {
							$this->load->model('seolang/seolang');
						}					
						if (!is_object($this->controller_seolang_mova_mova)) {
							$this->model_seolang_seolang->control('seolang/mova/mova');
						}						
						if (!$this->lm_registry->get('seolangmova')) {
							$this->lm_registry->set('seolangmova', $this->controller_seolang_mova_mova);
							if (SC_VERSION < 20) {
								$this->config->set('seolangmova', $this->controller_seolang_mova_mova);
							}
						} 		           	
					}
				}
				if (is_callable(array($this->lm_registry, 'get')) && $this->lm_registry->get('seolangmova')) {
					$url = $this->lm_registry->get('seolangmova')->after($url);
				}
            } 		
		// End SEO langmark code
		$this->url = $url;
		$this->ssl = $ssl;
	}

	/**
	 *
	 *
	 * @param	object	$rewrite
 	*/	

 	// SEO langmark function
 	public function lm_setRegistry($registry) {
		$this->lm_registry = $registry;
	}
	// End of SEO langmark function
    
	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}

	/**
	 * 
	 *
	 * @param	string		$route
	 * @param	mixed		$args
	 * @param	bool		$secure
	 *
	 * @return	string
 	*/
	public function link($route, $args = '', $secure = false) {
		if ($this->ssl && $secure) {
			$url = $this->ssl . 'index.php?route=' . $route;
		} else {
			$url = $this->url . 'index.php?route=' . $route;
		}
		
		if ($args) {
			if (is_array($args)) {
				$url .= '&amp;' . http_build_query($args);
			} else {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}
		
		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}
		

		// SEO langmark code
		if (!defined('DIR_CATALOG')) {
			if (is_callable(array($this->lm_registry, 'get')) && $this->lm_registry->get('seolangmova')) {
				$url = $this->lm_registry->get('seolangmova')->after($url, $route);
			}
		}
		//End of SEO langmark code
    
		return $url; 
	}
}