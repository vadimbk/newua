<?php
class ControllerStartupPermission extends Controller {
	public function index() {
		if (isset($this->request->get['route'])) {
			$route = '';

			$part = explode('/', $this->request->get['route']);

			if (isset($part[0])) {
				$route .= $part[0];
			}

			if (isset($part[1])) {
				$route .= '/' . $part[1];
			}

			// If a 3rd part is found we need to check if its under one of the extension folders.
			$extension = array(
'extension/modification',
				'extension/dashboard',
				'extension/analytics',
				'extension/captcha',
				'extension/extension',
				'extension/feed',
				'extension/fraud',

			'octemplates/blog',
			

			'octemplates/stickers',
			

			'octemplates/design',
			'octemplates/module',
			
				'extension/module',
				'extension/payment','extension/hbapps','extension/hbseo',
				'extension/shipping',
				'extension/theme',
				'extension/total',
				'extension/report'
			);

			if (isset($part[2]) && in_array($route, $extension)) {
				$route .= '/' . $part[2];
			}

			// We want to ingore some pages from having its permission checked.
			$ignore = array(
				'common/dashboard',
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'
			);


        // start: OCdevWizard Helper
        if ($this->request->get['route'] == 'extension/ocdevwizard/helper' || preg_match('/extension\/ocdevwizard\/helper/', $this->request->get['route'])) {
          $route = 'extension/ocdevwizard/helper';
        }
        // end: OCdevWizard Helper
      

        // start: OCdevWizard In Stock Alert
        if ($this->request->get['route'] == 'extension/ocdevwizard/in_stock_alert' || preg_match('/extension\/ocdevwizard\/in_stock_alert/', $this->request->get['route'])) {
          $route = 'extension/ocdevwizard/in_stock_alert';
        }
        // end: OCdevWizard In Stock Alert
      
			if (!in_array($route, $ignore) && !$this->user->hasPermission('access', $route)) {
				return new Action('error/permission');
			}
		}
	}
}
