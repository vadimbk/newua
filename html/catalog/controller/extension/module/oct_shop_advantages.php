<?php
/**************************************************************/
/*	@copyright	OCTemplates 2015-2019						  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**************************************************************/

class ControllerExtensionModuleOctShopAdvantages extends Controller {
    public function index($setting) {
	    static $module = 0;

        if (isset($setting['status']) && $setting['status']) {

			foreach ($setting as $key => $value) {
				if(is_array($value))
					$setting[$key] = $value[$this->session->data['language']];
			}
			
			if (isset($setting['select-heading_indormation_id_block1']) && $setting['select-heading_indormation_id_block1']) {
				$data['heading_indormation_id_block1'] = $this->url->link('information/information', 'information_id=' . $setting['select-heading_indormation_id_block1']);
			}
			
			if (isset($setting['select-heading_indormation_id_block2']) && $setting['select-heading_indormation_id_block2']) {
				$data['heading_indormation_id_block2'] = $this->url->link('information/information', 'information_id=' . $setting['select-heading_indormation_id_block2']);
			}
			
			if (isset($setting['select-heading_indormation_id_block3']) && $setting['select-heading_indormation_id_block3']) {
				$data['heading_indormation_id_block3'] = $this->url->link('information/information', 'information_id=' . $setting['select-heading_indormation_id_block3']);
			}
			
			if (isset($setting['select-heading_indormation_id_block4']) && $setting['select-heading_indormation_id_block4']) {
				$data['heading_indormation_id_block4'] = $this->url->link('information/information', 'information_id=' . $setting['select-heading_indormation_id_block4']);
			}
			
			$data['oct_shop_advantages'] = $setting;

			$data['module'] = $module++;
			
            return $this->load->view('octemplates/module/oct_shop_advantages', $data);
        }
    }
}