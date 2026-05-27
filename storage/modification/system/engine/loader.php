<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Loader class
*/
final class Loader {
	protected $registry;

	/**
	 * Constructor
	 *
	 * @param	object	$registry
 	*/
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * 
	 *
	 * @param	string	$route
	 * @param	array	$data
	 *
	 * @return	mixed
 	*/	
	public function controller($route, $data = array()) {
		// SEO multilang code - begin
		if (!defined('DIR_CATALOG')) {
			$lm_data = $this->lm_replacecontroller($route);
			if ($lm_data) {
				return $lm_data;
			}
		}		
		// SEO multilang code - end
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		// Keep the original trigger
		$trigger = $route;
		
		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('controller/' . $trigger . '/before', array(&$route, &$data));
		
		// Make sure its only the last event that returns an output if required.
		if ($result != null && !$result instanceof Exception) {
			$output = $result;
		} else {
			$action = new Action($route);
			$output = $action->execute($this->registry, array(&$data));
		}
		
		// Trigger the post events
		$result = $this->registry->get('event')->trigger('controller/' . $trigger . '/after', array(&$route, &$data, &$output));
		
		if ($result && !$result instanceof Exception) {
			$output = $result;
		}

		if (!$output instanceof Exception) {
			return $output;
		}
	}

	/**
	 * 
	 *
	 * @param	string	$route
 	*/	
	public function model($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		if (!$this->registry->has('model_' . str_replace('/', '_', $route))) {
			$file  = DIR_APPLICATION . 'model/' . $route . '.php';
			$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
			
			if (is_file($file)) {
				include_once(modification($file));
	
				$proxy = new Proxy();
				
				// Overriding models is a little harder so we have to use PHP's magic methods
				// In future version we can use runkit
				foreach (get_class_methods($class) as $method) {
					$proxy->{$method} = $this->callback($this->registry, $route . '/' . $method);
				}
				
				$this->registry->set('model_' . str_replace('/', '_', (string)$route), $proxy);
			} else {
				throw new \Exception('Error: Could not load model ' . $route . '!');
			}
		}
	}

	/**
	 * 
	 *
	 * @param	string	$route
	 * @param	array	$data
	 *
	 * @return	string
 	*/
// SEO multilang code - begin
	public function lm_reset_replace() {
		$this->registry->set('lm_replace_array', array());
	}

	public function lm_route_replace() {
		$this->registry->set('lm_route_array', array());
	}

	public function lm_reset_data() {
		$this->registry->set('lm_data_array', array());
	}

	public function lm_get_data() {
		$data_array_return = $this->registry->get('lm_data_array');
		$this->lm_reset_data();
		return $data_array_return;
	}

	public function lm_set_replacedata($replace_array, $route = 0) {
		$route_array = array();
		foreach ($replace_array as $val => $result) {
			$route_array[$route][$val] = $result;
		}

		if (!is_array($this->registry->get('lm_replace_array'))) $this->lm_reset_replace();
		if (!is_array($this->registry->get('lm_route_array'))) $this->lm_route_replace();

		$this->registry->set('lm_replace_array', array_merge($this->registry->get('lm_replace_array'), $replace_array));
		$this->registry->set('lm_route_array', array_merge($this->registry->get('lm_route_array'), $route_array));
	}

	public function lm_replacedata($data_array, $replace_array, $route) {
		if (!empty($replace_array)) {

			$replace_array_new = array();
			foreach ($this->registry->get('lm_route_array') as $val_route => $result_val) {
				if ($val_route == $route || $val_route === 0) {
					foreach ($result_val as $val_r => $result_r) {
						$replace_array_new[$val_r] = $replace_array[$val_r];
					}
				}
			}

			$data_array = array_replace($data_array, $replace_array_new);

			if (!is_array($this->registry->get('lm_data_array'))) $this->lm_reset_data();
			$this->registry->set('lm_data_array', array_merge($this->registry->get('lm_data_array'), $replace_array));
		}
		return $data_array;
	}

	public function lm_replacecontroller($route) {
		$lm_route_array = $this->registry->get('lm_route_array');
		if ($lm_route_array && !empty($lm_route_array)) {
			foreach ($this->registry->get('lm_route_array') as $val_route => $result_val) {
				if ($val_route == $route || $val_route === 0) {
					foreach ($result_val as $val_r => $result_r) {
						if ($val_route == $route && $route == $val_r && $val_route == $val_r) {
							return $result_r;
						}
					}
				}
			}
		}
		return false;
	}	
// SEO multilang code - end

