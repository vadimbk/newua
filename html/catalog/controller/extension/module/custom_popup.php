<?php
class ControllerExtensionModuleCustomPopup extends Controller {
	public function index($setting) {
		$data['html'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
        $data['custom_css'] = $setting['css'];
        $data['seconds_to_close'] = $setting['seconds_to_close'] * 1000;
        
        $id = 'custom_popup_' . $setting['uid'];
		
		if (!isset($this->session->data[$id])) {
			$this->session->data[$id] = 0;
		}
        
        if (!$setting['display_times'] || ($this->session->data[$id] <= $setting['display_times'])) {
			++$this->session->data[$id];
            
            return $this->load->view('extension/module/custom_popup', $data);
		}
	}
}