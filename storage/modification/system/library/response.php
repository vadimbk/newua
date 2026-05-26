<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Response class
*/
class Response {
	private $headers = array();
	private $level = 0;
	private $output;

	//SEO multilang vars
	private $lm_registry;
	public $lm_ = true;
	//End of SEO multilang vars
    

	/**
	 * Constructor
	 *
	 * @param	string	$header
	 *
 	*/

 	public function lm_setRegistry($registry) {
		$this->lm_registry = $registry;
	}
    
	public function addHeader($header) {
		$this->headers[] = $header;
	}
	
	/**
	 * 
	 *
	 * @param	string	$url
	 * @param	int		$status
	 *
 	*/
	public function redirect($url, $status = 302) {
		header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $url), true, $status);
		exit();
	}
	
	/**
	 * 
	 *
	 * @param	int		$level
 	*/
	public function setCompression($level) {
		$this->level = $level;
	}
	
	/**
	 * 
	 *
	 * @return	array
 	*/
	public function getOutput() {
		return $this->output;
	}
	
	/**
	 * 
	 *
	 * @param	string	$output
 	*/	
	public function setOutput($output) {
		$this->output = $output;
	}
	
	/**
	 * 
	 *
	 * @param	string	$data
	 * @param	int		$level
	 * 
	 * @return	string
 	*/
	private function compress($data, $level = 0) {
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)) {
			$encoding = 'gzip';
		}

		if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)) {
			$encoding = 'x-gzip';
		}

		if (!isset($encoding) || ($level < -1 || $level > 9)) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status()) {
			return $data;
		}

		$this->addHeader('Content-Encoding: ' . $encoding);

		return gzencode($data, (int)$level);
	}

public function summernoteReplace() {
  if ($this->output) {
    $this->output = str_replace('view/javascript/summernote/opencart.js','view/javascript/ckeditor/summernote_replacer.js',$this->output);
  }
}
      
	
	/**
	 * 
 	*/
	public function output() {

		// SEO langmark code
		if (!defined('DIR_CATALOG')) {
			if (is_callable(array($this->lm_registry, 'get'))) {
				$seolang_langmark_settings = $this->lm_registry->get('config')->get('seolang_langmark_settings');
				if (isset($seolang_langmark_settings) && $seolang_langmark_settings && $seolang_langmark_settings['langmark_widget_status']) {

					if (!is_object($this->lm_registry->get('model_seolang_seolang'))) {
						$this->lm_registry->get('load')->model('seolang/seolang');
					}

					if (!is_object($this->lm_registry->get('controller_seolang_mova_mova'))) {
						$this->lm_registry->get('model_seolang_seolang')->control('seolang/mova/mova');
					}
					if (!$this->lm_registry->get('seolangmova')) {
						$this->lm_registry->set('seolangmova', $this->lm_registry->get('controller_seolang_mova_mova'));
						if (SC_VERSION < 20) {
							$this->lm_registry->get('config')->set('seolangmova', $this->lm_registry->get('controller_seolang_mova_mova'));
						}
					}
				}
			}
		 
			if (is_callable(array($this->lm_registry, 'get')) && $this->lm_registry->get('seolangmova')) {
				$this->output = $this->lm_registry->get('seolangmova')->responseseolang($this->output);
				unset($this->seolangmova);
			}
		}
		// End SEO langmark code

			if (!defined('HTTP_CATALOG')) $this->output = str_replace('index.php?route=common/home', '', $this->output);

			$oct_output = $this->output;

			preg_match_all("/<style>(.*?)<\/style>/is", $oct_output, $oct_css_code);

			if (isset($oct_css_code[1]) && !empty($oct_css_code[1])) {
				$oct_output = preg_replace("/<style>.*?<\/style>/is", '', $oct_output);

				$styles = "<style>";

				foreach ($oct_css_code[1] as $code) {
					$styles .= $code;
				}

				$styles .= "</style></head>";

				$oct_output = preg_replace("/(<\/head>)/", $styles, $oct_output);
			}

			$this->output = $oct_output;
			
		if ($this->output) {

//ckeditor admin
if ( defined('HTTP_CATALOG') || defined('HTTPS_CATALOG') || defined('OPENCART_SERVER') ) {
  $this->summernoteReplace();
}
      
			$output = $this->level ? $this->compress($this->output, $this->level) : $this->output;
			
			if (!headers_sent()) {
				foreach ($this->headers as $header) {
					header($header, true);
				}
			}
			
			echo $output;
		}
	}
}