	public function view($route, $data = array()) {
		
		if (!defined('DIR_CATALOG')) {
			if (!defined('SC_VERSION')) define('SC_VERSION', substr(str_replace('.', '', VERSION), 0, 2));
			$seolang_langmark_settings = $this->registry->get('config')->get('seolang_langmark_settings');
			if (isset($seolang_langmark_settings) && $seolang_langmark_settings && $seolang_langmark_settings['langmark_widget_status']) {
		
				if (!is_object($this->registry->get('model_seolang_seolang'))) {
					$this->registry->get('load')->model('seolang/seolang');
				}
		
				if (!is_object($this->registry->get('controller_seolang_mova_mova'))) {
					$this->registry->get('model_seolang_seolang')->control('seolang/mova/mova');
				}
				if (!$this->registry->get('seolangmova')) {
					$this->registry->set('seolangmova', $this->registry->get('controller_seolang_mova_mova'));
					if (SC_VERSION < 20) {
						$this->registry->get('config')->set('seolangmova', $this->registry->get('controller_seolang_mova_mova'));
					}
				}
			}
		 
			if (is_callable(array($this->registry, 'get')) && $this->registry->get('seolangmova')) {
				$this->registry->get('seolangmova')->loadview();
			}		
			$data = $this->lm_replacedata($data, $this->registry->get('lm_replace_array'), $route);
		}
		// Huck for using .tpl
		$use_tpl = false;
		if (strpos($route, '.tpl')) {
			$use_tpl = true;
			$route = str_replace('.tpl', '', $route);
		}

		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		// Keep the original trigger
		$trigger = $route;
		
		// Template contents. Not the output!
		$template = '';
		
		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('view/' . $trigger . '/before', array(&$route, &$data, &$template));
		
		// Make sure its only the last event that returns an output if required.
		if ($result && !$result instanceof Exception) {
			$output = $result;
		} else {
			//$template = new Template($this->registry->get('config')->get('template_engine'));
			
			if ($use_tpl) {
				$template = new Template('template');
			} else {
				$template = new Template($this->registry->get('config')->get('template_engine'));
			}
				
			foreach ($data as $key => $value) {
				$template->set($key, $value);
			}

			//$output = $template->render($this->registry->get('config')->get('template_directory') . $route, $this->registry->get('config')->get('template_cache'));		
			
			if ($use_tpl) {
				$output = $template->render($this->registry->get('config')->get('template_directory') . $route);		
			} else {
				$output = $template->render($this->registry->get('config')->get('template_directory') . $route, $this->registry->get('config')->get('template_cache'));		
			}		
		}
		
		// Trigger the post events
		$result = $this->registry->get('event')->trigger('view/' . $trigger . '/after', array(&$route, &$data, &$output));
		
		if ($result && !$result instanceof Exception) {
			$output = $result;
		}
		
		return $output;
	}

	/**
	 * 
	 *
	 * @param	string	$route
 	*/
	public function library($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
			
		$file = DIR_SYSTEM . 'library/' . $route . '.php';
		$class = str_replace('/', '\\', $route);

		if (is_file($file)) {
			include_once(modification($file));

			$this->registry->set(basename($route), new $class($this->registry));
		} else {
			throw new \Exception('Error: Could not load library ' . $route . '!');
		}
	}

	/**
	 * 
	 *
	 * @param	string	$route
 	*/	
	public function helper($route) {
		$file = DIR_SYSTEM . 'helper/' . preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route) . '.php';

		if (is_file($file)) {
			include_once(modification($file));
		} else {
			throw new \Exception('Error: Could not load helper ' . $route . '!');
		}
	}

	/**
	 * 
	 *
	 * @param	string	$route
 	*/	
	public function config($route) {
		$this->registry->get('event')->trigger('config/' . $route . '/before', array(&$route));
		
		$this->registry->get('config')->load($route);
		
		$this->registry->get('event')->trigger('config/' . $route . '/after', array(&$route));
	}

	/**
	 * 
	 *
	 * @param	string	$route
	 * @param	string	$key
	 *
	 * @return	array
 	*/
	public function language($route, $key = '') {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		// Keep the original trigger
		$trigger = $route;
				
		$result = $this->registry->get('event')->trigger('language/' . $trigger . '/before', array(&$route, &$key));
		
		if ($result && !$result instanceof Exception) {
			$output = $result;
		} else {
			$output = $this->registry->get('language')->load($route, $key);
		}
		
		$result = $this->registry->get('event')->trigger('language/' . $trigger . '/after', array(&$route, &$key, &$output));
		
		if ($result && !$result instanceof Exception) {
			$output = $result;
		}
				
		return $output;
	}
	
	protected function callback($registry, $route) {
		return function($args) use($registry, $route) {
			static $model;
			
			$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

			// Keep the original trigger
			$trigger = $route;
					
			// Trigger the pre events
			$result = $registry->get('event')->trigger('model/' . $trigger . '/before', array(&$route, &$args));
			
			if ($result && !$result instanceof Exception) {
				$output = $result;
			} else {
				$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', substr($route, 0, strrpos($route, '/')));
				
				// Store the model object
				$key = substr($route, 0, strrpos($route, '/'));
				
				if (!isset($model[$key])) {
					$model[$key] = new $class($registry);
				}
				
				$method = substr($route, strrpos($route, '/') + 1);
				
				$callable = array($model[$key], $method);
	
				if (is_callable($callable)) {
					$output = call_user_func_array($callable, $args);
				} else {
					throw new \Exception('Error: Could not call model/' . $route . '!');
				}					
			}
			
			// Trigger the post events
			$result = $registry->get('event')->trigger('model/' . $trigger . '/after', array(&$route, &$args, &$output));
			
			if ($result && !$result instanceof Exception) {
				$output = $result;
			}
						
			return $output;
		};
	}	
}