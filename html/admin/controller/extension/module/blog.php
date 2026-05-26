<?php
/* All rights reserved belong to the module, the module developers http://opencartadmin.com */
// https://opencartadmin.com © 2011-2019 All Rights Reserved
// Distribution, without the author's consent is prohibited
// Commercial license
if (!class_exists('ControllerExtensionModuleBlog', false)) {
	class ControllerExtensionModuleBlog extends Controller {
		private $error = array();
		public function index() {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->index($this->registry);
				return $html;
		}
		public function uninstall() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->uninstall($this->registry);
				return $html;
			}
		}
		public function install() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->install($this->registry);
				return $html;
			}
		}
		public function sc_menu() {
			$this->control('agoo/blog');
			$html = $this->controller_agoo_blog->sc_menu();
			return $html;
		}

		public function check_ver() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->check_ver();
				return $html;
			}
		}


		public function schemes() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->schemes();
				return $html;
			}
		}

		public function widgets() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->widgets();
				return $html;
			}
		}

		public function ajax_list() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->ajax_list();
				return $html;
			}
		}

		public function install_new_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->install_new_loader();
				return $html;
			}
		}

		public function install_front_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->install_front_loader();
				return $html;
			}
		}

		public function install_back_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->install_back_loader();
				return $html;
			}
		}

		public function remove_old_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->remove_old_loader();
				return $html;
			}
		}

		public function remove_back_old_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->remove_back_old_loader();
				return $html;
			}
		}

		public function remove_front_old_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->remove_front_old_loader();
				return $html;
			}
		}

		public function remove_new_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->remove_new_loader();
				return $html;
			}
		}

		public function remove_front_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->remove_front_loader();
				return $html;
			}
		}

		public function remove_back_loader() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->remove_back_loader();
				return $html;
			}
		}

		public function ascp_widgets_save() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$this->controller_agoo_blog->ascp_widgets_save();
			}
		}

		public function autocomplete_template() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->autocomplete_template();
				return $html;
			}
		}

		public function msearchdir($path, $mode = "FULL", $myself = false, $maxdepth = -1, $d = 0) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->msearchdir($path, $mode, $myself, $maxdepth, $d);
				return $html;
			}
		}

		public function add_fields($prefix = '') {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->add_fields($prefix);
				return $html;
			}
		}

		public function http_catalog() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->http_catalog();
				return $html;
			}
		}

		public function cont($cont) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->cont($cont);
				return $html;
			}
		}

		public function cont_loading($cont, $file) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$this->controller_agoo_blog->cont_loading($cont, $file);
			}
		}

		public function deleteOldSetting() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->deleteOldSetting();
				return $html;
			}
		}

		public function deleteAllSettings() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->deleteAllSettings();
				return $html;
			}
		}

		public function deleteAllTables() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->deleteAllTables();
				return $html;
			}
		}


		public function createTables() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->createTables();
				return $html;
			}
		}

		public function cacheremove() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->cacheremove();
				return $html;
			}
		}

		public function prepare_ocmod() {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->prepare_ocmod();
				return $html;
			}
		}

		public function install_ocmod($widgets) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->install_ocmod($widgets);
				return $html;
			}
		}

		public function deletecache($key) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->deletecache($key);
				return $html;
			}
		}

		public function isAva($func) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->isAva($func);
				return $html;
			}
		}

		public function table_exists($tableName) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->table_exists($tableName);
				return $html;
			}
		}

		public function dir_permissions($file) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->dir_permissions($file);
				return $html;
			}
		}


		public function delTree($dir) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->delTree($dir);
				return $html;
			}
		}

		public function getDelFiles($dir, $ext = "*", $exp = array()) {
			if ($this->validate()) {
				$this->control('agoo/blog');
				$html = $this->controller_agoo_blog->getDelFiles($dir, $ext, $exp);
				return $html;
			}
		}


		protected function validate() {
			if (!$this->user->hasPermission('modify', 'agoo/blog')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
			return !$this->error;
		}
		public function control($cont) {
			$file = DIR_APPLICATION . 'controller/' . $cont . '.php';
			$class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $cont);
			if (file_exists($file)) {
				include_once($file);
				$this->registry->set('controller_' . str_replace('/', '_', $cont), new $class($this->registry));
			} else {
				trigger_error('Error: Could not load controller ' . $cont . '!');
				exit();
			}
		}
 	}
}
