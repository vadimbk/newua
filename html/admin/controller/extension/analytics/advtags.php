<?php
/**
 * Module: AdvTags
 * Controller
 */

class ControllerExtensionAnalyticsAdvtags extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/analytics/advtags'); //подключаем наш языковой файл

		$this->load->model('setting/setting');   //подключаем модель setting, он позволяет сохранять настройки модуля в БД

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) { //если мы нажали "Сохранить"  в панели, мы сохраняем текущие настройки
			$this->model_setting_setting->editSetting('advtags', $this->request->post);
			$this->response->redirect($this->url->link('extension/analytics', 'user_token=' . $this->session->data['user_token'], true));
		}
         // ваши переменные
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');		

		$data['text_pixel'] = $this->language->get('text_pixel');
		$data['text_send_fb_events'] = $this->language->get('text_send_fb_events');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_all'] = $this->language->get('text_all');
		$data['text_purchase_only'] = $this->language->get('text_purchase_only');

		$data['text_fb_gen_feed'] = $this->language->get('text_fb_gen_feed');
		$data['text_yes_direct'] = $this->language->get('text_yes_direct');
		$data['text_yes_file'] = $this->language->get('text_yes_file');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

        // если метод validate вернул warning, передадим его представлению
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/analytics', 'user_token=' . $this->session->data['user_token'], true)
		);

        //ссылки для формы и кнопки "cancel"
		$data['action'] = $this->url->link('extension/analytics/advtags', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('extension/analytics', 'user_token=' . $this->session->data['user_token'], true);

		//переменная с статусом модуля
        if (isset($this->request->post['advtags_status'])) {
			$data['advtags_status'] = $this->request->post['advtags_status'];
		} else {
			$data['advtags_status'] = $this->config->get('advtags_status');
		}


        if (isset($this->request->post['advtags_gtag_status'])) {
			$data['advtags_gtag_status'] = $this->request->post['advtags_gtag_status'];
		} else {
			$data['advtags_gtag_status'] = $this->config->get('advtags_gtag_status');
		}

        if (isset($this->request->post['advtags_gtag_tracker'])) {
			$data['advtags_gtag_tracker'] = $this->request->post['advtags_gtag_tracker'];
		} else {
			$data['advtags_gtag_tracker'] = $this->config->get('advtags_gtag_tracker');
		}

        if (isset($this->request->post['advtags_gtag_events'])) {
			$data['advtags_gtag_events'] = $this->request->post['advtags_gtag_events'];
		} else {
			$data['advtags_gtag_events'] = $this->config->get('advtags_gtag_events');
		}

        if (isset($this->request->post['advtags_gtag_conversion'])) {
			$data['advtags_gtag_conversion'] = $this->request->post['advtags_gtag_conversion'];
		} else {
			$data['advtags_gtag_conversion'] = $this->config->get('advtags_gtag_conversion');
		}

        //ссылки на контроллеры header,column_left,footer, иначе мы не сможем вывести заголовок, подвал и левое меню в файле представления
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/analytics/advtags', $data));
	}

    //обязательный метод в контроллере, он запускается для проверки разрешено ли пользователю изменять настройки данного модуля
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/analytics/advtags')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}