<?php
class ControllerStartupMaintenance extends Controller {
	public function index() {

			if (defined('VERSION')) {
				if (!defined('SC_VERSION')) {
					define('SC_VERSION', (int)substr(str_replace('.', '', VERSION), 0, 2));
				}
				if (!is_object($this->model_seolang_seolang)) {
					$this->load->model('seolang/seolang');
				}
				if (!is_object($this->controller_seolang_seolanglib)) {
					$this->model_seolang_seolang->control('seolang/seolanglib');
				}
				if (!$this->registry->get('seolanglib')) {
					$this->registry->set('seolanglib', $this->controller_seolang_seolanglib);
				}
				if (isset($this->cache->lm_) && $this->registry) {
					$this->cache->lm_setRegistry($this->registry);
				}				
			}
    
		if ($this->config->get('config_maintenance')) {
			// Route
			if (isset($this->request->get['route']) && $this->request->get['route'] != 'startup/router') {
				$route = $this->request->get['route'];
			} else {
				$route = $this->config->get('action_default');
			}			
			
			$ignore = array(
				'common/language/language',
				'common/currency/currency'
			);
			
			// Show site if logged in as admin
			$this->user = new Cart\User($this->registry);

			if ((substr($route, 0, 17) != 'extension/payment' && substr($route, 0, 3) != 'api') && !in_array($route, $ignore) && !$this->user->isLogged()) {
				return new Action('common/maintenance');
			}
		}
	}
}
