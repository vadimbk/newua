<?php
class ControllerExtensionModuleCSVOcextDMPRO extends Controller {
    
	private $error = array();
        private $this_version = '';
        private $this_extension = '';
        private $this_ocext_host = '';
        private $demo_mode = 1;
        private $debug_mode = 0;
        private $path_oc_version = 'extension/module';
        private $path_oc_version_feed = 'extension/feed';
        private $token_name = 'user_token';
        private $anyxml = FALSE;
        private $anyxls = FALSE;
        private $loader_name = 'load';
        private $ftype = '';
        private $setting_version_settings;
        
        public function __construct($registry) {
            $this->registry = $registry;
            $this->getAnyXMLStatus();
            $this->getAnyXLStatus();
            $this->getSettingVersionSettings();
            require_once(modification(DIR_APPLICATION . 'model/tool/ocext_loader.php'));
            $ocext_loader = new Ocext_Loader($registry);
            $this->registry->set('ocext_load', $ocext_loader);
            $this->loader_name = 'ocext_load';
        }
        
        
        private $max_memory_usage = array('memory_usage'=>0,'memory_usage_txt'=>'');
        
        public function setMaxMemoryUsage($max_memory_usage = array()) {
            
            $memory_usage = memory_get_usage();
            
            if(!$max_memory_usage && $memory_usage>$this->max_memory_usage['memory_usage']){
                
                $this->max_memory_usage['memory_usage'] = $memory_usage;
                
                $this->max_memory_usage['memory_usage_txt'] = round(($memory_usage/1024/1024),3).'Mb';
                
            }elseif($max_memory_usage){
                
                if($memory_usage > $max_memory_usage['memory_usage']){

                    $this->max_memory_usage['memory_usage'] = $memory_usage;

                    $this->max_memory_usage['memory_usage_txt'] = round(($memory_usage/1024/1024),3).'Mb';

                }
                
            }
            
        }
        
        public function getSettingVersionSettings(){
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $setting_version_settings = $this->model_tool_csv_ocext_dmpro->getSettingVersionSettings();
            
            $this->this_version = $setting_version_settings['edition']['version'];
            
            $this->this_extension = $setting_version_settings['edition']['extension'];
            
            $this->this_ocext_host = $setting_version_settings['edition']['version_host'];
            
            $this->setting_version_settings = $setting_version_settings;
            
        }
        
        public function getProcessHistoryStatus() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $odmpro_tamplate_data_id = 0;
            
            $supplier_name = '';
            
            if(isset($this->request->get['odmpro_tamplate_data_id'])){
            
                $odmpro_tamplate_data_id = $this->request->get['odmpro_tamplate_data_id'];
                
                $supplier_name = $this->request->get['supplier_name'];
                
            }
            
            $result = $this->model_tool_csv_ocext_dmpro->getProcessHistoryStatus($odmpro_tamplate_data_id,$supplier_name);
            
        }
        
        public function getAnyXMLStatus() {
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->anyxml = $this->model_tool_csv_ocext_dmpro->getAnyXMLStatus();
            
        }
        
        public function getAnyXLStatus() {
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->anyxls = $this->model_tool_csv_ocext_dmpro->getAnyXLStatus();
            
        }
        
        
        
        public function index() {
            
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');

		$this->load->model('setting/setting');
                
                $this->load->model('tool/csv_ocext_dmpro');
                
                $data['setting_version_settings'] = $this->setting_version_settings;   
                $data['setting_version_functional'] = $this->setting_version_settings['functional'];  
                
                $heading_title = sprintf($this->language->get('heading_title_'.$this->this_extension), str_replace('.0.0', '', $this->setting_version_settings['edition']['version']));
                
                $data['heading_title'] = $heading_title;
                
                $this->document->setTitle(strip_tags($heading_title));
                
                $data['open_tab'] = 'tab_csv_import';
                $data['token_name'] = $this->token_name;
                $data['path_oc_version'] = $this->path_oc_version;
                $data['path_oc_version_feed'] = $this->path_oc_version_feed;
                $data['heading_title'] = $heading_title;
                $data['tab_csv_import'] = $this->language->get('tab_csv_import');
                $data['tab_csv_export'] = $this->language->get('tab_csv_export');
                $data['tab_setting'] = $this->language->get('tab_setting');
                $data['tab_welcome_extecom'] = $this->language->get('tab_welcome_extecom');
                $data['text_step_1_setting'] = $this->language->get('text_step_1_setting');
                $data['text_step_4_setting'] = $this->language->get('text_step_4_setting');
                $data['text_step_2_synchronization'] = $this->language->get('text_step_2_synchronization');
                $data['text_step_3_ending'] = $this->language->get('text_step_3_ending');
                $data['entry_next'] = $this->language->get('entry_next');
                $data['text_wite'] = $this->language->get('text_wite'); 
                $data['entry_select'] = $this->language->get('entry_select');
                $data['entry_odmpro_format_data'] = $this->language->get('entry_odmpro_format_data');
                $data['text_type_data_ignor'] = $this->language->get('text_type_data_ignor');
                $data['text_csv_ocext_dmpro_key'] = $this->language->get('text_csv_ocext_dmpro_key');
                $data['text_csv_ocext_dmpro_email'] = $this->language->get('text_csv_ocext_dmpro_email');
                
                $edition_formats = implode(',',$this->setting_version_settings['edition']['import_formats']);
                
                $data['odmpro_format_data'][$edition_formats] = 'csv';
                
                if(file_exists(DIR_APPLICATION.'controller/module/yml_ocext_dmpro.php')){
                    
                    $data['odmpro_format_data'][] = 'yml';
                    
                }
                if(file_exists(DIR_APPLICATION.'controller/module/xls_ocext_dmpro.php')){
                    
                    $data['odmpro_format_data'][] = 'xls';
                    
                }
                
                $data['entry_odmpro_tamplate_data'] = $this->language->get('entry_odmpro_tamplate_data');
                $data['entry_odmpro_tamplate_data_empty'] = $this->language->get('entry_odmpro_tamplate_data_empty');
                $data['entry_odmpro_tamplate_data_new'] = $this->language->get('entry_odmpro_tamplate_data_new');
                $data['entry_odmpro_csv_delimiter'] = $this->language->get('entry_odmpro_csv_delimiter');
                $data['entry_odmpro_csv_enclosure'] = $this->language->get('entry_odmpro_csv_enclosure');
                $data['entry_odmpro_csv_escape'] = $this->language->get('entry_odmpro_csv_escape');
                $data['entry_odmpro_encoding'] = $this->language->get('entry_odmpro_encoding'); 
                $data['button_cancel'] = $this->language->get('button_cancel'); 
                $data['entry_odmpro_language'] = $this->language->get('entry_odmpro_language');
                $data['entry_odmpro_currency'] = $this->language->get('entry_odmpro_currency');
                $data['entry_odmpro_store'] = $this->language->get('entry_odmpro_store');
                $data['entry_odmpro_file'] = $this->language->get('entry_odmpro_file');
                $data['entry_odmpro_file_upload'] = $this->language->get('entry_odmpro_file_upload');
                $data['text_odmpro_file_url'] = $this->language->get('text_odmpro_file_url');
                $data['entry_odmpro_file_url'] = $this->language->get('entry_odmpro_file_url');
                $data['entry_download_field_to_file'] = $this->language->get('entry_download_field_to_file');
                $data['text_import_start'] = $this->language->get('text_import_start');
                $data['entry_odmpro_tamplate_data_level_0'] = $this->language->get('entry_odmpro_tamplate_data_level_0');
                $data['entry_odmpro_tamplate_data_level_1'] = $this->language->get('entry_odmpro_tamplate_data_level_1');
                $data['entry_odmpro_tamplate_data_level'] = $this->language->get('entry_odmpro_tamplate_data_level');
                $data['text_step_3_ending_export'] = $this->language->get('text_step_3_ending_export');
                $data['text_step_3_start_export'] = $this->language->get('text_step_3_start_export');
                
                
                $data['entry_odmpro_format_data_empty'] = '';
                
                $data['column_update_csv_link_template_data'] = $this->language->get('column_update_csv_link_template_data');
                $data['column_update_csv_link_token'] = $this->language->get('column_update_csv_link_token');
                $data['column_update_csv_link_link'] = $this->language->get('column_update_csv_link_link');
                $data['column_update_csv_link_link_export'] = $this->language->get('column_update_csv_link_link_export');
                $data['entry_update_csv_link_status_0'] = $this->language->get('entry_update_csv_link_status_0');
                $data['entry_update_csv_link_status_1'] = $this->language->get('entry_update_csv_link_status_1');
                $data['entry_update_csv_link_status_3'] = $this->language->get('entry_update_csv_link_status_3');
                $data['column_update_csv_link_status'] = $this->language->get('column_update_csv_link_status');
                $data['entry_update_csv_link_new_title'] = $this->language->get('entry_update_csv_link_new_title');
                $data['entry_update_csv_link_title'] = $this->language->get('entry_update_csv_link_title');
                $data['entry_update_csv_link_empty'] = $this->language->get('entry_update_csv_link_empty');
                
                if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                        
			$this->session->data['success'] = $this->language->get('text_success');
                        
                        if(isset($this->request->post['odmpro_update_csv_link'])){
                            foreach ($this->request->post['odmpro_update_csv_link'] as $key => $value) {

                                if(isset($value['token']) && !$value['token'] && !$value['tamplate_data_id'] && !$value['status']){

                                    unset($this->request->post['odmpro_update_csv_link'][$key]);

                                }elseif($value['status']==3){

                                    unset($this->request->post['odmpro_update_csv_link'][$key]);

                                }

                            }
                            
                            if(isset($this->request->post['odmpro_update_csv_smart_exchange_link'])){
                                
                                $this->model_tool_csv_ocext_dmpro->editSetting('odmpro_update_csv_smart_exchange_link', $this->request->post,TRUE);
                                
                                $this->model_tool_csv_ocext_dmpro->updateSmartExchange($this->request->post);
                                
                            }

                            $this->model_tool_csv_ocext_dmpro->editSetting('odmpro_update_csv_link', $this->request->post,TRUE);
                        }
                        
                        if(isset($this->request->post['csv_ocext_dmpro_key'])){
                            
                            $this->load->model('setting/setting');

                            $this->model_setting_setting->editSetting('csv_ocext_dmpro', $this->request->post);
                        }
                        
                        $this->response->redirect($this->url->link($this->path_oc_version.'/csv_ocext_dmpro', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL'));
                        
		}
                
                $data['csv_ocext_dmpro_key'] = '';
                
                if($this->config->get('csv_ocext_dmpro_key')){
                    
                    $data['csv_ocext_dmpro_key'] = $this->config->get('csv_ocext_dmpro_key');
                    
                }
                
                $data['csv_ocext_dmpro_email'] = '';
                
                if($this->config->get('csv_ocext_dmpro_email')){
                    
                    $data['csv_ocext_dmpro_email'] = $this->config->get('csv_ocext_dmpro_email');
                    
                }
                
                $lic = $this->model_tool_csv_ocext_dmpro->getLincenceStatus(FALSE); 
                
                $data['text_lic_error'] = '';
                $data['text_lic_success'] = '';
                
                if(!$lic['status'] && (!isset($lic['error']) || !$lic['error']) ){
                    $data['open_tab'] = 'tab-welcome-extecom';
                    $data['text_lic_error'] = "Продукт не зарегистрирован. Пожалуйста, обратитесь в службу поддержки за получением данных для лицензии";
                    $this->error['warning'] = $data['text_lic_error'];
                }
                elseif(!$lic['status']){
                    $data['text_lic_error'] = $lic['error'];
                    $this->error['warning'] = $data['text_lic_error'];
                    $data['open_tab'] = 'tab-welcome-extecom';
                }elseif(isset($lic['success'])){
                    
                    $data['text_lic_success'] = $lic['success'];
                    
                }
                
                /*
                if(!$lic){
                    
                    $data['open_tab'] = 'tab-welcome-extecom';
                    $data['text_lic_error'] = "Продукт не зарегистрирован. Пожалуйста, обратитесь в службу поддержки за получением данных для лицензии";
                    $this->error['warning'] = $data['text_lic_error'];
                }
                */
                //миграция настроек в таблицу модуля
                if($this->config->get('odmpro_tamplate_data') && !$this->config->get('odmpro_last_version_data_migrate')){
                    
                    $odmpro_tamplate_data_migrate['odmpro_tamplate_data'] = $this->config->get('odmpro_tamplate_data');
                    
                    $this->model_tool_csv_ocext_dmpro->editSetting('odmpro',$odmpro_tamplate_data_migrate,TRUE);
                    
                    if($this->config->get('odmpro_update_csv_link')){
                        
                        $odmpro_update_csv_link_migrate['odmpro_update_csv_link'] = $this->config->get('odmpro_update_csv_link');
                        
                        $this->model_tool_csv_ocext_dmpro->editSetting('odmpro_update_csv_link',$odmpro_update_csv_link_migrate,TRUE);
                        
                    }
                    
                    $this->model_setting_setting->editSetting('odmpro_last_version_data_migrate', array('odmpro_last_version_data_migrate'=>1),TRUE);

                }
                
                $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
                
                $odmpro_update_csv_link = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro_update_csv_link','odmpro_update_csv_link',TRUE);
                
                //$data['recommended_params'] = $this->model_tool_csv_ocext_dmpro->getRecommendedParams($config_odmpro_tamplate_data);
                
                $data['debug_mode'] = $this->debug_mode;
                
                $data['demo_mode'] = $this->demo_mode;
                
                $data['odmpro_update_csv_link'] = array();
                
                if($odmpro_update_csv_link){
                
                    $data['odmpro_update_csv_link'] = $odmpro_update_csv_link;
                
                }
                
                $data['odmpro_update_csv_link_tamplate_data'] = array();
            
                if($config_odmpro_tamplate_data){

                    $data['odmpro_update_csv_link_tamplate_data'] = $config_odmpro_tamplate_data;

                }
                
                if(isset($this->setting_version_settings['functional']['smart_exchange'])){
                    
                    $odmpro_update_csv_smart_exchange_link = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro_update_csv_smart_exchange_link','odmpro_update_csv_smart_exchange_link',TRUE);
                    
                    $config_odmpro_tamplate_data['odmpro_update_csv_smart_exchange_link'] = $odmpro_update_csv_smart_exchange_link;
                    
                    $config_odmpro_tamplate_data['odmpro_update_csv_link'] = $odmpro_update_csv_link;
                    
                    $data['smart_exchange'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings('smart_exchange',$config_odmpro_tamplate_data,'','');
                }
                
                $data['cancel'] = $this->url->link('extension/module', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                
                $data['entry_odmpro_tamplate_data_save_tamplate_data'] = $this->language->get('entry_odmpro_tamplate_data_save_tamplate_data');
                
                $data['action_setting'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                
                if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
                
                if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
                }elseif(isset ($this->session->data['error'])){
                        $data['error_warning'] = $this->session->data['error'];
                        unset($this->session->data['error']);
                } else {
			$data['error_warning'] = '';
		}
                
                $data[$this->token_name] = $this->session->data[$this->token_name];
  		$data['breadcrumbs'] = array();
   		$data['breadcrumbs'][] = array(
                    'text'      => $this->language->get('text_home'),
                    'href'      => $this->url->link('common/home', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL'),
                    'separator' => false
   		);
   		$data['breadcrumbs'][] = array(
                    'text'      => $this->language->get('text_module'),
                    'href'      => $this->url->link('extension/extension', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL'),
                    'separator' => ' :: '
   		);
   		$data['breadcrumbs'][] = array(
                    'text'      => $heading_title,
                    'href'      => $this->url->link($this->path_oc_version.'/csv_ocext_dmpro', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL'),
                    'separator' => ' :: '
   		);
                
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_content_top'] = $this->language->get('text_content_top');
		$data['text_content_bottom'] = $this->language->get('text_content_bottom');
		$this->load->model('design/layout');
		$data['layouts'] = $this->model_design_layout->getLayouts();
                $data['back'] = $this->url->link('extension/module', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
		$data['button_back'] = $this->language->get( 'button_back' );
                $data['header'] = $this->load->controller('common/header');
                $data['column_left'] = $this->load->controller('common/column_left');
                $data['footer'] = $this->load->controller('common/footer');
                
                $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/csv_ocext_dmpro'.$this->ftype, $data));
	}
        
        public function getStepOneSettings() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            $data['token_name'] = $this->token_name;
            $data['path_oc_version'] = $this->path_oc_version;   
            $data['setting_version_settings'] = $this->setting_version_settings;   
            $data['setting_version_functional'] = $this->setting_version_settings['functional'];   
            
            $odmpro_update_csv_link = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro_update_csv_link','odmpro_update_csv_link',TRUE);
            
            $data['format_data'] = $this->request->post['format_data'];
            
            $data['type_process'] = 'import';
            
            if(isset($this->request->get['type_process'])){
            
                $data['type_process'] = $this->request->get['type_process'];
                
            }
            
            $data['demo_mode'] = $this->demo_mode;
            
            // редирект в другой модуль
            if($data['format_data'] && $data['format_data']!='csv'){
                
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
                
                $link = $this->url->link($this->path_oc_version.'/'.$data['format_data'].'_ocext_dmpro', ''.$this->token_name.'=' . $this->request->get[$this->token_name], 'SSL');
                
                $data['entry_odmpro_format_data_redirect'] = sprintf($this->language->get('entry_odmpro_format_data_redirect'),$link,$data['format_data']);
                
                $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_step_one_settings'.$this->ftype, $data));
                
                return;
                
            }elseif(!$data['format_data']){
                
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
                
                $data['entry_odmpro_format_data_redirect'] = $this->language->get('entry_odmpro_format_select_format_data');
                
                $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_step_one_settings'.$this->ftype, $data));
                
                return;
                
            }
            
            $tamplate_data_selected_id = $this->request->post['tamplate_data'];
            
            $data['tamplate_data_selected_id'] = $tamplate_data_selected_id;
            
            $this->load->model('setting/setting');
            
            //$this->load->model('tool/csv_ocext_dmpro');
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $data['tamplates_data'] = array();
            
            /*
             * первый вход, импорт примеров
             */
            if(!$config_odmpro_tamplate_data && !$this->config->get('odmpro_csv_first_us')){

                $import_sample_data['odmpro_tamplate_data'] = json_decode('{"a36846f8883d8e531e9ab61005989af7":{"format_data":"csv","id":"a36846f8883d8e531e9ab61005989af7","csv_delimiter":"^","csv_enclosure":"~","csv_escape":"","encoding":"UTF-8","log_status":"1","log_details":"1","log_update":"1","log_html":"1","log_file_name":"ocext_log\/ocext_log_csv_import_export","file_upload":"","file_url":"http:\/\/amigration2_0-2_1.ocext.com\/image\/csv_8_test.csv","export_file_name":"csv_export2","file_name_write_time":"0","level":"1","language_id":"1","currency_code":"RUB","store_id":["0"],"export_field_name":{"a36846f8883d8e531e9ab61005989af7":{"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435":"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435","\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440":"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440","\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435":"\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435","\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f":"\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f","\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435":"\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435","\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b":"\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b","\u0426\u0435\u043d\u0430":"\u0426\u0435\u043d\u0430","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)","\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c","\u0412\u0435\u0441":"\u0412\u0435\u0441","\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c","\u0420\u0430\u0437\u043c\u0435\u0440":"\u0420\u0430\u0437\u043c\u0435\u0440","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430":"\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430","\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c":"\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c","\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435":"\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435","\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439":"\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439","\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f":"\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id":"\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku":"\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 1":"\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 1","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 2":"\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 2","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u043e\u043f\u0446\u0438\u0438":"\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u043e\u043f\u0446\u0438\u0438","\u0426\u0435\u043d\u0430 \u043f\u043e \u0430\u043a\u0446\u0438\u0438":"\u0426\u0435\u043d\u0430 \u043f\u043e \u0430\u043a\u0446\u0438\u0438","\u041e\u043f\u0446\u0438\u044f \u0446\u0435\u043d\u0430 \u0438 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e":"\u041e\u043f\u0446\u0438\u044f \u0446\u0435\u043d\u0430 \u0438 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3","SEO URL(Alias)":"SEO URL(Alias)"}},"type_data":{"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435":"product","\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440":"product","\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435":"product","\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f":"product","\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435":"product","\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b":"product","\u0426\u0435\u043d\u0430":"product","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)":"product","\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"product","\u0412\u0435\u0441":"product","\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"product","\u0420\u0430\u0437\u043c\u0435\u0440":"product","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430":"product","\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c":"product","\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435":"product","\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439":"product","\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f":"product","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id":"product","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku":"product","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 1":"0","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 2":"0","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u043e\u043f\u0446\u0438\u0438":"0","\u0426\u0435\u043d\u0430 \u043f\u043e \u0430\u043a\u0446\u0438\u0438":"0","\u041e\u043f\u0446\u0438\u044f \u0446\u0435\u043d\u0430 \u0438 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e":"0","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1":"0","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2":"0","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3":"0","SEO URL(Alias)":"0"},"type_data_column":{"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435":{"db_table___db_column":"product_description___name","additinal_settings":{"column_request":"0"}},"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440":{"db_table___db_column":"product_identificator___identificator","additinal_settings":{"identificator_insert":"1","identificator_type":"jan","column_request":"1"}},"\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435":{"db_table___db_column":"product___image_advanced","additinal_settings":{"image_upload":"1","image_new_path":"\/new\/image\/","image_new_name":"1","column_request":"0"}},"\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f":{"db_table___db_column":"product___images","additinal_settings":{"delimeter":",","image_upload":"1","image_new_path":"\/new\/image","image_new_name":"1","column_request":"0"}},"\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435":{"db_table___db_column":"product_description___description","additinal_settings":{"column_request":"0"}},"\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b":{"db_table___db_column":"product_description___meta_title","additinal_settings":{"column_request":"0"}},"\u0426\u0435\u043d\u0430":{"db_table___db_column":"product___price_advanced","additinal_settings":{"price_rate":"","price_delta":"","price_around":"1","column_request":"0"}},"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)":{"db_table___db_column":"product___category_whis_path","additinal_settings":{"delimeter":"\/","main_category":"1","all_product_category":"1","column_request":"0"}},"\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":{"db_table___db_column":"product___manufacturer_name","additinal_settings":{"column_request":"0"}},"\u0412\u0435\u0441":{"db_table___db_column":"product___weight","additinal_settings":{"column_request":"0"}},"\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":{"db_table___db_column":"product_attribute___attribute_value","additinal_settings":{"attribute_group_id___attribute_id":"8___28","column_request":"0"}},"\u0420\u0430\u0437\u043c\u0435\u0440":{"db_table___db_column":"product_option_value___option_value_option_value_name","additinal_settings":{"option_id":"field_this_file___\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435","quantity_default":"0","price_default":"field_this_file___\u0426\u0435\u043d\u0430","price_rate":"","price_delta":"","price_around":"0","price_whis_delta":"0","required_default":"0","subtract_default":"0","column_request":"0"}},"\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430":{"db_table___db_column":"product_attribute___attribute_values_whis_attrubute_name","additinal_settings":{"attribute_group_id":"9","delimiter_2":"---","delimiter_1":"|","column_request":"0"}},"\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c":{"db_table___db_column":"product_attribute___attribute_values_whis_attrubute_name_and_group_name","additinal_settings":{"delimiter_2":"---","delimiter_1":"|","attribute_group_id":"0","column_request":"0"}},"\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435":{"db_table___db_column":"product_filter___filter_name","additinal_settings":{"filter_group_id":"14","column_request":"0"}},"\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439":{"db_table___db_column":"product_filter___filter_values_whis_filter_name","additinal_settings":{"filter_group_id":"0","delimeter":"|","column_request":"0"}},"\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f":{"db_table___db_column":"product_filter___filter_values_whis_filter_name_and_group_name","additinal_settings":{"delimiter_2":"---","delimiter_1":"|","filter_group_id":"0","column_request":"0"}},"\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id":{"db_table___db_column":"product_related___relate_by_product_id","additinal_settings":{"delimeter":"","column_request":"0"}},"\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku":{"db_table___db_column":"product_related___relate_by_sku","additinal_settings":{"delimeter":"\/","column_request":"0"}}},"type_data_general_settings":{"product":{"quantity_default":"100","status_enable":"1","seo_url_generator":"1","dis_by_quan":"","manufacturer_filter":"0","prodict_id_from_filter":"","prodict_id_to_filter":""}},"type_change":"update_data","name":"\u041f\u0440\u0438\u043c\u0435\u0440 \u0442\u043e\u0432\u0430\u0440\u044b, \u043e\u043f\u0446\u0438\u0438, \u0444\u0438\u043b\u044c\u0442\u0440\u044b, \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u044b, \u0441\u043e\u043f\u0443\u0442\u0441\u0442\u0432\u0443\u044e\u0449\u0438\u0435, \u0441\u043a\u0438\u0434\u043a\u0438, \u0427\u041f\u0423","start":"1","limit":"2"},"35d8655daf0bcf670014fa38fd7d16c1":{"format_data":"csv","id":"35d8655daf0bcf670014fa38fd7d16c1","csv_delimiter":"^","csv_enclosure":"~","csv_escape":"","encoding":"UTF-8","log_status":"1","log_details":"1","log_update":"1","log_html":"1","log_file_name":"ocext_log\/ocext_log_csv_import_export.htm","file_upload":"","file_url":"http:\/\/amigration2_0-2_1.ocext.com\/image\/csv_8_test.csv","export_file_name":"csv_export","file_name_write_time":"0","level":"1","language_id":"1","currency_code":"RUB","store_id":["0"],"hide_column":"1","export_field_name":{"35d8655daf0bcf670014fa38fd7d16c1":{"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435":"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435","\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440":"\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440","\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435":"\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435","\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f":"\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f","\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435":"\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435","\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b":"\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b","\u0426\u0435\u043d\u0430":"\u0426\u0435\u043d\u0430","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)","\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c","\u0412\u0435\u0441":"\u0412\u0435\u0441","\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c","\u0420\u0430\u0437\u043c\u0435\u0440":"\u0420\u0430\u0437\u043c\u0435\u0440","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430":"\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430","\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c":"\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c","\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435":"\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435","\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439":"\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439","\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f":"\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id":"\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku":"\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 1":"\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 1","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 2":"\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 2","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u043e\u043f\u0446\u0438\u0438":"\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u043e\u043f\u0446\u0438\u0438","\u0426\u0435\u043d\u0430 \u043f\u043e \u0430\u043a\u0446\u0438\u0438":"\u0426\u0435\u043d\u0430 \u043f\u043e \u0430\u043a\u0446\u0438\u0438","\u041e\u043f\u0446\u0438\u044f \u0446\u0435\u043d\u0430 \u0438 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e":"\u041e\u043f\u0446\u0438\u044f \u0446\u0435\u043d\u0430 \u0438 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3":"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3","SEO URL(Alias)":"SEO URL(Alias)"}},"type_data":{"\u041d\u0430\u0437\u0432\u0430\u043d\u0438\u0435":"0","\u0418\u0434\u0435\u043d\u0442\u0438\u0444\u0438\u043a\u0430\u0442\u043e\u0440":"0","\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435":"0","\u0414\u043e\u043f.\u0438\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u044f":"0","\u041e\u043f\u0438\u0441\u0430\u043d\u0438\u0435":"0","\u041c\u0435\u0442\u0430 \u0442\u0430\u0439\u0442\u043b":"0","\u0426\u0435\u043d\u0430":"0","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)":"category","\u041f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"0","\u0412\u0435\u0441":"0","\u0421\u0442\u0440\u0430\u043d\u0430 \u043f\u0440\u043e\u0438\u0437\u0432\u043e\u0434\u0438\u0442\u0435\u043b\u044c":"0","\u0420\u0430\u0437\u043c\u0435\u0440":"0","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430":"0","\u0413\u0440\u0443\u043f\u043f\u0430 \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0430\u0442\u0440\u0438\u0431\u0443\u0442\u0430 \u0438 \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435\u043c":"0","\u0424\u0438\u043b\u044c\u0442\u0440, \u043e\u0434\u043d\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0435":"0","\u0424\u0438\u043b\u044c\u0442\u0440, \u043d\u0435\u0441\u043a\u043e\u043b\u044c\u043a\u043e \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u0439":"0","\u0424\u0438\u043b\u044c\u0442\u0440, \u0441 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0433\u0440\u0443\u043f\u043f\u044b \u0438 \u043d\u0430\u0437\u0432\u0430\u043d\u0438\u0435\u043c \u0437\u043d\u0430\u0447\u0435\u043d\u0438\u044f":"0","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e product_id":"0","\u0421\u0432\u044f\u0437\u0430\u043d\u043d\u044b\u0435 \u0442\u043e\u0432\u0430\u0440\u044b \u043f\u043e sku":"0","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 1":"0","\u041e\u043f\u0446\u0438\u044f, \u0432\u0430\u0440\u0438\u0430\u043d\u0442 \u043e\u0444\u043e\u0440\u043c\u043b\u0435\u043d\u0438\u044f 2":"0","\u0417\u043d\u0430\u0447\u0435\u043d\u0438\u0435 \u043e\u043f\u0446\u0438\u0438":"0","\u0426\u0435\u043d\u0430 \u043f\u043e \u0430\u043a\u0446\u0438\u0438":"0","\u041e\u043f\u0446\u0438\u044f \u0446\u0435\u043d\u0430 \u0438 \u043a\u043e\u043b\u0438\u0447\u0435\u0441\u0442\u0432\u043e":"0","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1":"category","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2":"category","\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3":"category","SEO URL(Alias)":"0"},"type_data_column":{"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 (\u043f\u0443\u0442\u044c \u0441 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u044f\u043c\u0438)":{"db_table___db_column":"category___category_whis_path","additinal_settings":{"delimeter":"\/","column_request":"0"}},"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 1":{"db_table___db_column":"category___category_name_and_parent_level","additinal_settings":{"parent_level":"0","parent_category_id":"0","column_request":"0"}},"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 2":{"db_table___db_column":"category___category_name_and_parent_level","additinal_settings":{"parent_level":"1","parent_category_id":"0","column_request":"0"}},"\u0420\u0430\u0437\u043c\u0435\u0449\u0435\u043d\u0438\u0435 3":{"db_table___db_column":"category___category_name_and_parent_level","additinal_settings":{"parent_level":"2","parent_category_id":"0","column_request":"0"}}},"type_data_general_settings":{"category":{"status_enable":"0","seo_url_generator":"1"}},"type_change":"update_data","name":"\u041f\u0440\u0438\u043c\u0435\u0440 \u0438\u043c\u043f\u043e\u0440\u0442\u0430 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u0439 \u0432 \u043a\u043e\u043b\u043e\u043d\u043a\u0430\u0445 \u0438 \u043f\u0443\u0442\u0435\u043c","start":"1","limit":"30"},"b30e6c43fb1efeafa83c7ac528cf8794":{"format_data":"csv","id":"b30e6c43fb1efeafa83c7ac528cf8794","csv_delimiter":";","csv_enclosure":"","csv_escape":"","encoding":"UTF-8","log_status":"1","log_details":"1","log_update":"1","log_html":"1","log_file_name":"ocext_log\/ocext_log_csv_import_export.htm","file_upload":"","file_url":"http:\/\/amigration2_0-2_1.ocext.com\/image\/csv_6_test.csv","export_file_name":"csv_export","file_name_write_time":"0","level":"1","language_id":"1","currency_code":"RUB","store_id":["0"],"export_field_name":{"b30e6c43fb1efeafa83c7ac528cf8794":{"\u041e\u0442\u0437\u044b\u0432":"\u041e\u0442\u0437\u044b\u0432","\u0410\u0432\u0442\u043e\u0440":"\u0410\u0432\u0442\u043e\u0440","product_id \u043d\u0430 \u0441\u0430\u0439\u0442\u0435":"product_id \u043d\u0430 \u0441\u0430\u0439\u0442\u0435","rewiev_id":"rewiev_id","Rating":"Rating"}},"type_data":{"\u041e\u0442\u0437\u044b\u0432":"review","\u0410\u0432\u0442\u043e\u0440":"review","product_id \u043d\u0430 \u0441\u0430\u0439\u0442\u0435":"review","rewiev_id":"review","Rating":"review"},"type_data_column":{"\u041e\u0442\u0437\u044b\u0432":{"db_table___db_column":"review___text","additinal_settings":{"column_request":"0"}},"\u0410\u0432\u0442\u043e\u0440":{"db_table___db_column":"review___author","additinal_settings":{"column_request":"0"}},"product_id \u043d\u0430 \u0441\u0430\u0439\u0442\u0435":{"db_table___db_column":"review___product_id","additinal_settings":{"column_request":"0"}},"rewiev_id":{"db_table___db_column":"review_identificator___identificator","additinal_settings":{"identificator_insert":"1","identificator_type":"aid","column_request":"0"}},"Rating":{"db_table___db_column":"review___rating","additinal_settings":{"column_request":"0"}}},"type_data_general_settings":{"review":{"status_enable":"1"}},"type_change":"update_data","name":"\u041f\u0440\u0438\u043c\u0435\u0440 \u0438\u043c\u043f\u043e\u0440\u0442\u0430 \u043e\u0442\u0437\u044b\u0432\u043e\u0432","start":"1","limit":"30"},"d59ba9b72d32e10c82e9c030602c5b48":{"format_data":"csv","id":"d59ba9b72d32e10c82e9c030602c5b48","csv_delimiter":"^","csv_enclosure":"","csv_escape":"","encoding":"UTF-8","log_status":"1","log_details":"0","log_update":"1","log_html":"1","log_file_name":"ocext_log\/ocext_log_csv_import_export","file_upload":"","file_url":"","export_file_name":"csv_export2","file_name_write_time":"0","level":"0","language_id":"1","currency_code":"RUB","store_id":["0"],"export_field_name":{"d59ba9b72d32e10c82e9c030602c5b48":{"column_1":"column_1","column_2":"column_2","column_3":"column_3","column_4":"column_4","column_5":"column_5","column_6":"column_6","column_7":"column_7"}},"type_data":{"column_1":"product","column_2":"product","column_3":"product","column_4":"product","column_5":"product","column_6":"product","column_7":"product"},"type_data_column":{"column_1":{"db_table___db_column":"product___seo_url","additinal_settings":{"column_request":"0"}},"column_2":{"db_table___db_column":"product___quantity_advanced","additinal_settings":{"quantity_update":"","column_request":"0"}},"column_3":{"db_table___db_column":"product_identificator___identificator","additinal_settings":{"identificator_insert":"0","identificator_type":"jan","column_request":"0"}},"column_4":{"db_table___db_column":"product_discount___date_start","additinal_settings":{"column_request":"0"}},"column_5":{"db_table___db_column":"product_option_value___weight","additinal_settings":{"column_request":"0"}},"column_6":{"db_table___db_column":"product___seo_url_aut","additinal_settings":{"column_request":"0"}},"column_7":{"db_table___db_column":"product___url_whis_params","additinal_settings":{"column_request":"0"}}},"type_data_general_settings":{"product":{"quantity_default":"","status_enable":"0","seo_url_generator":"0","dis_by_quan":"","categories_filter":"0","manufacturer_filter":"0","prodict_id_from_filter":"","prodict_id_to_filter":"","related_data_column":"1"}},"type_change":"0","name":"\u041f\u0440\u0438\u043c\u0435\u0440 \u044d\u043a\u0441\u043f\u043e\u0440\u0442\u0430","start":"1","limit":"5"},"6112fd6bbd9ebaec82590d2c9bff2f9c":{"format_data":"csv","id":"6112fd6bbd9ebaec82590d2c9bff2f9c","csv_delimiter":"^","csv_enclosure":"~","csv_escape":"","encoding":"UTF-8","log_status":"0","log_details":"0","log_update":"1","log_html":"1","log_file_name":"ocext_log\/ocext_log_csv_import_export","file_upload":"","file_url":"http:\/\/oc2102.ocext.com\/index.php?route=feed\/ocext_feed_generator_yamarket&token=96227","anyxml_xml_upload":"1","xml_specification":"YML_category","export_file_name":"csv_export","file_name_write_time":"0","level":"1","language_id":"1","currency_code":"RUB","store_id":["0"],"export_field_name":{"6112fd6bbd9ebaec82590d2c9bff2f9c":{"yml_catalog__shop__categories__category-id":"yml_catalog__shop__categories__category-id","yml_catalog__shop__categories__category":"yml_catalog__shop__categories__category","yml_catalog__shop__categories__category-parentId":"yml_catalog__shop__categories__category-parentId","0":""}},"type_data":{"yml_catalog__shop__categories__category-id":"category","yml_catalog__shop__categories__category":"category","yml_catalog__shop__categories__category-parentId":"category","0":"0"},"type_data_column":{"yml_catalog__shop__categories__category-id":{"db_table___db_column":"category_identificator___identificator","additinal_settings":{"identificator_insert":"1","identificator_type":"aid","column_request":"0"}},"yml_catalog__shop__categories__category":{"db_table___db_column":"category_description___name","additinal_settings":{"column_request":"0"}},"yml_catalog__shop__categories__category-parentId":{"db_table___db_column":"category___parent_id","additinal_settings":{"column_request":"0"}}},"type_data_general_settings":{"category":{"status_enable":"1","seo_url_generator":"1"}},"type_change":"update_data","name":"\u0418\u043c\u043f\u043e\u0440\u0442 \u043a\u0430\u0442\u0435\u0433\u043e\u0440\u0438\u0439 \u0438\u0437 YML (\u043f\u043e\u0441\u043b\u0435 \u043e\u0431\u0440\u0430\u0431\u043e\u0442\u043a\u0438 anyXML \u0435\u0441\u043b\u0438 \u0443\u0441\u0442\u0430\u043d\u043e\u0432\u043b\u0435\u043d)","start":"1","limit":"30","new_file_upload":"e099fc35596cbe91be6950015b62436e.csv"},"6e3cfc7eb337458723dfa8260eb760a3":{"format_data":"csv","id":"6e3cfc7eb337458723dfa8260eb760a3","csv_delimiter":"^","csv_enclosure":"~","csv_escape":"","encoding":"UTF-8","log_status":"0","log_details":"0","log_update":"1","log_html":"1","log_file_name":"ocext_log\/ocext_log_csv_import_export","file_upload":"","file_url":"http:\/\/oc2102.ocext.com\/index.php?route=feed\/ocext_feed_generator_yamarket&token=96227","anyxml_xml_upload":"1","xml_specification":"YML_offer","export_file_name":"csv_export","file_name_write_time":"0","level":"1","language_id":"1","currency_code":"RUB","store_id":["0"],"export_field_name":{"6e3cfc7eb337458723dfa8260eb760a3":{"yml_catalog__shop__offers":"yml_catalog__shop__offers","yml_catalog__shop__offers__offer-id":"yml_catalog__shop__offers__offer-id","yml_catalog__shop__offers__offer-type":"yml_catalog__shop__offers__offer-type","yml_catalog__shop__offers__offer-available":"yml_catalog__shop__offers__offer-available","yml_catalog__shop__offers__offer-fee":"yml_catalog__shop__offers__offer-fee","yml_catalog__shop__offers__offer":"yml_catalog__shop__offers__offer","yml_catalog__shop__offers__offer__url":"yml_catalog__shop__offers__offer__url","yml_catalog__shop__offers__offer__price":"yml_catalog__shop__offers__offer__price","yml_catalog__shop__offers__offer__currencyId":"yml_catalog__shop__offers__offer__currencyId","yml_catalog__shop__offers__offer__categoryId":"yml_catalog__shop__offers__offer__categoryId","yml_catalog__shop__offers__offer__market_category":"yml_catalog__shop__offers__offer__market_category","yml_catalog__shop__offers__offer__picture":"yml_catalog__shop__offers__offer__picture","yml_catalog__shop__offers__offer__store":"yml_catalog__shop__offers__offer__store","yml_catalog__shop__offers__offer__pickup":"yml_catalog__shop__offers__offer__pickup","yml_catalog__shop__offers__offer__delivery":"yml_catalog__shop__offers__offer__delivery","yml_catalog__shop__offers__offer__delivery-options":"yml_catalog__shop__offers__offer__delivery-options","yml_catalog__shop__offers__offer__delivery-options__option-cost":"yml_catalog__shop__offers__offer__delivery-options__option-cost","yml_catalog__shop__offers__offer__delivery-options__option-days":"yml_catalog__shop__offers__offer__delivery-options__option-days","yml_catalog__shop__offers__offer__delivery-options__option-order-before":"yml_catalog__shop__offers__offer__delivery-options__option-order-before","yml_catalog__shop__offers__offer__delivery-options__option":"yml_catalog__shop__offers__offer__delivery-options__option","yml_catalog__shop__offers__offer__name":"yml_catalog__shop__offers__offer__name","yml_catalog__shop__offers__offer__model":"yml_catalog__shop__offers__offer__model","yml_catalog__shop__offers__offer__description":"yml_catalog__shop__offers__offer__description","yml_catalog__shop__offers__offer__sales_notes":"yml_catalog__shop__offers__offer__sales_notes","yml_catalog__shop__offers__offer__manufacturer_warranty":"yml_catalog__shop__offers__offer__manufacturer_warranty","yml_catalog__shop__offers__offer__adult":"yml_catalog__shop__offers__offer__adult","yml_catalog__shop__offers__offer__cpa":"yml_catalog__shop__offers__offer__cpa","yml_catalog__shop__offers__offer__description_html":"yml_catalog__shop__offers__offer__description_html","0":""}},"type_data":{"yml_catalog__shop__offers":"0","yml_catalog__shop__offers__offer-id":"product","yml_catalog__shop__offers__offer-type":"0","yml_catalog__shop__offers__offer-available":"0","yml_catalog__shop__offers__offer-fee":"0","yml_catalog__shop__offers__offer":"0","yml_catalog__shop__offers__offer__url":"0","yml_catalog__shop__offers__offer__price":"product","yml_catalog__shop__offers__offer__currencyId":"0","yml_catalog__shop__offers__offer__categoryId":"product","yml_catalog__shop__offers__offer__market_category":"product","yml_catalog__shop__offers__offer__picture":"product","yml_catalog__shop__offers__offer__store":"0","yml_catalog__shop__offers__offer__pickup":"0","yml_catalog__shop__offers__offer__delivery":"0","yml_catalog__shop__offers__offer__delivery-options":"0","yml_catalog__shop__offers__offer__delivery-options__option-cost":"0","yml_catalog__shop__offers__offer__delivery-options__option-days":"0","yml_catalog__shop__offers__offer__delivery-options__option-order-before":"0","yml_catalog__shop__offers__offer__delivery-options__option":"0","yml_catalog__shop__offers__offer__name":"product","yml_catalog__shop__offers__offer__model":"product","yml_catalog__shop__offers__offer__description":"product","yml_catalog__shop__offers__offer__sales_notes":"0","yml_catalog__shop__offers__offer__manufacturer_warranty":"0","yml_catalog__shop__offers__offer__adult":"0","yml_catalog__shop__offers__offer__cpa":"0","yml_catalog__shop__offers__offer__description_html":"0","0":"0"},"type_data_column":{"yml_catalog__shop__offers__offer-id":{"db_table___db_column":"product_identificator___identificator","additinal_settings":{"identificator_insert":"1","identificator_type":"ean","column_request":"0"}},"yml_catalog__shop__offers__offer__price":{"db_table___db_column":"product___price_advanced","additinal_settings":{"price_rate":"","price_delta":"","price_around":"0","column_request":"0"}},"yml_catalog__shop__offers__offer__categoryId":{"db_table___db_column":"product___category_id","additinal_settings":{"main_category":"1","column_request":"0"}},"yml_catalog__shop__offers__offer__market_category":{"db_table___db_column":"product___category_whis_path","additinal_settings":{"delimeter":"\/","main_category":"0","all_product_category":"0","column_request":"0"}},"yml_catalog__shop__offers__offer__picture":{"db_table___db_column":"product___images","additinal_settings":{"delimeter":",","image_upload":"1","image_new_path":"\/import_yml\/","image_new_name":"1","column_request":"0"}},"yml_catalog__shop__offers__offer__name":{"db_table___db_column":"product_description___name","additinal_settings":{"column_request":"0"}},"yml_catalog__shop__offers__offer__model":{"db_table___db_column":"product___model","additinal_settings":{"column_request":"0"}},"yml_catalog__shop__offers__offer__description":{"db_table___db_column":"product_description___description","additinal_settings":{"column_request":"0"}}},"type_data_general_settings":{"product":{"quantity_default":"","status_enable":"0","seo_url_generator":"0","dis_by_quan":"","categories_filter":"0","manufacturer_filter":"0","prodict_id_from_filter":"","prodict_id_to_filter":"","related_data_column":"0"}},"type_change":"update_data","name":"\u0418\u043c\u043f\u043e\u0440\u0442 \u0442\u043e\u0432\u0430\u0440\u043e\u0432 \u0438\u0437 YML (\u043f\u043e\u0441\u043b\u0435 \u043e\u0431\u0440\u0430\u0431\u043e\u0442\u043a\u0438 anyXML \u0435\u0441\u043b\u0438 \u0443\u0441\u0442\u0430\u043d\u043e\u0432\u043b\u0435\u043d)","start":"1","limit":"30","new_file_upload":"0c0af19cc31925e658c0e1471d0eb788.csv"}}',TRUE);
                
                if ($this->validate()) {
                    
                    $this->model_tool_csv_ocext_dmpro->editSetting('odmpro', $import_sample_data,TRUE);
                    $this->model_setting_setting->editSetting('odmpro_csv_first_us', array('odmpro_csv_first_us'=>1),TRUE);
                    
                }
                
                $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
                
            }
            
            if($config_odmpro_tamplate_data){
                
                $data['tamplates_data'] = $config_odmpro_tamplate_data;
                
            }
            
            //var_dump($data['tamplates_data'][$tamplate_data_selected_id]);
            
            $default_csv_export_file_name = 'csv_export';
            
            $data['tamplate_data_selected'] = array(
                'name'              =>  $this->language->get('tamplate_data_name_new'),
                'id'                =>  $tamplate_data_selected_id,
                'file_url'          =>  '',
                'file_upload'       =>  '',
                'store_id'          =>  array(0),
                'currency_code'     =>  $this->config->get('config_currency'),
                'currency_code_to'     =>  $this->config->get('config_currency'),
                'language_id'       =>  $this->config->get('config_language_id'),   
                'encoding'          =>  'UTF-8',
                'csv_delimiter'     =>  '^',
                'anyxml_xml_upload' => 0,
                'anyxml_xls_upload' => 0,
                'level' =>  0,
                'log_status' =>  0,
                'log_details'   => 0,
                'log_update'    => 1,
                'log_html'  => 1,
                'log_file_name' =>  'ocext_log/ocext_log_csv_import_export',
                'csv_enclosure'     => '~',
                'csv_escape'        => "\\",
                'export_file_name'  => $default_csv_export_file_name,
                'file_name_write_time'  => 0,
                'anycsv_sinch_supplier_setting_id'=>'',
                'ftp_dir'=>'',
                'ftp_password'=>'',
                'ftp_login'=>'',
		'ba_login'=>'',
                'ba_password'=>'',
		
		
                'anycsv_sinch_supplier_name'=>''
            );
            
            $data['anycsv_sinch_supplier_setting_id'] = '';
            
            $data['anycsv_sinch_supplier_name'] = '';
            
            $anycsv_sinch_supplier = $this->model_tool_csv_ocext_dmpro->getAnycsvSinchSupplier();
            
            $process_history = array();
            
            $data['process_history_info'] = '';
            
            if($anycsv_sinch_supplier['supplier_setting']){
                
                foreach ($anycsv_sinch_supplier['supplier_setting'] as $anycsv_sinch_supplier_value) {
                    
                    $anycsv_sinch_supplier_tamplate_data = $data['tamplate_data_selected'];
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_setting_id'] = $anycsv_sinch_supplier_value['supplier_name'].'___'.$anycsv_sinch_supplier_value['setting_id'];
                    
                    $anycsv_sinch_supplier_tamplate_data['name'] = $anycsv_sinch_supplier_value['title'];
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_cut_columns'] = '';
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_add_logic'] = array();
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_start_page'] = 1;
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_limit_on_page'] = 100;
                    
                    $anycsv_sinch_supplier_tamplate_data['id'] = 0;
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_update_file'] = 1;
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_only_stock_data'] = 0;
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_option_columns'] = array();
                    
                    $data['option_values'] = $this->getOptions();
                    
                    $data['option_values'][0] = array( 'name'=>"feed_column",'option_id'=>'column');
                    
                    ksort($data['option_values']);
                    
                    
                    $anycsv_sinch_supplier_tamplate_data_add_column = array();
                    
                    if(isset($anycsv_sinch_supplier['anycsv_sinch_supplier_tamplate_data_add_column'][$anycsv_sinch_supplier_value['supplier_name']])){
                        
                        $anycsv_sinch_supplier_tamplate_data_add_column = $anycsv_sinch_supplier['anycsv_sinch_supplier_tamplate_data_add_column'][$anycsv_sinch_supplier_value['supplier_name']];
                        
                    }
                    
                    foreach ($anycsv_sinch_supplier_tamplate_data_add_column as $anycsv_sinch_supplier_tamplate_data_add_column) {
                        
                        $anycsv_sinch_supplier_tamplate_data[$anycsv_sinch_supplier_tamplate_data_add_column['field']] = $anycsv_sinch_supplier_tamplate_data_add_column['def'];
                        
                    }
                    
                    $anycsv_sinch_supplier_tamplate_data['anycsv_sinch_supplier_id_greater_than'] = '';
                    
                    $data['tamplates_data'][$anycsv_sinch_supplier_value['supplier_name'].'___'.$anycsv_sinch_supplier_value['setting_id']] = $anycsv_sinch_supplier_tamplate_data;
                    
                    /*
                     * При первом вызове в $data['anycsv_sinch_supplier_setting_id'] нет данных, по этому добавляются
                     */
                    
                    if($tamplate_data_selected_id==$anycsv_sinch_supplier_value['supplier_name'].'___'.$anycsv_sinch_supplier_value['setting_id']){
                        
                        $data['anycsv_sinch_supplier_setting_id'] = $anycsv_sinch_supplier_value['supplier_name'].'___'.$anycsv_sinch_supplier_value['setting_id'];
                        
                        $data['anycsv_sinch_supplier_title'] = $anycsv_sinch_supplier_value['title'];
                        
                        $data['anycsv_sinch_supplier_name'] = $anycsv_sinch_supplier_value['supplier_name'];
                        
                    }
                    
                }
                
            }
            
            $data['encodings'] = $this->model_tool_csv_ocext_dmpro->getIConvCodes(TRUE);
            
            if(isset($data['tamplates_data'][$tamplate_data_selected_id])){
                
                $data['tamplate_data_selected'] = $data['tamplates_data'][$tamplate_data_selected_id];
                
                if(isset($data['tamplate_data_selected']['anycsv_sinch_supplier_setting_id'])){
                
                    $data['anycsv_sinch_supplier_setting_id'] = $data['tamplate_data_selected']['anycsv_sinch_supplier_setting_id'];
                    
                    if($anycsv_sinch_supplier['supplier_setting']){
                
                        foreach ($anycsv_sinch_supplier['supplier_setting'] as $anycsv_sinch_supplier_value) {

                            if($anycsv_sinch_supplier_value['supplier_name'].'___'.$anycsv_sinch_supplier_value['setting_id']==$data['anycsv_sinch_supplier_setting_id']){
                                
                                $data['anycsv_sinch_supplier_title'] = $anycsv_sinch_supplier_value['title'];
                                
                                $data['anycsv_sinch_supplier_name'] = $anycsv_sinch_supplier_value['supplier_name'];
                                
                                if(isset($anycsv_sinch_supplier['process_history'][$data['anycsv_sinch_supplier_name']])){
                                    
                                    $process_history = $anycsv_sinch_supplier['process_history'][$data['anycsv_sinch_supplier_name']];
                                    
                                }
                                
                            }

                        }
                    
                    }
                    
                    if(!isset($data['anycsv_sinch_supplier_title'])){
                        
                        $data['anycsv_sinch_supplier_title'] = "Профиль выключен или удален";
                        
                    }
                    
                }
                
                if(isset($process_history[$tamplate_data_selected_id])){
                    
                    $info_ph = $process_history[$tamplate_data_selected_id]['info'];
                    
                    if(!$info_ph['errors']){
                        
                        $info_ph['errors'] = 'Ошибок не было';
                        
                    }
                    
                    if(!isset($info_ph['process_history_status']) || !$info_ph['process_history_status']){
                        
                        $info_ph['process_history_status'] = 'Последний статус процесса неизвестен';
                        
                    }
                    
                    $productivity = 'Параметры производительности неизвестны';
                    
                    if(isset($info_ph['productivity']) && $info_ph['productivity']){
                        
                        $productivity = "Время ответа сервера: ".$info_ph['productivity']['time_api_response'];
                        $productivity .= ", время предварительно обработки данных одного вызова: ".$info_ph['productivity']['time_preliminary_packaging'];
                        $productivity .= ", время упаковки данных для записи одного вызова: ".$info_ph['productivity']['time_final_packaging'];
                        
                    }
                    
                    $data['process_history_info'] = 'Обработано всего страниц: '.$info_ph['total_count_pages'].', обработано всего позиций: '.$info_ph['offers_filtred'].',<br>создан: '.$info_ph['date_create'].', обновлен: '.$info_ph['last_update'].',<br>ошибки: '.$info_ph['errors'].', Последний статус процесса: '.$info_ph['process_history_status'].'<br>'.$productivity;
                    
                }
                
            }
            
            $data['ocext_dmpro_step_one_settings_sinch_supplier'] = $this->model_tool_csv_ocext_dmpro->getOcextDmproStepOneSettingsSinchSupplier($data['tamplate_data_selected'],$data['anycsv_sinch_supplier_name']);
            
            $data['operators_anysinch'] = array('&lt;'=>'&lt;','≤'=>'≤','='=>'=','≥'=>'≥','&gt;'=>'&gt;','≠'=>'≠','like'=>'Содержит');
            
            $data['filter_fields'] = array();
            
            $data['filter_operators'] = array('&lt;'=>'&lt;','≤'=>'≤','='=>'=','≥'=>'≥','&gt;'=>'&gt;','≠'=>'≠','±'=>'±','like'=>'Содержит','not_like'=>'Не содержит');
            
            $data['filter_actions'] = array(''=>"Выбрать",'skip'=>"Пропускать",'save'=>"Оставлять");
            
            if(isset($data['tamplate_data_selected']['export_field_name'][$tamplate_data_selected_id])){
                
                foreach ($data['tamplate_data_selected']['export_field_name'][$tamplate_data_selected_id] as $export_field_name) {
                    
                    $data['filter_fields'][$export_field_name] = $export_field_name;
                    
                }
                
            }
            
            if(isset($data['tamplate_data_selected']['self_column'])){
                
                foreach ($data['tamplate_data_selected']['self_column'] as $import_self_column_id => $import_self_column) {
                    
                    $data['filter_fields']['self_column___'.$import_self_column_id] = $import_self_column['import_self_column_name'];
                    
                }
                
            }
            
            //поля, которых ранее не было в сохраненных шаблонах
            if(!isset($data['tamplate_data_selected']['anyxls_xls_upload'])){
                
                $data['tamplate_data_selected']['anyxls_xls_upload'] = 0;
                
            }
            
            $data['text_anyxls_status_false'] = '';
            
            $data['xls_specifications'] = array();
            
            if(!$this->anyxls){
                
                $data['text_anyxls_status_false'] = $this->language->get('text_anyxls_status_false');
                
            }else{
                
                $this->load->model('tool/anyxls_ocext_plugin');
            
                $data['xls_specifications'] = $this->model_tool_anyxls_ocext_plugin->getXLSSpecifications();
                
                
            }
            
            $data['text_xls_specification_select'] = $this->language->get('text_xls_specification_select');
            $data['text_xls_specification'] = $this->language->get('text_xls_specification');
            $data['text_anyxls_xls_upload'] = $this->language->get('text_anyxls_xls_upload');
            $data['text_anyxls_count_column'] = $this->language->get('text_anyxls_count_column');
            $data['text_anyxls_count_rows'] = $this->language->get('text_anyxls_count_rows');
            
            //поля, которых ранее не было в сохраненных шаблонах
            if(!isset($data['tamplate_data_selected']['anyxml_xml_upload'])){
                
                $data['tamplate_data_selected']['anyxml_xml_upload'] = 0;
                
            }
            
            $data['text_anyxml_status_false'] = '';
            
            $data['xml_specifications'] = array();
            
            if(!$this->anyxml){
                
                $data['text_anyxml_status_false'] = $this->language->get('text_anyxml_status_false');
                
            }else{
                
                $this->load->model('tool/anyxml_ocext_plugin');
            
                $data['xml_specifications'] = $this->model_tool_anyxml_ocext_plugin->getXMLSpecifications();
                
            }
            $data['text_xml_specification_select'] = $this->language->get('text_xml_specification_select');
            $data['text_xml_specification'] = $this->language->get('text_xml_specification');
            
            
            
            
            //поля, которых ранее не было в сохраненных шаблонах
            if(!isset($data['tamplate_data_selected']['export_file_name'])){
                
                $data['tamplate_data_selected']['export_file_name'] = $default_csv_export_file_name;
                
            }
            if(!isset($data['tamplate_data_selected']['file_name_write_time'])){
                
                $data['tamplate_data_selected']['file_name_write_time'] = 0;
                
            }
            //поля, которые могли остаться пустыми при сохранении шаблона на странице экспорта
            if(!isset($data['tamplate_data_selected']['file_upload'])){
                
                $data['tamplate_data_selected']['file_upload'] = '';
                
            }
            if(!isset($data['tamplate_data_selected']['file_url'])){
                
                $data['tamplate_data_selected']['file_url'] = '';
                
            }
            
            if(!isset($data['tamplate_data_selected']['file_url'])){
                
                $data['tamplate_data_selected']['file_url'] = '';
                
            }
            
            if(!isset($data['tamplate_data_selected']['ftp_dir'])){
                
                $data['tamplate_data_selected']['ftp_dir'] = '';
                $data['tamplate_data_selected']['ftp_login'] = '';
                $data['tamplate_data_selected']['ftp_password'] = '';
                
            }
	    
	    if(!isset($data['tamplate_data_selected']['ba_password'])){
                
                $data['tamplate_data_selected']['ba_password'] = '';
                $data['tamplate_data_selected']['ba_login'] = '';
                
            }
	    
	    
            
            if(!isset($data['tamplate_data_selected']['log_status'])){
                
                $data['tamplate_data_selected']['log_status'] = 0;
                
            }
            
            if(!isset($data['tamplate_data_selected']['log_details'])){
                
                $data['tamplate_data_selected']['log_details'] = 0;
                
            }
            
            if(!isset($data['tamplate_data_selected']['log_html'])){
                
                $data['tamplate_data_selected']['log_html'] = 1;
                
            }
            
            if(!isset($data['tamplate_data_selected']['log_update'])){
                
                $data['tamplate_data_selected']['log_update'] = 1;
                
            }
            
            if(!isset($data['tamplate_data_selected']['csv_escape']) || !$data['tamplate_data_selected']['csv_escape']){
                
                $data['tamplate_data_selected']['csv_escape'] = "\\";
                
            }
            
            
            if(isset($this->setting_version_settings['functional']['edistributier_adaptor']) && $this->setting_version_settings['functional']['edistributier_adaptor']){
            
                //$data['edistributier_adaptor'] = $this->model_tool_csv_ocext_dmpro->getEdistributierAdaptorVeiw('edistributier_adaptor',$data['tamplate_data_selected'],'','');

            }
	    
	    if(isset($this->setting_version_settings['functional']['yml_to_dsv']) && $this->setting_version_settings['functional']['yml_to_dsv']){
            
                $data['yml_setting'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings('yml_setting',$data['tamplate_data_selected'],'','');

            }
            
            
            if(isset($this->setting_version_settings['functional']['php_after_import']) && $this->setting_version_settings['functional']['php_after_import']){
            
                $data['php_after_import'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings('php_after_import',$data['tamplate_data_selected'],$data['type_process'],'');

            }
            
            
            
            
            $data['text_log_details'] = $this->language->get('text_log_details');
            $data['text_anyxml_xml_upload'] = $this->language->get('text_anyxml_xml_upload');
            
            $data['text_log_html'] = $this->language->get('text_log_html');
            $data['text_log_update'] = $this->language->get('text_log_update');
            $data['text_log_title'] = $this->language->get('text_log_title');
            $data['text_log_file_name'] = $this->language->get('text_log_file_name');
            $data['entry_odmpro_tamplate_data_new'] = $this->language->get('entry_odmpro_tamplate_data_new');
            $data['entry_select'] = $this->language->get('entry_select');
            $data['entry_odmpro_tamplate_data'] = $this->language->get('entry_odmpro_tamplate_data');
            $data['entry_odmpro_tamplate_data_empty'] = $this->language->get('entry_odmpro_tamplate_data_empty');
            $data['entry_odmpro_tamplate_data_select'] = $this->language->get('entry_odmpro_tamplate_data_select');
            $data['entry_odmpro_format_data_empty'] = $this->language->get('entry_odmpro_format_data_empty');
            $data['text_info_box_modal_step_1_import_csv'] = $this->language->get('text_info_box_modal_step_1_import_csv');
            $data['text_info_box_modal_step_2_import_csv'] = $this->language->get('text_info_box_modal_step_2_import_csv');
            $data['text_info_box_modal_step_3_import_csv'] = $this->language->get('text_info_box_modal_step_3_import_csv');
            $data['entry_odmpro_csv_delimiter'] = $this->language->get('entry_odmpro_csv_delimiter');
            $data['entry_odmpro_csv_enclosure'] = $this->language->get('entry_odmpro_csv_enclosure');
            $data['entry_odmpro_csv_escape'] = $this->language->get('entry_odmpro_csv_escape');
            $data['entry_odmpro_encoding'] = $this->language->get('entry_odmpro_encoding');
            $data['entry_odmpro_tamplate_data_level_0'] = $this->language->get('entry_odmpro_tamplate_data_level_0');
            $data['entry_odmpro_tamplate_data_level_1'] = $this->language->get('entry_odmpro_tamplate_data_level_1');
            $data['entry_odmpro_tamplate_data_level'] = $this->language->get('entry_odmpro_tamplate_data_level');
            $data['entry_odmpro_language'] = $this->language->get('entry_odmpro_language');
            $data['entry_export_file_name'] = $this->language->get('entry_export_file_name');
            $data['entry_export_file_name_write_time'] = $this->language->get('entry_export_file_name_write_time');
            $data['entry_disable'] = $this->language->get('entry_disable');
            $data['entry_enable'] = $this->language->get('entry_enable');
            $data['text_type_data_ignor'] = $this->language->get('text_type_data_ignor');
            
            
            //$this->load->model('localisation/language');
            
            $languages = $this->model_tool_csv_ocext_dmpro->getLanguages(array('start'=>0,'limit'=>10000));
            
            $data['languages'] = array();
            
            foreach ($languages as $language) {
                    $data['languages'][$language['language_id']] = array(
                            'language_id' => $language['language_id'],
                            'name'        => $language['name'] . (($language['code'] == $this->config->get('config_language')) ? $this->language->get('text_default') : null),
                            'code'        => $language['code']
                    );
            }
            
            $data['entry_odmpro_currency'] = $this->language->get('entry_odmpro_currency');
            
            $this->load->model('localisation/currency');
            
            $currencies = $this->model_localisation_currency->getCurrencies(array('start'=>0,'limit'=>10000));
            
            $data['currencies'] = array();
            
            foreach ($currencies as $currency) {
                    $data['currencies'][$currency['code']] = array(
                            'name'         => $currency['title'] . (($currency['code'] == $this->config->get('config_currency')) ? $this->language->get('text_default') : null),
                            'code'          => $currency['code'],
                    );
            }
            
            $data['entry_odmpro_store'] = $this->language->get('entry_odmpro_store');
            
            $this->load->model('setting/store');
            
            $stores = $this->model_setting_store->getStores();
            
            $data['stores'][] = array('store_id'=>0,'name'=>$this->language->get('entry_odmpro_store_default'));
            
            foreach ($stores as $store) {
                
                $data['stores'][$store['store_id']] = $store;
                
            }
            
            $data['entry_odmpro_file_upload_error_type'] = '';
            
            $extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
            
            $allowed = array();
            
            $filetypes = explode("\n", $extension_allowed);
            
            foreach ($filetypes as $filetype) {
                
                    $allowed[] = trim($filetype);
            }

            if (!in_array('csv', $allowed)) {
                
                    $link_on_setting = $link = $this->url->link('setting/setting', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                    
                    $data['entry_odmpro_file_upload_error_type'] = sprintf($this->language->get('entry_odmpro_file_upload_error_type'),$link_on_setting);
                    
            }
            
            $allowed = array();

            $mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

            $filetypes = explode("\n", $mime_allowed);

            foreach ($filetypes as $filetype) {
                    $allowed[] = trim($filetype);
            }
            
            

            if (!in_array('application/vnd.ms-excel', $allowed)) {
                
                    $link_on_setting = $link = $this->url->link('setting/setting', ''.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                    
                    $data['entry_odmpro_file_upload_error_type'] = sprintf($this->language->get('entry_odmpro_file_upload_error_type'),$link_on_setting);
                   
            }
	    
	    $data['save_template_setting_link'] = $this->url->link($this->path_oc_version.'/'.$data['format_data'].'_ocext_dmpro/saveTemplateSetting', 'tid='.$tamplate_data_selected_id.'&'.$this->token_name.'=' . $this->request->get[$this->token_name], 'SSL');
            
            $data['title_group_id_box'] = $this->language->get('title_group_id_box');
            $data['title_group_id_box_product_data'] = $this->language->get('title_group_id_box_product_data');
            $data['title_group_id_box_vendor_id'] = $this->language->get('title_group_id_box_vendor_id');
            $data['title_group_id_box_vendor_operator'] = $this->language->get('title_group_id_box_vendor_operator');
            $data['title_group_id_box_vendor_value'] = $this->language->get('title_group_id_box_vendor_value');
            $data['title_group_id_box_attrubute_data'] = $this->language->get('title_group_id_box_attrubute_data');
            $data['title_group_id_box_attrubute_data_vendor_id'] = $this->language->get('title_group_id_box_attrubute_data_vendor_id');
            $data['title_group_id_box_attrubute_data_vendor_operator'] = $this->language->get('title_group_id_box_attrubute_data_vendor_operator');
            $data['title_group_id_box_attrubute_data_vendor_value'] = $this->language->get('title_group_id_box_attrubute_data_vendor_value');
            $data['title_group_id_box_disable_type'] = $this->language->get('title_group_id_box_disable_type');
            $data['title_group_id_box_disable_quantity'] = $this->language->get('title_group_id_box_disable_quantity');
            $data['title_group_id_box_disable_price'] = $this->language->get('title_group_id_box_disable_price');
            $data['title_group_id_box_disable_product'] = $this->language->get('title_group_id_box_disable_product');
            $data['title_group_id_box_skip_by_quantity'] = $this->language->get('title_group_id_box_skip_by_quantity');
            $data['title_group_id_box_skip_by_price'] = $this->language->get('title_group_id_box_skip_by_price');
            $data['title_group_id_box_left_prefix'] = $this->language->get('title_group_id_box_left_prefix');
            $data['title_group_id_box_right_prefix'] = $this->language->get('title_group_id_box_right_prefix');
            $data['title_group_id_box_prefix'] = $this->language->get('title_group_id_box_prefix');
            
            $data['title_group_id_box_category_matching_title'] = $this->language->get('title_group_id_box_category_matching_title');
            $data['title_group_id_box_category_matching_csv_column_name'] = $this->language->get('title_group_id_box_category_matching_csv_column_name');
            $data['title_group_id_box_category_matching_csv_delimeter'] = $this->language->get('title_group_id_box_category_matching_csv_delimeter');
            
            $data['operators_group_id_box'] = $this->model_tool_csv_ocext_dmpro->getSqlWhereOperators();
            
            $data['count_group_id_box'] = $this->model_tool_csv_ocext_dmpro->getSettingVersionDataByKey('count_group_id_box');
            
            $data['actions_with_data_group'] = $this->model_tool_csv_ocext_dmpro->getSettingVersionDataByKey('actions_with_data_group');
            
            $data['product_fields_group_id_box'] = $this->model_tool_csv_ocext_dmpro->getColumns('product',$data['tamplate_data_selected'],$data['type_process'],TRUE);
            
            if(isset($data['product_fields_group_id_box']['product'])){
                
                $data['product_fields_group_id_box'] = $data['product_fields_group_id_box']['product'];
                
            }
            
            $data['attributes_group_id_box'] = $this->getAttributes();
            
            if(isset($this->setting_version_settings['functional']['export_to_xls']) && $this->setting_version_settings['functional']['export_to_xls']){

                $data['export_to_xls_one_set'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings('export_to_xls_one_set',$data['tamplate_data_selected'],'','');

            }
            
            
            $data['entry_odmpro_file'] = $this->language->get('entry_odmpro_file');
            $data['entry_odmpro_file_upload'] = $this->language->get('entry_odmpro_file_upload');
            $data['text_odmpro_file_url'] = $this->language->get('text_odmpro_file_url');
            $data['entry_odmpro_file_url'] = $this->language->get('entry_odmpro_file_url');
            $data['text_wite'] = $this->language->get('text_wite');
            $data['entry_next'] = $this->language->get('entry_next');
            $data[$this->token_name] = $this->session->data[$this->token_name];
            
            $data['entry_download_field_to_file'] = $this->language->get('entry_download_field_to_file');
            
            $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_step_one_settings'.$this->ftype, $data));
            
        }
        
        public function getStepTwoSettings() {
            $data['token_name'] = $this->token_name;
            $this->load->model('setting/setting');
            $data['path_oc_version'] = $this->path_oc_version;
            $data['type_process'] = 'import';
            $data['setting_version_settings'] = $this->setting_version_settings;   
            $data['setting_version_functional'] = $this->setting_version_settings['functional'];  
            if(isset($this->request->get['type_process'])){

                $data['type_process'] = $this->request->get['type_process'];

            }
            
            $type_process = $data['type_process'];
            
            $data['errors'] = array();
            
            $odmpro_tamplate_data_id = $this->request->post['odmpro_tamplate_data']['id'];
            
            $format_data = $this->request->post['odmpro_tamplate_data']['format_data'];
            
            $data['format_data'] = $format_data;
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $lic = $this->model_tool_csv_ocext_dmpro->getLincenceStatus();
            
            $data['text_lic_error'] = '';
                
            if(!$lic['status'] && (!isset($lic['error']) || !$lic['error']) ){
                $data['text_lic_error'] = "Продукт не зарегистрирован. Пожалуйста, обратитесь в службу поддержки за получением данных для лицензии";
            }
            elseif(!$lic['status']){
                $data['text_lic_error'] = $lic['error'];
            }
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            
            if($config_odmpro_tamplate_data && $odmpro_tamplate_data_id && isset($config_odmpro_tamplate_data[$odmpro_tamplate_data_id])){
                
                $odmpro_tamplates_data = $config_odmpro_tamplate_data;
                
                $odmpro_tamplate_data = array_merge($odmpro_tamplates_data[$odmpro_tamplate_data_id],$this->request->post['odmpro_tamplate_data']);
                
            }else{
                
                $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
                
            }
            
            if(!isset($odmpro_tamplate_data['start'])){
                
                $odmpro_tamplate_data['start'] = 1;
                
            }
            if(!isset($odmpro_tamplate_data['limit'])){
                
                $odmpro_tamplate_data['limit'] = 30;
                
            }
            
            $data['errors_odmpro_title'] = $this->language->get('errors_odmpro_title');
                
            $data['tamplate_data_selected'] = $odmpro_tamplate_data;
            
            $data['tamplate_data_selected']['new_file_upload'] = '';

            $data['tamplate_data_selected']['getCSVAsHTML'] = '';

            $data['text_getCSVAsHTML'] = $this->language->get('text_getCSVAsHTML');
            
            if($format_data=='csv'){
                
                $this->load->model('tool/csv_ocext_dmpro');
                
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
                
                foreach ($odmpro_tamplate_data as $data_field => $data_value) {
                    
                    if($type_process=='import' && ($data_field=='store_id' && !$data_value) ){
                        
                        $data['errors'][] = $this->language->get('errors_odmpro_store_id');
                        
                    }
                    
                    if( $type_process=='import' &&  ($data_field=='csv_delimiter' || $data_field=='encoding' || $data_field=='currency_code' || $data_field=='language_id') && !$data_value ){
                        
                        $data['errors'][] = $this->language->get('errors_odmpro_'.$data_field);
                        
                    }
                    
                    if($type_process=='import' &&  !$odmpro_tamplate_data['file_url'] && !$odmpro_tamplate_data['file_upload']  && $data_field=='file_url'){
                        
                        $data['errors'][] = $this->language->get('errors_odmpro_file_upload_file_url');
                        
                    }
                    
                    if( (!isset($odmpro_tamplate_data['anyyml_yml_upload']) || !$odmpro_tamplate_data['anyyml_yml_upload']) &&  $type_process=='import' &&  $odmpro_tamplate_data['file_url'] && $data_field=='file_url' ){
                        
                        $new_file_upload = $this->model_tool_csv_ocext_dmpro->getFileByURL($odmpro_tamplate_data['file_url'],FALSE,TRUE,$odmpro_tamplate_data);
                        
                        if(!$new_file_upload){
                            
                            $data['errors'][] = $this->language->get('errors_odmpro_file_url_no_file');
                            
                        }else{
                            
                            $odmpro_tamplate_data['file_upload'] = $new_file_upload;
                        
                            $data['tamplate_data_selected']['new_file_upload'] = $new_file_upload;
                            
                            $odmpro_tamplate_data['file_url'] = '';
                            
                        }
                        
                    }
                    elseif( $type_process=='import' &&  $odmpro_tamplate_data['file_upload'] && $data_field=='file_upload' ){
                        
                        $httpcode = $this->model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['file_upload'],TRUE);
                        
                        if(!$httpcode){
                            
                            $data['errors'][] = $this->language->get('errors_odmpro_file_fail');
                            
                        }
                    }
                }
                if(!isset($odmpro_tamplate_data['store_id'])){
                    
                    $data['errors'][] = $this->language->get('errors_odmpro_store_id');
                    
                }
                
                $data['anyxls_link_on_file'] = '';
                
                if($type_process !=='export' && $this->anyxls && isset($odmpro_tamplate_data['anyxls_xls_upload']) && $odmpro_tamplate_data['anyxls_xls_upload']){
                    
                    $any_XLS_result = $this->getAnyXLSResult($odmpro_tamplate_data);
                    
                    if(isset($any_XLS_result['error']) && $any_XLS_result['error']){
                        
                        $data['errors'][] = $any_XLS_result['error'];
                        
                    }else{
                        
                        $odmpro_tamplate_data['file_url'] = '';
                        
                        $data['yandex_market_categories'] = array();
                        
                        if(isset($any_XLS_result['yandex_market_categories'])){
                            
                            $data['yandex_market_categories'] = $any_XLS_result['yandex_market_categories'];
                            
                            $data['yandex_market_categories']["__Неизвестная категория__"] = "__Неизвестная категория__";
                            
                            $language_id = (int)$this->config->get('config_language_id');
                            
                            if(isset($odmpro_tamplate_data['language_id'])){
                                
                                $language_id = (int)$odmpro_tamplate_data['language_id'];
                                
                            }
                            
                            $data['yandex_market_categories_site_cats'] = $this->model_tool_csv_ocext_dmpro->getCategories('/',$language_id);
                            
                            $data[$this->token_name] = $this->session->data[$this->token_name];
                            
                        }
                        
                        $odmpro_tamplate_data['file_upload'] = $any_XLS_result['file_upload'];
                        
                        $data['tamplate_data_selected']['new_file_upload'] = $any_XLS_result['file_upload'];
                        
                        $data['tamplate_data_selected']['getCSVAsHTML'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getCSVAsHTML', '&file_name='.$any_XLS_result['file_upload'].'&odmpro_tamplate_data_id='.$odmpro_tamplate_data_id.'&'.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                        
                        $data['anyxml_link_on_file'] = HTTP_SERVER.str_replace($_SERVER['DOCUMENT_ROOT'], '', DIR_DOWNLOAD).$any_XLS_result['file_upload'];
                        
                    }
                    
                }
                
                if($type_process !=='export' && $this->anyxml && isset($odmpro_tamplate_data['anyxml_xml_upload']) && $odmpro_tamplate_data['anyxml_xml_upload']){
                    
                    $any_XML_result = $this->getAnyXMLResult($odmpro_tamplate_data);
                    
                    if(isset($any_XML_result['error']) && $any_XML_result['error']){
                        
                        $data['errors'][] = $any_XML_result['error'];
                        
                    }else{
                        
                        $odmpro_tamplate_data['file_url'] = '';
                        
                        $data['yandex_market_categories'] = array();
                        
                        if(isset($any_XML_result['yandex_market_categories'])){
                            
                            $data['yandex_market_categories'] = $any_XML_result['yandex_market_categories'];
                            $data['yandex_market_categories']["__Неизвестная категория__"] = "__Неизвестная категория__";
                            $language_id = (int)$this->config->get('config_language_id');
                            
                            if(isset($odmpro_tamplate_data['language_id'])){
                                
                                $language_id = (int)$odmpro_tamplate_data['language_id'];
                                
                            }
                            
                            $data['yandex_market_categories_site_cats'] = $this->model_tool_csv_ocext_dmpro->getCategories('/',$language_id);
                            
                            $data[$this->token_name] = $this->session->data[$this->token_name];
                            
                        }
                        
                        $odmpro_tamplate_data['file_upload'] = $any_XML_result['file_upload'];
                        
                        $data['tamplate_data_selected']['new_file_upload'] = $any_XML_result['file_upload'];
                        
                        $data['tamplate_data_selected']['getCSVAsHTML'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getCSVAsHTML', '&file_name='.$any_XML_result['file_upload'].'&odmpro_tamplate_data_id='.$odmpro_tamplate_data_id.'&'.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                        
                        $data['anyxml_link_on_file'] = HTTP_SERVER.str_replace($_SERVER['DOCUMENT_ROOT'], '', DIR_DOWNLOAD).$any_XML_result['file_upload'];
                        
                    }
                    
                }
		
                if($type_process=='import' && isset($odmpro_tamplate_data['anyyml_yml_upload']) && $odmpro_tamplate_data['anyyml_yml_upload']){
                    
                    $any_YML_result = $this->getAnyYMLResult($odmpro_tamplate_data);
                    
                    if(isset($any_YML_result['error']) && $any_YML_result['error']){
                        
                        $data['errors'][] = $any_YML_result['error'];
                        
                    }else{
                        
                        $odmpro_tamplate_data['file_url'] = '';
                        
                        $data['yandex_market_categories'] = array();
                        $data['yandex_market_categories']["__Неизвестная категория__"] = "__Неизвестная категория__";
                        if(isset($any_YML_result['yandex_market_categories'])){
                            
                            $data['yandex_market_categories'] = $any_YML_result['yandex_market_categories'];
                            $data['yandex_market_categories']["__Неизвестная категория__"] = "__Неизвестная категория__";
                            $language_id = (int)$this->config->get('config_language_id');
                            
                            if(isset($odmpro_tamplate_data['language_id'])){
                                
                                $language_id = (int)$odmpro_tamplate_data['language_id'];
                                
                            }
                            
                            $data['yandex_market_categories_site_cats'] = $this->model_tool_csv_ocext_dmpro->getCategories('/',$language_id);
                            
                            $data[$this->token_name] = $this->session->data[$this->token_name];
                            
                        }
                        
                        if($any_YML_result['file_upload'] && !is_array($any_YML_result['file_upload'])){
                            
                            $odmpro_tamplate_data['file_upload'] = $any_YML_result['file_upload'];
                        
                            $data['tamplate_data_selected']['new_file_upload'] = $any_YML_result['file_upload'];

                            $data['tamplate_data_selected']['getCSVAsHTML'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getCSVAsHTML', '&file_name='.$any_YML_result['file_upload'].'&odmpro_tamplate_data_id='.$odmpro_tamplate_data_id.'&'.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');

                            $data['anyyml_link_on_file'] = DIR_DOWNLOAD.$any_YML_result['file_upload'];
                            
                        }else{
                            
                            if($any_YML_result['file_upload']){
                                
                                $odmpro_tamplate_data['file_upload'] = $any_YML_result['file_upload'][0];
                                
                                foreach ($any_YML_result['file_upload'] as $file_upload_cache) {
                                
                                    $data['tamplate_data_selected']['new_file_upload'][$file_upload_cache] = $file_upload_cache;

                                    $data['tamplate_data_selected']['getCSVAsHTML'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getCSVAsHTML', '&file_name='.$odmpro_tamplate_data['file_upload'].'&odmpro_tamplate_data_id='.$odmpro_tamplate_data_id.'&'.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');

                                    $data['anyyml_link_on_file'][$file_upload_cache] = DIR_DOWNLOAD.$file_upload_cache;

                                }
                                
                            }else{
                                
                                $odmpro_tamplate_data['file_upload'] = '';
                                
                                $data['tamplate_data_selected']['new_file_upload'] = '';

                                $data['tamplate_data_selected']['getCSVAsHTML'] = '';

                                $data['anyyml_link_on_file'] = '';
                                
                            }
                            
                        }
                        
                        if(!$any_YML_result['max_exe_time']){
                            
                            $any_YML_result['max_exe_time'] = 0.5;
                            
                        }
                        
                        $data['anyyml_max_exe_time'] = $any_YML_result['max_exe_time'].' сек.';
                        
                        $data['anyyml_memory_usage'] = round(($any_YML_result['memory_usage']/1024/1024),3).' Mb'; 
                        
                        $data['anyyml_time_on_item'] = '';
                        
                        if($any_YML_result['count_rows']){
                            $data['anyyml_time_on_item'] = round(($any_YML_result['max_exe_time']/$any_YML_result['count_rows'])*1000,2).' ms на одну строку';
                        }
                        
                    }
                    
                }
                
                $data['anycsv_sinch_link_on_file'] = '';
                
                $data['anycsv_sinch_file_upload'] = '';
                
                if(isset($odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']) && $odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']){
                    
                    if((!isset($odmpro_tamplate_data['anycsv_sinch_file_upload']) || !$odmpro_tamplate_data['anycsv_sinch_file_upload']) || (isset($odmpro_tamplate_data['anycsv_sinch_supplier_update_file']) && $odmpro_tamplate_data['anycsv_sinch_supplier_update_file'])){
                        
                        $status_continuation = 0;
                        
                        if(isset($this->request->get['status_continuation'])){
                            
                            $status_continuation = $this->request->get['status_continuation'];
                            
                        }
                        
                        $any_CSV_Sinc_Supplier_result = $this->getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation);
                        
                    }else{
                        
                        $any_CSV_Sinc_Supplier_result['file_upload'] = $odmpro_tamplate_data['anycsv_sinch_file_upload'];
                        
                        $data['tamplate_data_selected']['new_file_upload'] = $data['anycsv_sinch_file_upload'];
                        
                        $data['tamplate_data_selected']['getCSVAsHTML'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getCSVAsHTML', '&file_name='.$any_CSV_Sinc_Supplier_result['file_upload'].'&odmpro_tamplate_data_id='.$odmpro_tamplate_data_id.'&'.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                        
                        $data['anyxml_link_on_file'] = HTTP_SERVER.str_replace($_SERVER['DOCUMENT_ROOT'], '', DIR_DOWNLOAD).$any_CSV_Sinc_Supplier_result['file_upload'];
                        
                    }
                    
                    if(isset($any_CSV_Sinc_Supplier_result['file_upload']) && $any_CSV_Sinc_Supplier_result['file_upload']){
                        
                        $odmpro_tamplate_data['file_upload'] = $any_CSV_Sinc_Supplier_result['file_upload'];
                        
                        $data['anycsv_sinch_link_on_file'] = HTTP_SERVER.str_replace($_SERVER['DOCUMENT_ROOT'], '', DIR_DOWNLOAD).$odmpro_tamplate_data['file_upload'];
                        
                        $data['anycsv_sinch_file_upload'] = $odmpro_tamplate_data['file_upload'];
                        
                        $data['tamplate_data_selected']['new_file_upload'] = $data['anycsv_sinch_file_upload'];
                        
                        $data['tamplate_data_selected']['getCSVAsHTML'] = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getCSVAsHTML', '&file_name='.$any_CSV_Sinc_Supplier_result['file_upload'].'&odmpro_tamplate_data_id='.$odmpro_tamplate_data_id.'&'.$this->token_name.'=' . $this->session->data[$this->token_name], 'SSL');
                        
                        $data['anyxml_link_on_file'] = HTTP_SERVER.str_replace($_SERVER['DOCUMENT_ROOT'], '', DIR_DOWNLOAD).$any_CSV_Sinc_Supplier_result['file_upload'];
                        
                    }elseif(isset($any_CSV_Sinc_Supplier_result['error']) && $any_CSV_Sinc_Supplier_result['error']){
                        
                        $data['errors'][] = $any_CSV_Sinc_Supplier_result['error'];
                        
                    }
                    
                    //удаляем ошибку, связанную с отсутствием ссылки или файла
                    foreach($data['errors'] as $error_num => $error_text){
                        
                        if($error_text==$this->language->get('errors_odmpro_file_fail') || $error_text==$this->language->get('errors_odmpro_file_url_no_file') || $error_text == $this->language->get('errors_odmpro_file_upload_file_url')){
                            
                            unset($data['errors'][$error_num]);
                            
                        }
                        
                    }
                    
                }
               
                if($data['errors']){
                    
                    return $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_step_two_settings'.$this->ftype, $data));
                    
                }
                
                $file_url_for_view = '';
                
                if($type_process=='import' && $odmpro_tamplate_data['file_url']){
                    
                    $file = $this->model_tool_csv_ocext_dmpro->getFileByURL($odmpro_tamplate_data['file_url'],FALSE,FALSE,$odmpro_tamplate_data);
                    
                    $file_url_for_view = $odmpro_tamplate_data['file_url'];
                    
                }elseif($type_process=='import'){
                    
                    $file = $this->model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['file_upload']);
                    
                    $file_url_for_view = $odmpro_tamplate_data['file_upload'];
                    
                }
                
                if(isset($odmpro_tamplate_data['group_id_box']['category_matching_csv_column_name']) && $odmpro_tamplate_data['group_id_box']['category_matching_csv_column_name']){
                    
                    $yandex_market_categories_for_csv = array();
                    
                    $category_matching_csv_column_name = trim($odmpro_tamplate_data['group_id_box']['category_matching_csv_column_name']);
                    
                    $category_matching_csv_delimeter = $odmpro_tamplate_data['group_id_box']['category_matching_csv_delimeter'];
                    
                    if($odmpro_tamplate_data['file_url']){

                            $yandex_market_categories_for_csv = $this->model_tool_csv_ocext_dmpro->getCsvRows(FALSE,1,100000,$odmpro_tamplate_data,$file_url_for_view,FALSE,10000,'... ...',TRUE,FALSE,$file_url_for_view);

                    }elseif ($odmpro_tamplate_data['file_upload']) {

                            $yandex_market_categories_for_csv = $this->model_tool_csv_ocext_dmpro->getCsvRows(FALSE,1,100000,$odmpro_tamplate_data,'',FALSE,10000,'... ...',TRUE,FALSE,$file_url_for_view);

                    }

                    if(isset($yandex_market_categories_for_csv['field_position']) && in_array($category_matching_csv_column_name,$yandex_market_categories_for_csv['field_position'])){

                        $num_pos_categories = NULL;

                        foreach($yandex_market_categories_for_csv['field_position'] as $nums_pos_categories => $field_position_name){

                                if($field_position_name==$category_matching_csv_column_name){

                                    $num_pos_categories = $nums_pos_categories;

                                }

                        }

                        if(!is_null($num_pos_categories) && isset($yandex_market_categories_for_csv['data'])){

                            $language_id = (int)$this->config->get('config_language_id');

                            if(isset($odmpro_tamplate_data['language_id'])){

                                    $language_id = (int)$odmpro_tamplate_data['language_id'];

                            }

                            $data['yandex_market_categories_site_cats'] = $this->model_tool_csv_ocext_dmpro->getCategories($category_matching_csv_delimeter,$language_id); 

                            $data[$this->token_name] = $this->session->data[$this->token_name];

                            $data['yandex_market_categories'] = array();
$data['yandex_market_categories']["__Неизвестная категория__"] = "__Неизвестная категория__";
                            foreach($yandex_market_categories_for_csv['data'] as $yandex_market_categories_rows){

                                    if(isset($yandex_market_categories_rows[$num_pos_categories]) && !empty($yandex_market_categories_rows[$num_pos_categories])){
                                        
                                        $data['yandex_market_categories'][html_entity_decode($yandex_market_categories_rows[$num_pos_categories])] = html_entity_decode($yandex_market_categories_rows[$num_pos_categories]);
                                        
                                    }

                            }

                            ksort($data['yandex_market_categories']);

                        }

                    }
                    
                }
                
                if(isset($odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']) && $odmpro_tamplate_data['id']==$odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']){
                    
                    $odmpro_tamplate_data['id'] = 0;
                    $data['tamplate_data_selected']['id'] = 0;
                    
                }
		
		
                
                $csv_fields = array();
                
                if($type_process=='import'){
                    
                    $file_for_view = $file;
                    
                    // $text = iconv('utf-8', 'cp1251', $text);
                    
                    if($odmpro_tamplate_data['encoding']!='UTF-8'){
                        
                         //$file_for_view = iconv(mb_detect_encoding((string)$file_for_view), 'utf-8', $file_for_view);
                    }
                
                    $csv_fields = $this->model_tool_csv_ocext_dmpro->getCsvRows($file,0,1,$odmpro_tamplate_data);
                    
                }
                
                $data['csv_data_for_view'] = array();
                
                $data['csv_data_last_row_for_view'] = array();
                
                $start_row_by_add_first_row = 1;
                
                if(isset($odmpro_tamplate_data['add_first_row']) && $odmpro_tamplate_data['add_first_row']){
                    
                    $start_row_by_add_first_row = 0;
                    
                }
                
                if($type_process=='import' && isset($csv_fields['count_rows']) && isset($file_for_view) && $file_for_view){
                    
                    if($odmpro_tamplate_data['file_url']){
                     
                        $data['csv_data_for_view'] = $this->model_tool_csv_ocext_dmpro->getCsvRows(FALSE,$start_row_by_add_first_row,10,$odmpro_tamplate_data,$file_url_for_view,FALSE,100,'... ...',TRUE);
                    
                        $csv_data_last_row_for_view = $this->model_tool_csv_ocext_dmpro->getCsvRows(FALSE,0,0,$odmpro_tamplate_data,$file_url_for_view,FALSE,300,'... ...',TRUE,TRUE);

                        $data['csv_data_last_row_for_view'] = end($csv_data_last_row_for_view['data']);
                        
                    }elseif ($odmpro_tamplate_data['file_upload']) {
                    
                        $data['csv_data_for_view'] = $this->model_tool_csv_ocext_dmpro->getCsvRows(FALSE,$start_row_by_add_first_row,10,$odmpro_tamplate_data,'',FALSE,100,'... ...',TRUE,FALSE,$file_url_for_view);
                    
                        $csv_data_last_row_for_view = $this->model_tool_csv_ocext_dmpro->getCsvRows(FALSE,0,0,$odmpro_tamplate_data,'',FALSE,300,'... ...',TRUE,TRUE,$file_url_for_view);

                        $data['csv_data_last_row_for_view'] = end($csv_data_last_row_for_view['data']);
                        
                    }
                    
                }
                
                if( $type_process=='import' && (!$csv_fields['data'] || !$csv_fields["count_fields"] || !$csv_fields["count_rows"] || !$file) ){
                    
                    $data['errors'][] = $this->language->get('errors_odmpro_file_fail');
                    
                    return $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_step_two_settings'.$this->ftype, $data));
                    
                }
                
                $types_data =  $this->getTypesData();
                
                $data['types_data'] = $types_data['types_data'];
                
                foreach ($data['types_data'] as $type_data => $tmp) {
                    
                    $data['unique_types_data'][$type_data] = array(
                        
                        'aid'=>  sprintf($this->language->get('entry_unique_type_data_aid'), $type_data.'_id'),
                        
                        'name'=>  sprintf($this->language->get('entry_unique_type_data_name'), 'name')
                        
                    );
                    if($type_data=='product'){
                        
                        $data['unique_types_data'][$type_data]['model'] = sprintf($this->language->get('entry_unique_type_data_model'), 'model');
                        
                        //$data['unique_types_data'][$type_data]['sku'] = sprintf($this->language->get('entry_unique_type_data_sku'), 'sku');
                        
                        $data['unique_types_data'][$type_data]['ean'] = sprintf($this->language->get('entry_unique_type_data_ean'), 'ean');
                        
                    }
                }
                
                $data['entry_unique_type_information'] = $this->language->get('entry_unique_type_information');
                
                $data['entry_unique_type_information_column_field'] = $this->language->get('entry_unique_type_information_column_field');
                
                $data['entry_unique_type_information_column_unique_type_data'] = $this->language->get('entry_unique_type_information_column_unique_type_data');
                
		$data['excel_column_name_by_column_name'] = array();
		
                if($type_process=='import'){
                    
                    $data['data_rows'] = $csv_fields['field_position'];
                    
                    if(isset($odmpro_tamplate_data['add_first_row']) && $odmpro_tamplate_data['add_first_row']){
                        
                        foreach ($data['data_rows'] as $add_first_row_name => $tmp) {
                            
                            $data['data_rows'][$add_first_row_name] = $add_first_row_name;
                            
                        }
                        
                    }
                
                    $data['count_fields'] = $csv_fields['count_fields'];

                    $data['count_rows'] = $csv_fields['count_rows'];
                    
                }elseif($type_process=='export'){
                    
                    $data['data_rows'] = array();
                    
                    if(isset($odmpro_tamplate_data['export_field_name'][$odmpro_tamplate_data_id]) && is_array($odmpro_tamplate_data['export_field_name'][$odmpro_tamplate_data_id])){
                        
                        foreach ($odmpro_tamplate_data['export_field_name'][$odmpro_tamplate_data_id] as $type_data_field_export) {
                            
                            $data['data_rows'][] = $type_data_field_export;
                            
                        }
                        
                    }
                    
                }
		/*
		if($type_process=='import' && isset($odmpro_tamplate_data['cut_columns']) && $odmpro_tamplate_data['cut_columns']!==''){
		    
		    $cut_columns = explode('|', $odmpro_tamplate_data['cut_columns']);
		    
		    $cut_columns_names = array();
		    
		    foreach ($cut_columns as $cut_column_name) {
			
			$cut_columns_names[$cut_column_name] = $cut_column_name;
			
		    }
		    
		    foreach($data['data_rows'] as $column_name_for_cut_num => $column_name_for_cut){
			
			if(isset($cut_columns_names[$column_name_for_cut])){
			    
			    unset($data['data_rows'][$column_name_for_cut_num]);
			    
			}
			
		    }
		    
		}
                */
                $column_name_nums = array();
                foreach ($data['data_rows'] as $key_temp => $column_name) {
                    $column_name_parts = explode('_', $column_name);
                    if($column_name_parts[0]=='column' && isset($column_name_parts[1])){
                        $column_name_parts[1] = (int)$column_name_parts[1];
                        $column_name_nums[$column_name_parts[1]] = $column_name_parts[1];
                    }
                }
                $data['column_name_num'] = 1;
                ksort($column_name_nums);
                if($column_name_nums){
                    $data['column_name_num'] = end($column_name_nums)+1;
                }  
		
		if($data['data_rows']){

		    $data['excel_column_name_by_column_name'] = $this->model_tool_csv_ocext_dmpro->getExcelColumnNameByColumnNames($data['data_rows'],$odmpro_tamplate_data);

		}
                
                $data['text_count_fields'] = $this->language->get('text_count_fields');
                $data['text_count_rows'] = $this->language->get('text_count_rows');
                $data['text_column_field_to_file'] = $this->language->get('text_column_field_to_file');
                $data['text_column_param_field_to_file'] = sprintf($this->language->get('text_column_param_field_to_file'),$this->language->get('text_column_param_field_to_file_'.$type_process));
                $data['text_column_param_field_to_file_param'] = $this->language->get('text_column_param_field_to_file_param');
                $data['text_step_3_ending_export'] = $this->language->get('text_step_3_ending_export');
                $data['text_step_3_start_export'] = $this->language->get('text_step_3_start_export');
                $data['text_type_data_category'] = sprintf($this->language->get('text_type_data_category'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_manufacturer'] = sprintf($this->language->get('text_type_data_manufacturer'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_product'] = sprintf($this->language->get('text_type_data_product'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_information'] = sprintf($this->language->get('text_type_data_information'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_filter'] = sprintf($this->language->get('text_type_data_filter'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_filter_group'] = sprintf($this->language->get('text_type_data_filter_group'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_option'] = sprintf($this->language->get('text_type_data_option'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_option_value'] = sprintf($this->language->get('text_type_data_option_value'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_attribute_group'] = sprintf($this->language->get('text_type_data_attribute_group'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_attribute'] = sprintf($this->language->get('text_type_data_attribute'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_review'] = sprintf($this->language->get('text_type_data_review'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['text_type_data_order_product'] = sprintf($this->language->get('text_type_data_order_product'),$this->language->get('text_type_data_type_proccess_'.$type_process));
                $data['entry_odmpro_csv_delimiter'] = $this->language->get('entry_odmpro_csv_delimiter');
                $data['entry_odmpro_csv_enclosure'] = $this->language->get('entry_odmpro_csv_enclosure');
                $data['entry_odmpro_csv_escape'] = $this->language->get('entry_odmpro_csv_escape');
                $data['entry_odmpro_encoding'] = $this->language->get('entry_odmpro_encoding');
                $data['entry_anyxml_link_on_file'] = $this->language->get('entry_anyxml_link_on_file');
                $data['entry_anyxls_link_on_file'] = $this->language->get('entry_anyxls_link_on_file');
                
                
                $data['text_step_2_synchronization'] = $this->language->get('text_step_2_synchronization');
                $data['text_type_data_ignor'] = $this->language->get('text_type_data_ignor');
                $data['text_type_data_hide_column'] = $this->language->get('text_type_data_hide_column');
                $data['tamplate_data_name_new'] = $this->language->get('tamplate_data_name_new');
                $data['entry_odmpro_tamplate_data_save'] = $this->language->get('entry_odmpro_tamplate_data_save');
                $data['entry_odmpro_tamplate_data_delete'] = $this->language->get('entry_odmpro_tamplate_data_delete');
                $data['entry_odmpro_tamplate_data_update'] = $this->language->get('entry_odmpro_tamplate_data_update');
                $data['entry_odmpro_tamplate_data_done'] = $this->language->get('entry_odmpro_tamplate_data_done');
                $data['entry_odmpro_tamplate_data_save_tamplate_data'] = $this->language->get('entry_odmpro_tamplate_data_save_tamplate_data');
                $data['entry_odmpro_tamplate_type_save'] = $this->language->get('entry_odmpro_tamplate_type_save');
                $data['entry_odmpro_tamplate_data_select'] = $this->language->get('entry_odmpro_tamplate_data_select');
                
                
                $data['text_step_3_ending'] = $this->language->get('text_step_3_ending');
                $data['text_step_3_start_import'] = $this->language->get('text_step_3_start_import');
                $data['text_step_3_start'] = $this->language->get('text_step_3_start');
                $data['text_step_3_limit'] = $this->language->get('text_step_3_limit');
                $data['entry_odmpro_tamplate_data'] = $this->language->get('entry_odmpro_tamplate_data');
                $data['entry_odmpro_tamplate_data_name'] = $this->language->get('entry_odmpro_tamplate_data_name');
                $data['entry_type_change'] = $this->language->get('entry_type_change');
                $data['text_check_row'] = $this->language->get('text_check_row');
                $data['text_check_row_info'] = $this->language->get('text_check_row_info');
                $data['text_check_row_empty'] = $this->language->get('text_check_row_empty');
                
                
                $data['types_change'] = array(
                    'new_data'  =>  $this->language->get('entry_type_change_new_data'),
                    'update_data'  =>  $this->language->get('entry_type_change_update_data'),
                    'only_update_data'  =>  $this->language->get('entry_type_change_only_update_data'),
                    'only_new_data' => $this->language->get('entry_type_change_only_new_data'),
                );
                
                $data['self_column'] = '';
                
                if(isset($this->setting_version_settings['functional']['self_column']) && $this->setting_version_settings['functional']['self_column']){
                    
                    $data['self_column'] = $this->model_tool_csv_ocext_dmpro->getSelfColumn($odmpro_tamplate_data,$types_data['types_data'],$type_process);
                    
                }
                
                $data['entry_select'] = $this->language->get('entry_select');
                
                return $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_step_two_settings'.$this->ftype, $data));
            }
        }
        
        public function getLastLogData() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $last_log_data = $this->model_tool_csv_ocext_dmpro->getLastLogData();
            
            echo json_encode($last_log_data);
            
        }
        
        public function getSmartExchangeCheckConnect() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $getSmartExchangeCheckConnect = $this->model_tool_csv_ocext_dmpro->getSmartExchangeCheckConnect();
            
            echo $getSmartExchangeCheckConnect;
            
        }
        
        public function getActionTask() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $task_id = $this->request->get['task_id'];
            
            $action_status_id = $this->request->get['action_status_id'];
            
            $getActionTask = $this->model_tool_csv_ocext_dmpro->getActionTask($task_id,$action_status_id);
            
            echo $getActionTask;
            
        }
        
        
        
        public function getTypesDataGeneralSettingFields($types_data=array(),$odmpro_tamplate_data=array(),$type_process='import') {
            
            $additinal_settings = array();
            
            if($types_data && $odmpro_tamplate_data){
                
                $j = 0;
                
                $this->load->model('tool/csv_ocext_dmpro');
                
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
                
                $general_settings_fields = $this->model_tool_csv_ocext_dmpro->getGeneralSettingsFields();
                
                foreach ($types_data as $field => $type_data) {
                    
                    if(isset($general_settings_fields[$type_data]) && $general_settings_fields[$type_data]['additinal_settings']){
                        
                        $help_added = FALSE;
                        
                        foreach ($general_settings_fields[$type_data]['additinal_settings'] as $additinal_column=>$additinal_column_param) {

                            $additinal_settings[$type_data][$j]['help'] = '';
                            
                            if(isset($general_settings_fields[$type_data]['help']) && $general_settings_fields[$type_data]['help'] && !$help_added){

                                $additinal_settings[$type_data][$j]['help'] = $general_settings_fields[$type_data]['help'];
                                
                                $help_added = TRUE;

                            }

                            $additinal_settings[$type_data][$j]['onchange'] = '';

                            if(isset($additinal_column_param['onchange']) && $additinal_column_param['onchange']){

                                $additinal_settings[$type_data][$j]['onchange'] = $additinal_column_param['onchange'];

                            }

                            $additinal_settings[$type_data][$j]['style'] = '';

                            if(isset($additinal_column_param['style']) && $additinal_column_param['style']){

                                $additinal_settings[$type_data][$j]['style'] = $additinal_column_param['style'];

                            }
                            
                            $additinal_settings[$type_data][$j]['hide_this_additinal_data'] = 0;
                                    
                            if(isset($additinal_column_param['export']) && !$additinal_column_param['export'] && $type_process=='export'){

                                $additinal_settings[$type_data][$j]['hide_this_additinal_data'] = 1;

                            }
                            
                            if(isset($additinal_column_param['import']) && !$additinal_column_param['import'] && $type_process=='import'){

                                $additinal_settings[$type_data][$j]['hide_this_additinal_data'] = 1;

                            }

                            $additinal_settings[$type_data][$j]['class'] = '';

                            if(isset($additinal_column_param['class']) && $additinal_column_param['class']){

                                $additinal_settings[$type_data][$j]['class'] = $additinal_column_param['class'];

                            }

                            $additinal_settings[$type_data][$j]['id'] = '';

                            if(isset($additinal_column_param['id']) && $additinal_column_param['id']){

                                $additinal_settings[$type_data][$j]['id'] = $additinal_column_param['id'];

                            }

                            $additinal_settings[$type_data][$j]['name'] = 'odmpro_tamplate_data[type_data_general_settings]['.$type_data.']['.$additinal_column.']';

                            $additinal_settings[$type_data][$j]['placeholder'] = '';

                            if(isset($additinal_column_param['placeholder']) && $additinal_column_param['placeholder']){

                                $additinal_settings[$type_data][$j]['placeholder'] = $additinal_column_param['placeholder'];

                            }

                            $additinal_settings[$type_data][$j]['data-original-title'] = '';

                            if(isset($additinal_column_param['data-original-title']) && $additinal_column_param['data-original-title']){

                                $additinal_settings[$type_data][$j]['data-original-title'] = $additinal_column_param['data-original-title'];

                            }


                            $additinal_settings[$type_data][$j]['element'] = $additinal_column_param['element'];

                            if($additinal_settings[$type_data][$j]['element']=='input'){

                                $additinal_settings[$type_data][$j]['type'] = 'text';

                                if(isset($additinal_column_param['type']) && $additinal_column_param['type']){

                                    $additinal_settings[$type_data][$j]['type'] = $additinal_column_param['type'];

                                }

                                if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]){

                                    $additinal_settings[$type_data][$j]['value'] = $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column];

                                }else{
                                    
                                    $additinal_settings[$type_data][$j]['value'] = '';

                                    if(isset($additinal_column_param['default_value']) && $additinal_column_param['default_value']){

                                        $additinal_settings[$type_data][$j]['value'] = $additinal_column_param['default_value'];

                                    }

                                }

                            }
                            /*
                             * У select'ов свой набор options
                             */
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='status_enable'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<3;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_status_enable_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && ($additinal_column=='stock_status_id_by_price' || $additinal_column=='stock_status_id_by_quantity' ) ){

                                $additinal_settings[$type_data][$j]['element'] = 'select';
                                
                                //$additinal_settings[$type_data][$j]['multiple'] = 'multiple';
                                
                                $this->load->model('localisation/stock_status');
                                $fiter_stock_status = array(); 
                                $results = $this->model_localisation_stock_status->getStockStatuses($fiter_stock_status);
                                $options = array();
                                foreach ($results as $result) {
                                    $options[$result['stock_status_id']] = array(
                                            'stock_status_id' => $result['stock_status_id'], 
                                            'name'        => $result['name']
                                    );
                                }

                                //$additinal_settings[$type_data][$j]['name'] = 'odmpro_tamplate_data[type_data_general_settings]['.$type_data.']['.$additinal_column.'][]';
                                
                                $i = 0;
                                
                                $additinal_settings[$type_data][$j]['options'][$i]['value'] = '';
                                $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_select');
                                $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                
                                if(!$options){
                                    //entry_select
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_empty');
                                }

                                $i++;

                                foreach ($options as $option) {

                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $option['stock_status_id'];

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $option['name'];

                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column] == $option['stock_status_id']){

                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';

                                    }else{

                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';

                                    }
                                    $i++;
                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='no_csv_headers'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<2;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_endis_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='skip_by_no_image'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<2;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_endis_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && ( $additinal_column=='image_upload_curl' || $additinal_column=='image_new_dir' )){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<2;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_endis_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='seller_id'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';
                                
                                $results = $this->db->query(' SELECT * FROM '. DB_PREFIX .'ms_seller ');
                                $options = array();
                                foreach ($results->rows as $result) {
                                    $options[$result['seller_id']] = array(
                                            'seller_id' => $result['seller_id'], 
                                            'name'        => $result['nickname'].' / seller_id = '.$result['seller_id']
                                    );
                                }
                                
                                $i = 0;
                                
                                $additinal_settings[$type_data][$j]['options'][$i]['value'] = 0;

                                if($options){

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_all');

                                }else{
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_empty');

                                }

                                $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';

                                $i++;

                                foreach ($options as $option) {

                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $option['seller_id'];

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $option['name'];

                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column] == $option['seller_id']){

                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';

                                    }else{

                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';

                                    }
                                    $i++;
                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='checkbox' && $additinal_column=='categories_filter'){

                                $additinal_settings[$type_data][$j]['element'] = 'checkbox';
                                
                                $this->load->model('catalog/category');
                                $filter_categories_data = array();
                                $results = $this->model_catalog_category->getCategories($filter_categories_data);
                                $options = array();
                                foreach ($results as $result) {
                                    $options[$result['category_id']] = array(
                                            'category_id' => $result['category_id'], 
                                            'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                                    );
                                }

                                $additinal_settings[$type_data][$j]['name'] = 'odmpro_tamplate_data[type_data_general_settings]['.$type_data.']['.$additinal_column.']';
                                
                                $i = 0;
                                
                                $additinal_settings[$type_data][$j]['options'][$i]['value'] = 0;

                                if($options){

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_all');

                                }else{
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_empty');

                                }

                                $additinal_settings[$type_data][$j]['options'][$i]['checked'] = '';
                                
                                if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && is_array($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column][0])){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['checked'] = ' checked="checked" ';
                                    
                                }

                                $i++;

                                foreach ($options as $option) {

                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $option['category_id'];

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $option['name'];

                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && 
					    ( ( is_array($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column][$option['category_id']]) )
					      || $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column] == $option['category_id'] ) ){
					  
                                        $additinal_settings[$type_data][$j]['options'][$i]['checked'] = ' checked="checked" ';

                                    }else{

                                        $additinal_settings[$type_data][$j]['options'][$i]['checked'] = '';

                                    }
                                    $i++;
                                }

                            }
                            
                            elseif($additinal_settings[$type_data][$j]['element']=='checkbox' && $additinal_column=='manufacturer_filter'){

                                $additinal_settings[$type_data][$j]['element'] = 'checkbox';
                                
                                $filter_manufacturer = array('sort'=>'name');
                                $this->load->model('catalog/manufacturer');
                                $manufacturers = $this->model_catalog_manufacturer->getManufacturers($filter_manufacturer);
                                $options = array();
                                foreach ($manufacturers as $result) {
                                    $options[$result['manufacturer_id']] = array(
                                            'manufacturer_id' => $result['manufacturer_id'], 
                                            'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                                    );
                                }

                                $additinal_settings[$type_data][$j]['name'] = 'odmpro_tamplate_data[type_data_general_settings]['.$type_data.']['.$additinal_column.']';
                                
                                $i = 0;
                                
                                $additinal_settings[$type_data][$j]['options'][$i]['value'] = 0;

                                if($options){

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_all');

                                }else{
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_empty');

                                }

                                $additinal_settings[$type_data][$j]['options'][$i]['checked'] = '';
                                
                                if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && is_array($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column][0])){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['checked'] = ' checked="checked" ';
                                    
                                }

                                $i++;

                                foreach ($options as $option) {

                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $option['manufacturer_id'];

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $option['name'];

                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && 
					    ( ( is_array($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column][$option['manufacturer_id']]) )
					      || $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column] == $option['manufacturer_id'] ) ){
					  
                                        $additinal_settings[$type_data][$j]['options'][$i]['checked'] = ' checked="checked" ';

                                    }else{

                                        $additinal_settings[$type_data][$j]['options'][$i]['checked'] = '';

                                    }
                                    $i++;
                                }

                            }
                            
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='manufacturer_filter'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';
                                
                                //$additinal_settings[$type_data][$j]['multiple'] = 'multiple';
                                $filter_manufacturer = array('sort'=>'name');
                                $this->load->model('catalog/manufacturer');
                                $manufacturers = $this->model_catalog_manufacturer->getManufacturers($filter_manufacturer);
                                $options = array();
                                foreach ($manufacturers as $result) {
                                    $options[$result['manufacturer_id']] = array(
                                            'manufacturer_id' => $result['manufacturer_id'], 
                                            'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                                    );
                                }

                                //$additinal_settings[$type_data][$j]['name'] = 'odmpro_tamplate_data[type_data_general_settings]['.$type_data.']['.$additinal_column.'][]';
                                
                                $i = 0;
                                
                                $additinal_settings[$type_data][$j]['options'][$i]['value'] = 0;

                                if($options){

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_all');

                                }else{
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_empty');

                                }

                                $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';

                                $i++;

                                foreach ($options as $option) {

                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $option['manufacturer_id'];

                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $option['name'];

                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column] == $option['manufacturer_id']){

                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';

                                    }else{

                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';

                                    }
                                    $i++;
                                }

                            }
                            
                            
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && ($additinal_column=='seo_url_generator' || $additinal_column=='delete_attributes_before_import' || $additinal_column=='delete_options_before_import' || $additinal_column=='delete_specials_before_import' || $additinal_column=='delete_discounts_before_import' || $additinal_column=='delete_categories_before_import' ||  $additinal_column == 'quick_stock_update') ){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<2;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_endis_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='dis_by_quan'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<2;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_endis_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='related_data_column'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=1;$i<3;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_related_data_column_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='today_filter'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<4;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_today_filter_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='select' && $additinal_column=='image_crop'){

                                $additinal_settings[$type_data][$j]['element'] = 'select';

                                for($i=0;$i<2;$i++){
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['value'] = $i;
                                    
                                    $additinal_settings[$type_data][$j]['options'][$i]['text'] = $this->language->get('type_data_general_setting_endis_'.$i);
                                    
                                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data][$additinal_column]==$i){
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = 'selected=""';
                                        
                                    }else{
                                        
                                        $additinal_settings[$type_data][$j]['options'][$i]['selected'] = '';
                                        
                                    }

                                }
                                
                                $additinal_settings[$type_data][$j]['advanced_setting'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings($additinal_column,$odmpro_tamplate_data,$type_process,$type_data);

                            }
                            elseif($additinal_settings[$type_data][$j]['element']=='custom' && $additinal_column=='export_where_rules'){

                                $additinal_settings[$type_data][$j]['element'] = 'custom';
                                
                                $additinal_settings[$type_data][$j]['export_where_rules'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings($additinal_column,$odmpro_tamplate_data,$type_process,$type_data);

                            }
                            
                            elseif($additinal_settings[$type_data][$j]['element']=='custom' && $additinal_column=='export_where_product_rules'){

                                $additinal_settings[$type_data][$j]['element'] = 'custom';
                                
                                $additinal_settings[$type_data][$j]['export_where_product_rules'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings('export_where_rules',$odmpro_tamplate_data,$type_process,'product');

                            }
                            
                            elseif($additinal_settings[$type_data][$j]['element']=='custom' && $additinal_column=='export_where_order_rules'){

                                $additinal_settings[$type_data][$j]['element'] = 'custom';
                                
                                $additinal_settings[$type_data][$j]['export_where_order_rules'] = $this->model_tool_csv_ocext_dmpro->getAdvancedSettings('export_where_rules',$odmpro_tamplate_data,$type_process,'order');

                            }
                            
                            $j++;
                            
                            
                                    
                        }
                        
                    }
                    
                }
                
            }
            
            return $additinal_settings;
            
        }
        
        public function getTypesDataGeneralSetting() {
            
            $data = array();
            $data['setting_version_settings'] = $this->setting_version_settings;   
            $data['setting_version_functional'] = $this->setting_version_settings['functional'];  
            $data['type_process'] = 'import';
            
            if(isset($this->request->get['type_process'])){

                $data['type_process'] = $this->request->get['type_process'];

            }
            
            $odmpro_tamplate_data_id = $this->request->post['odmpro_tamplate_data']['id'];
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            
            if(isset($config_odmpro_tamplate_data[$odmpro_tamplate_data_id])){
                
                $odmpro_tamplates_data = $config_odmpro_tamplate_data;
                
                $odmpro_tamplate_data = array_merge($odmpro_tamplates_data[$odmpro_tamplate_data_id],$this->request->post['odmpro_tamplate_data']);
                
                //$odmpro_tamplate_data = $odmpro_tamplates_data[$odmpro_tamplate_data_id];
                
            }else{
                
                $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
                
            }
            
            
            
            $type_data_all = array();
            
            $types_data = array();
            
            if(isset($odmpro_tamplate_data['type_data'])){
                
                $type_data_all = $odmpro_tamplate_data['type_data'];
                
            }
            
            foreach ($type_data_all as $field => $type_data){
                
                if($type_data){
                    
                    $types_data[$type_data]=$type_data;
                    
                }
                
            }
            
            foreach ($types_data as $field => $type_data) {
                
                $data['entry_types_data_general_setting_'.$type_data] = $this->language->get('entry_types_data_general_setting_'.$type_data);
                
            }
            
            $data['types_data_general_setting'] = $this->getTypesDataGeneralSettingFields($types_data, $odmpro_tamplate_data, $data['type_process']);
            
            $data['tamplate_data_selected'] = $odmpro_tamplate_data;
            
            return $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_types_data_general_setting'.$this->ftype, $data));
        }
        
        public function getTypesData($only_type_data=FALSE) {
            
            $data = array();
            $data['setting_version_settings'] = $this->setting_version_settings;   
            $data['setting_version_functional'] = $this->setting_version_settings['functional'];  
            $ajax = FALSE;
            
            $data['type_process'] = 'import';
            
            if(isset($this->request->get['type_process'])){

                $data['type_process'] = $this->request->get['type_process'];

            }
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $odmpro_tamplate_data_id = $this->request->post['odmpro_tamplate_data']['id'];
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            
            if(isset($config_odmpro_tamplate_data[$odmpro_tamplate_data_id])){
                
                $odmpro_tamplates_data = $config_odmpro_tamplate_data;
                
                $odmpro_tamplate_data = array_merge($odmpro_tamplates_data[$odmpro_tamplate_data_id],$this->request->post['odmpro_tamplate_data']);
                
            }else{
                
                $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
                
            }
            
            if(isset($this->request->get['type_data'])){
                
                $type_data = $this->request->get['type_data'];
                
                $field = $this->request->get['field'];
                
                $ajax = TRUE;
                
            }
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            if($data['type_process']=='import'){
                
                $data['types_data']['category'] = $this->model_tool_csv_ocext_dmpro->getColumns('category',$odmpro_tamplate_data,$data['type_process']);

                //$data['types_data']['option_value'] = $this->model_tool_csv_ocext_dmpro->getColumns('option_value',$odmpro_tamplate_data);
                
                //$data['types_data']['attribute_group'] = $this->model_tool_csv_ocext_dmpro->getColumns('attribute_group',$odmpro_tamplate_data);

                //$data['types_data']['attribute'] = $this->model_tool_csv_ocext_dmpro->getColumns('attribute',$odmpro_tamplate_data);

                $data['types_data']['product'] = $this->model_tool_csv_ocext_dmpro->getColumns('product',$odmpro_tamplate_data,$data['type_process']);
                
                $data['types_data']['manufacturer'] = $this->model_tool_csv_ocext_dmpro->getColumns('manufacturer',$odmpro_tamplate_data,$data['type_process']);

                //$data['types_data']['filter'] = $this->model_tool_csv_ocext_dmpro->getColumns('filter',$odmpro_tamplate_data);

                //$data['types_data']['filter_group'] = $this->model_tool_csv_ocext_dmpro->getColumns('filter_group',$odmpro_tamplate_data);
                
                $data['types_data']['review'] = $this->model_tool_csv_ocext_dmpro->getColumns('review',$odmpro_tamplate_data,$data['type_process']);
                
            }elseif($data['type_process']=='export'){
                
                $data['types_data']['product'] = $this->model_tool_csv_ocext_dmpro->getColumns('product',$odmpro_tamplate_data,$data['type_process']);
                
                if(isset($this->setting_version_settings['functional']['order_export'])){
                    
                    $data['types_data']['order_product'] = $this->model_tool_csv_ocext_dmpro->getColumns('order_product',$odmpro_tamplate_data,$data['type_process']);
                    
                }
                
            }

            if($data['types_data']){

                foreach ($data['types_data'] as $key_type_data => $value_type_data) {
                    
                    foreach ($value_type_data as $key_value_type_data => $value_value_type_data) {
                        
                        $data['types_data_option_group_name_'.$key_value_type_data] = $this->language->get('types_data_option_group_name_'.$key_value_type_data);
                        
                    }

                }

            }
            
            if(!$ajax || $only_type_data){
                
                return $data;
                
            }else{
                
                $data['type_data'] = $type_data;
                
                $data['field'] = trim($field);
                
                $this->load->model('tool/csv_ocext_dmpro');
                    
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');

                $columns = array();

                if($type_data){

                    $columns = $this->model_tool_csv_ocext_dmpro->getColumns($type_data,$odmpro_tamplate_data,$data['type_process']);

                }

                $data['columns'] = $columns;

                $data['entry_select'] = $this->language->get('entry_select');
                
                $data['entry_odmpro_delimiter'] = $this->language->get('entry_odmpro_delimiter');
                
                $data['text_column_type_data_column_image_upload'] = $this->language->get('text_column_type_data_column_image_upload');
                
                $data['text_column_type_data_column_image_upload_no'] = $this->language->get('text_column_type_data_column_image_upload_no');

                $data['tamplate_data_selected'] = $odmpro_tamplate_data;
                
                return $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_types_data'.$this->ftype, $data));
            }
        }
        
        public function getXPathResult() {
            
            $result = '';
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $url = $this->request->post['url'];
            
            $x_path = $this->request->post['x_path'];
            
            if ($this->validate()) {
            
                $params['xpath_stags'] = $this->request->post['xpath_stags'];

                $x_path_file = $this->model_tool_csv_ocext_dmpro->getCacheFileByURL($url);

                if($x_path_file){

                    $result = $this->model_tool_csv_ocext_dmpro->getXPathResult($x_path_file,$x_path,$params);

                }
            
            }
            
            if($result===''){
                
                $result = "Результат не получен";
                
            }else{
                
                $result = htmlentities($result);
                
            }
            
            echo $result;
            
        }
        
        public function getTypesDataSelfColumns() {
            
            $type_process = 'import';
            
            if(isset($this->request->get['type_process'])){

                $type_process = $this->request->get['type_process'];

            }
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $odmpro_tamplate_data_id = $this->request->post['odmpro_tamplate_data']['id'];
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            
            if(isset($config_odmpro_tamplate_data[$odmpro_tamplate_data_id])){
                
                $odmpro_tamplates_data = $config_odmpro_tamplate_data;
                
                if(isset($odmpro_tamplates_data[$odmpro_tamplate_data_id]['self_column'])){
                    
                    $self_column = $odmpro_tamplates_data[$odmpro_tamplate_data_id]['self_column'];
                    
                    foreach ($self_column as $self_column_id => $self_column_data) {
                        
                        foreach ($self_column_data as $self_column_data_type => $self_column_data_value) {
                         
                            if(!isset($this->request->post['odmpro_tamplate_data']['self_column'][$self_column_id][$self_column_data_type])){
                                
                                $this->request->post['odmpro_tamplate_data']['self_column'][$self_column_id][$self_column_data_type] = $self_column_data_value;
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
                $odmpro_tamplate_data = array_merge($odmpro_tamplates_data[$odmpro_tamplate_data_id],$this->request->post['odmpro_tamplate_data']);
                
            }else{
                
                $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
                
            }
            
            $type_data = '';
            
            $self_column_id = '';
            
            if(isset($this->request->get['type_data'])){
                
                $type_data = $this->request->get['type_data'];
                
                $self_column_id = $this->request->get['self_column_id'];
                
            }
            
            $this->load->model('tool/csv_ocext_dmpro');

            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');

            $columns = array();

            if($type_data){

                $columns = $this->model_tool_csv_ocext_dmpro->getColumns($type_data,$odmpro_tamplate_data,$type_process);
                
                //$export_columns = $this->model_tool_csv_ocext_dmpro->getColumns($type_data,$odmpro_tamplate_data,$type_process,TRUE);
                
                $export_columns = $columns;
                
                return $this->model_tool_csv_ocext_dmpro->getTypesDataSelfColumns($odmpro_tamplate_data,$type_process,$type_data,$columns,$self_column_id,$export_columns);

            }
            
            return '';
                
        }
        
        public function getTypesDataColumnAdditional() {
            
            $data = array();
            $data['setting_version_settings'] = $this->setting_version_settings;   
            $data['setting_version_functional'] = $this->setting_version_settings['functional'];  
            $data['type_process'] = 'import';
            
            if(isset($this->request->get['type_process'])){

                $data['type_process'] = $this->request->get['type_process'];

            }
            
            $db_table___db_column = $this->request->get['db_table___db_column'];
            
            $db_table___db_column_parts = explode('___', $db_table___db_column);
            
            $db_table = $db_table___db_column_parts[0];
            
            /*
             * $type_data теперь $db_table
             */
            
            /*
             * $type_data_column теперь $db_column
             */
            
            $db_column = '';
            
            if(isset($db_table___db_column_parts[1])){
                
                $db_column = $db_table___db_column_parts[1];
                
            }
            
            $field = trim($this->request->get['field']);
            
            $odmpro_tamplate_data_id = $this->request->post['odmpro_tamplate_data']['id'];
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            
            if(isset($config_odmpro_tamplate_data[$odmpro_tamplate_data_id])){
                
                $odmpro_tamplates_data = $config_odmpro_tamplate_data;
                
                //$odmpro_tamplate_data = array_merge($odmpro_tamplates_data[$odmpro_tamplate_data_id],$this->request->post['odmpro_tamplate_data']);
                
                $odmpro_tamplate_data = $odmpro_tamplates_data[$odmpro_tamplate_data_id];
                
                
            }else{
                
                $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
                
            }
            
            $language_id = $odmpro_tamplate_data['language_id'];
            
            $fields = array();
            
            foreach ($odmpro_tamplate_data['type_data'] as $field_this => $tmp) {
                
                $fields[$field_this] = $field_this;
                
            }
            
            $data['db_table'] = $db_table;
            
            $data['field'] = trim($field);
            
            if($field!==''){
                
                $this->load->model('tool/csv_ocext_dmpro');
                
                $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
                
                $additinal_settings = array();
                
                $abstract_field = $this->model_tool_csv_ocext_dmpro->getAbstractFields();
                /*
                 * все расширенные настройки должны быть заданые в $abstract_field в additinal_settings
                 */
                
                $j = 0;
                
                foreach ($abstract_field as $db_table_key => $db_columns_row) {
                    
                    if($db_table_key==$db_table){
                        
                        foreach ($db_columns_row as $db_column_key => $db_column_params) {
                            
                            if(!isset($db_column_params[$data['type_process']]) || (isset($db_column_params[$data['type_process']]) && $db_column_params[$data['type_process']]) ){
                                
                                /*
                                 * Создание дополнительных настроек
                                 */
                                if($db_column==$db_column_params['field'] && isset($db_column_params['additinal_settings']) && $db_column_params['additinal_settings']){

                                    $help_added = FALSE;

                                    foreach ($db_column_params['additinal_settings'] as $additinal_column=>$additinal_column_param) {

                                        $additinal_settings[$j]['help'] = '';

                                        if(isset($db_column_params['help']) && $db_column_params['help'] && !$help_added){

                                            $additinal_settings[$j]['help'] = $db_column_params['help'];

                                            $help_added = TRUE;

                                        }

                                        $additinal_settings[$j]['onchange'] = '';

                                        if(isset($additinal_column_param['onchange']) && $additinal_column_param['onchange']){

                                            $additinal_settings[$j]['onchange'] = $additinal_column_param['onchange'];

                                        }
                                        
                                        $additinal_settings[$j]['box-title'] = '';
                                        
                                        if(isset($additinal_column_param['box-title']) && $additinal_column_param['box-title']){

                                            $additinal_settings[$j]['box-title'] = $additinal_column_param['box-title'];

                                        }

                                        $additinal_settings[$j]['style'] = '';

                                        if(isset($additinal_column_param['style']) && $additinal_column_param['style']){

                                            $additinal_settings[$j]['style'] = $additinal_column_param['style'];

                                        }

                                        $additinal_settings[$j]['hide_this_additinal_data'] = 0;

                                        if(isset($additinal_column_param['export']) && !$additinal_column_param['export'] && $data['type_process']=='export'){

                                            $additinal_settings[$j]['hide_this_additinal_data'] = 1;

                                        }

                                        if(isset($additinal_column_param['import']) && !$additinal_column_param['import'] && $data['type_process']=='import'){

                                            $additinal_settings[$j]['hide_this_additinal_data'] = 1;

                                        }

                                        $additinal_settings[$j]['class'] = '';

                                        if(isset($additinal_column_param['class']) && $additinal_column_param['class']){

                                            $additinal_settings[$j]['class'] = $additinal_column_param['class'];

                                        }

                                        $additinal_settings[$j]['id'] = '';

                                        if(isset($additinal_column_param['id']) && $additinal_column_param['id']){

                                            $additinal_settings[$j]['id'] = $additinal_column_param['id'];

                                        }

                                        $additinal_settings[$j]['name'] = 'odmpro_tamplate_data[type_data_column]['.$field.'][additinal_settings]['.$additinal_column.']';

                                        $additinal_settings[$j]['placeholder'] = '';

                                        if(isset($additinal_column_param['placeholder']) && $additinal_column_param['placeholder']){

                                            $additinal_settings[$j]['placeholder'] = $additinal_column_param['placeholder'];

                                        }

                                        $additinal_settings[$j]['data-original-title'] = '';

                                        if(isset($additinal_column_param['data-original-title']) && $additinal_column_param['data-original-title']){

                                            $additinal_settings[$j]['data-original-title'] = $additinal_column_param['data-original-title'];

                                        }


                                        $additinal_settings[$j]['element'] = $additinal_column_param['element'];

                                        if($additinal_settings[$j]['element']=='input' && isset($additinal_column_param['group'])){
                                            
                                            for($g=0;$g<$additinal_column_param['group'];$g++){   
                                                
                                                $additinal_settings[$j]['group'][$g]['field']['name'] = 'odmpro_tamplate_data[type_data_column]['.$field.'][additinal_settings]['.$additinal_column.']['.$g.'][field]';
                                                $additinal_settings[$j]['group'][$g]['value']['name'] = 'odmpro_tamplate_data[type_data_column]['.$field.'][additinal_settings]['.$additinal_column.']['.$g.'][value]';
                                                $additinal_settings[$j]['group'][$g]['field']['value'] = '';
                                                $additinal_settings[$j]['group'][$g]['value']['value'] = '';
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column][$g]['field']!==''){

                                                    $additinal_settings[$j]['group'][$g]['field']['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column][$g]['field'];

                                                }
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column][$g]['value']!==''){

                                                    $additinal_settings[$j]['group'][$g]['value']['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column][$g]['value'];

                                                }
                                                
                                            }
                                            
                                        }
                                        
                                        elseif($additinal_settings[$j]['element']=='input' && isset($additinal_column_param['range'])){
                                            
                                            $names_range = array('from','to','multiply','plus');
                                            
                                            for($g=0;$g<$additinal_column_param['range'];$g++){   
                                                
                                                foreach ($names_range as $name_range) {
                                                    $additinal_settings[$j]['range'][$g][$name_range]['name'] = 'odmpro_tamplate_data[type_data_column]['.$field.'][additinal_settings]['.$additinal_column.']['.$g.']['.$name_range.']';
                                                    $additinal_settings[$j]['range'][$g][$name_range]['value'] = '';
                                                    if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column][$g][$name_range]!==''){

                                                        $additinal_settings[$j]['range'][$g][$name_range]['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column][$g][$name_range];

                                                    }
                                                }
                                                
                                            }
                                            
                                        }elseif($additinal_settings[$j]['element']=='input'){

                                            $additinal_settings[$j]['type'] = 'text';

                                            if(isset($additinal_column_param['type']) && $additinal_column_param['type']){

                                                $additinal_settings[$j]['type'] = $additinal_column_param['type'];

                                            }

                                            if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]!==''){

                                                $additinal_settings[$j]['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column];

                                            }else{

                                                $additinal_settings[$j]['value'] = '';

                                                if(isset($additinal_column_param['default_value']) && $additinal_column_param['default_value'] != ''){

                                                    $additinal_settings[$j]['value'] = $additinal_column_param['default_value'];

                                                }

                                            }

                                        }
                                        /*
                                         * У select'ов свой набор options
                                         */
                                        /*
                                         * image_upload
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='image_upload'){

                                                $additinal_settings[$j]['element'] = 'select';

                                                for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_image_upload_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }

                                            }

                                        }
                                        /*
                                         * first_image_main
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='first_image_main'){

                                                $additinal_settings[$j]['element'] = 'select';

                                                for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_first_image_main_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }

                                            }

                                        }
                                        /*
                                         * first_image_add
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='first_image_add'){

                                                $additinal_settings[$j]['element'] = 'select';

                                                for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_first_image_add_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }

                                            }

                                        }

                                        /*
                                         * price_around
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='price_around'){

                                                $additinal_settings[$j]['element'] = 'select';

                                                for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_price_around_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }

                                            }

                                        }
                                        /*
                                         * customer_group
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='customer_group'){

                                                $this->load->model('customer/customer_group');

                                                $customer_groups = $this->model_customer_customer_group->getCustomerGroups();

                                                $i = 0;

                                                $additinal_settings[$j]['options'][$i]['value'] = 0;

                                                if($customer_groups){

                                                        $additinal_settings[$j]['options'][$i]['text'] = "По умолчанию";

                                                }else{
                                                        $additinal_settings[$j]['options'][$i]['text'] = "По умолчанию";

                                                }

                                                $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                $i++;

                                                foreach ($customer_groups as $option) {

                                                        $additinal_settings[$j]['options'][$i]['value'] = $option['customer_group_id'];

                                                        $additinal_settings[$j]['options'][$i]['text'] = $option['name'];

                                                        if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option['customer_group_id']){

                                                                $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                        }else{

                                                                $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                        }
                                                        $i++;
                                                }

                                        }
                                        /*
                                         * subtract_default
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='subtract_default'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_subtract_default_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }

                                        /*
                                         * required_default
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='required_default'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_required_default_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }

                                        /*
                                         * price_default
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='price_default'){

                                            for($i=0;$i<1;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_price_default_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $this->language->get('entry_type_data_column_price_default_field');
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }
                                        }

                                        /*
                                         * quantity_default
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='quantity_default'){

                                            for($i=0;$i<1;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_quantity_default_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $this->language->get('entry_type_data_column_quantity_default_field');
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }
                                        }

                                        /*
                                         * price_whis_delta
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='price_whis_delta'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_price_whis_delta_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }

                                        /*
                                         * subtract_default
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='subtract_default'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_subtract_default_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }
                                        /*
                                         * image_new_name
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='image_new_name'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_image_new_name_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }
                                        /*
                                         * main_category
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='main_category'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_main_category_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }
                                        /*
                                         * parent_category_id
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='parent_category_id'){

                                            $options = $this->model_tool_csv_ocext_dmpro->getCategories('&nbsp;&nbsp;&gt;&nbsp;&nbsp;',$this->config->get('config_language_id'));

                                            $i = 0;

                                            $additinal_settings[$j]['options'][$i]['value'] = 0;

                                            if($options){

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);

                                            }else{
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column.'_empty');

                                            }

                                            $additinal_settings[$j]['options'][$i]['selected'] = '';

                                            $i++;

                                            foreach ($options as $option) {

                                                $additinal_settings[$j]['options'][$i]['value'] = $option['category_id'];

                                                $additinal_settings[$j]['options'][$i]['text'] = $option['name'];

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option['category_id']){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                                $i++;
                                            }

                                        }
                                        /*
                                         * all_product_category
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='all_product_category'){

                                            for($i=0;$i<2;$i++){

                                                $additinal_settings[$j]['options'][$i]['value'] = $i;

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_all_product_category_'.$i);

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                            }

                                        }
                                        /*
                                         * filter_group_id, attribute_group_id
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && ($additinal_column=='filter_group_id' || $additinal_column=='attribute_group_id')){

                                            $options = $this->getAttributeOrFilterGroupsByDBTable($language_id, $db_table);
                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;
                                            if($options){
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);
                                            }else{
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column.'_empty');
                                            }
                                            $additinal_settings[$j]['options'][$i]['selected'] = '';
                                            $i++;
                                            foreach ($options as $option) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $this->language->get('entry_type_data_column_group_identificator_group_optiongroup');
                                                $additinal_settings[$j]['options'][$i]['value'] = $option[$additinal_column];
                                                $additinal_settings[$j]['options'][$i]['text'] = $option['name'];
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option[$additinal_column]){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }

                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $this->language->get('entry_type_data_column_group_identificator_group_optiongroup_fields');
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                        }
                                        /*
                                         * product_assortiment_name_article
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' &&
                                                ( $additinal_column=='option_value_name_field_1'
                                                || $additinal_column=='option_value_name_field_2'
                                                || $additinal_column=='option_value_name_field_3'
                                                || $additinal_column=='option_value_name_field_4'
                                                || $additinal_column=='option_value_name_field_5'
                                                )
                                            ){
                                            
                                            $add_to_name = '';
                                            
                                            if( $additinal_column=='option_value_name_field_1'
                                                || $additinal_column=='option_value_name_field_2'
                                                || $additinal_column=='option_value_name_field_3'
                                                || $additinal_column=='option_value_name_field_4'
                                                || $additinal_column=='option_value_name_field_5'
                                                ){
                                            
                                                $add_to_name_parts = explode('_',$additinal_column);
                                                
                                                $add_to_name = ' '.end($add_to_name_parts);
                                                
                                            }

                                            
                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;
                                            $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_option_value_name_field').$add_to_name;
                                            $additinal_settings[$j]['options'][$i]['selected'] = '';
                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }
                                            $i++;
                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = '';
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                        }
                                        /*
                                         * product_assortiment_name_article
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='product_assortiment_name_article'){

                                            
                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;
                                            $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_product_assortiment_name_article');
                                            $additinal_settings[$j]['options'][$i]['selected'] = '';
                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }
                                            $assortiment_articles = array('ean','model','jan','upc','isbn','mpn','sku');
                                            $i++;
                                            foreach ($assortiment_articles as $assortiment_article) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = '';
                                                $additinal_settings[$j]['options'][$i]['value'] = $assortiment_article;
                                                $additinal_settings[$j]['options'][$i]['text'] = $assortiment_article;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $assortiment_article){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                        }
                                        
                                        /*
                                         * price_purchase_price
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='price_purchase_price'){

                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;
                                            $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);
                                            $additinal_settings[$j]['options'][$i]['selected'] = '';
                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }
                                            $i++;
                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = '';
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                        }
                                        /*
                                         * price_purchase_price
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='price_rrp'){

                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;
                                            $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);
                                            $additinal_settings[$j]['options'][$i]['selected'] = '';
                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }
                                            $i++;
                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = '';
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                        }
                                        /*
                                         * attribute_name_field
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='attribute_name_field'){

                                            $options = $this->getAttributeOrFilterGroupsByDBTable($language_id, $db_table);
                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;
                                            $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);
                                            $additinal_settings[$j]['options'][$i]['selected'] = '';
                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }
                                            $i++;
                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = '';
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;
                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }

                                        }
                                        /*
                                         * attribute_group_id___attribute_id
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='attribute_group_id___attribute_id'){

                                            $options = $this->getAttributes();

                                            $i = 0;

                                            $additinal_settings[$j]['options'][$i]['value'] = 0;

                                            if($options){

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);

                                            }else{
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column.'_empty');

                                            }

                                            $additinal_settings[$j]['options'][$i]['selected'] = '';

                                            $i++;

                                            foreach ($options as $option) {

                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $option['attribute_group_name'];

                                                $additinal_settings[$j]['options'][$i]['value'] = $option['attribute_group_id'].'___'.$option['attribute_id'];

                                                $additinal_settings[$j]['options'][$i]['text'] = $option['name'];

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option['attribute_group_id'].'___'.$option['attribute_id']){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                                $i++;
                                            }   

                                        }

                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='option_id___option_value_id'){

                                            $options = $this->getValuesOptions();

                                            $i = 0;

                                            $additinal_settings[$j]['options'][$i]['value'] = 0;

                                            if($options){

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);

                                            }else{
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column.'_empty');

                                            }

                                            $additinal_settings[$j]['options'][$i]['selected'] = '';

                                            $i++;

                                            foreach ($options as $option) {

                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $option['optiongroup'];

                                                $additinal_settings[$j]['options'][$i]['value'] = $option['option_id'].'___'.$option['option_value_id'];

                                                $additinal_settings[$j]['options'][$i]['text'] = $option['name'];

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option['option_id'].'___'.$option['option_value_id']){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }
                                                $i++;
                                            }   

                                        }

                                        /*
                                         * option_id_for_field
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' &&
                                                (
                                                $additinal_column=='option_id'
                                                || $additinal_column=='option_id_for_field_1'
                                                || $additinal_column=='option_id_for_field_2'
                                                || $additinal_column=='option_id_for_field_3'
                                                || $additinal_column=='option_id_for_field_4'
                                                || $additinal_column=='option_id_for_field_5'
                                                )){

                                            $options = $this->getOptions();
                                            
                                            $add_to_name = '';
                                            
                                            if( $additinal_column=='option_id_for_field_1'
                                                || $additinal_column=='option_id_for_field_2'
                                                || $additinal_column=='option_id_for_field_3'
                                                || $additinal_column=='option_id_for_field_4'
                                                || $additinal_column=='option_id_for_field_5'
                                                ){
                                            
                                                $add_to_name_parts = explode('_',$additinal_column);
                                                
                                                $add_to_name = ' '.end($add_to_name_parts);
                                                
                                                foreach($options as $option_key => $option){
                                                    
                                                    $options[$option_key][$additinal_column] = $option['option_id'];
                                                    
                                                }
                                                
                                            }

                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;

                                            if($options){

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column).$add_to_name;

                                            }else{
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column.'_empty');
                                            }

                                            $additinal_settings[$j]['options'][$i]['selected'] = '';

                                            $i++;

                                            foreach ($options as $option) {

                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $this->language->get('entry_type_data_column_group_identificator_option_optiongroup');

                                                $additinal_settings[$j]['options'][$i]['value'] = $option[$additinal_column];

                                                $additinal_settings[$j]['options'][$i]['text'] = $option['name'];

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option[$additinal_column]){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }

                                                $i++;
                                            }

                                            /*
                                             * Только для импорта
                                             */
                                            if($data['type_process']=='import'){

                                                $style = "";

                                            }else{

                                                $style = "display:none";

                                            }

                                            foreach ($fields as $field_this) {
                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $this->language->get('entry_type_data_column_group_identificator_option_optiongroup_fields');
                                                $additinal_settings[$j]['options'][$i]['value'] = 'field_this_file___'.$field_this;
                                                $additinal_settings[$j]['options'][$i]['text'] = $field_this;
                                                $additinal_settings[$j]['options'][$i]['style'] = $style;

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == 'field_this_file___'.$field_this){
                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';
                                                }else{
                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';
                                                }
                                                $i++;
                                            }


                                        }

                                        /*
                                         * option_value_id
                                         */
                                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='option_value_id'){

                                            $options = $this->getValuesOptions();

                                            $i = 0;
                                            $additinal_settings[$j]['options'][$i]['value'] = 0;

                                            if($options){

                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column);

                                            }else{
                                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_group_identificator_'.$additinal_column.'_empty');
                                            }

                                            $additinal_settings[$j]['options'][$i]['selected'] = '';

                                            $i++;

                                            foreach ($options as $option) {

                                                $additinal_settings[$j]['options'][$i]['value'] = $option[$additinal_column];

                                                $additinal_settings[$j]['options'][$i]['text'] = $option['name'];

                                                $additinal_settings[$j]['options'][$i]['optiongroup'] = $option['optiongroup'];

                                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $option[$additinal_column]){

                                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                                }else{

                                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                                }

                                                $i++;
                                            }   

                                        }

                                        $j++;

                                    }

                                }
                                /*
                                 * Создание справки, инструкции, когда нет дополнительных форм
                                 */
                                elseif($db_column==$db_column_params['field'] && isset($db_column_params['help']) && $db_column_params['help'] && (!isset($db_column_params['additinal_settings']) || !$db_column_params['additinal_settings'])){

                                    $additinal_settings[$j]['help'] = $db_column_params['help'];

                                    $j++;

                                }
                            
                            }
                            
                            
                        }
                        
                    }
                    
                    /*
                     * Идентификатор не имеет названия таблицы в $db_table, он выглядит, как $db_table . '_identificator'
                     */
                    
                    elseif($db_column=='identificator'){
                        
                        foreach ($db_columns_row as $db_column_key => $db_column_params) {
                            
                            if($db_column_key==$db_column){
                                
                                foreach ($db_column_params['additinal_settings'] as $additinal_column => $additinal_column_param) {
                       
                                    $additinal_settings[$j]['help'] = '';

                                     if(isset($db_column_params['help']) && $db_column_params['help']){

                                         $additinal_settings[$j]['help'] = $db_column_params['help'];

                                     }
                                     
                                    $additinal_settings[$j]['hide_this_additinal_data'] = 0;
                                    
                                    if(isset($additinal_column_param['export']) && !$additinal_column_param['export'] && $data['type_process']=='export'){

                                        $additinal_settings[$j]['hide_this_additinal_data'] = 1;

                                    }

                                     $additinal_settings[$j]['onchange'] = '';

                                     if(isset($additinal_column_param['onchange']) && $additinal_column_param['onchange']){

                                         $additinal_settings[$j]['onchange'] = $additinal_column_param['onchange'];

                                     }

                                     $additinal_settings[$j]['style'] = '';

                                     if(isset($additinal_column_param['style']) && $additinal_column_param['style']){

                                         $additinal_settings[$j]['style'] = $additinal_column_param['style'];

                                     }

                                     $additinal_settings[$j]['class'] = '';

                                     if(isset($additinal_column_param['class']) && $additinal_column_param['class']){

                                         $additinal_settings[$j]['class'] = $additinal_column_param['class'];

                                     }

                                     $additinal_settings[$j]['id'] = '';

                                     if(isset($additinal_column_param['id']) && $additinal_column_param['id']){

                                         $additinal_settings[$j]['id'] = $additinal_column_param['id'];

                                     }

                                     $additinal_settings[$j]['name'] = 'odmpro_tamplate_data[type_data_column]['.$field.'][additinal_settings]['.$additinal_column.']';

                                     $additinal_settings[$j]['placeholder'] = '';

                                     if(isset($additinal_column_param['placeholder']) && $additinal_column_param['placeholder']){

                                         $additinal_settings[$j]['placeholder'] = $additinal_column_param['placeholder'];

                                     }

                                     $additinal_settings[$j]['data-original-title'] = '';

                                     if(isset($additinal_column_param['data-original-title']) && $additinal_column_param['data-original-title']){

                                         $additinal_settings[$j]['data-original-title'] = $additinal_column_param['data-original-title'];

                                     }


                                     $additinal_settings[$j]['element'] = $additinal_column_param['element'];

                                     if($additinal_settings[$j]['element']=='input'){

                                         $additinal_settings[$j]['type'] = 'text';

                                         if(isset($additinal_column_param['type']) && $additinal_column_param['type']){

                                             $additinal_settings[$j]['type'] = $additinal_column_param['type'];

                                         }

                                         if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]){

                                             $additinal_settings[$j]['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column];

                                         }else{

                                             $additinal_settings[$j]['value'] = '';

                                         }

                                     }
                                     /*
                                      * У select'ов свой набор options
                                      */
                                     /*
                                      * identificator_type
                                      */
                                     elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='identificator_type'){

                                         $types_data =  $this->getTypesData();

                                         $types_data = $types_data['types_data'];

                                         $options = array();

                                         foreach ($types_data as $type_data_this => $tmp) {
                                             
                                             if($type_data_this=='review' || $type_data_this=='order'){
                                                 
                                                $options[$type_data_this] = array(
                                                    'aid'=>  sprintf($this->language->get('entry_unique_type_data_aid'), $type_data_this.'_id')
                                                );
                                                 
                                             }else{
                                                 
                                                 $options[$type_data_this] = array(
                                                    'aid'=>  sprintf($this->language->get('entry_unique_type_data_aid'), $type_data_this.'_id'),
                                                    'name'=>  sprintf($this->language->get('entry_unique_type_data_name'), 'name')
                                                );
                                                 
                                             }
                                             if($type_data_this=='product'){

                                                 $columns_product = $this->model_tool_csv_ocext_dmpro->getOnlyColumnsName($type_data_this,array('product_id','date_available','tax_class_id','manufacturer_id','stock_status_id','points','weight_class_id','weight','length','width','height','length_class_id','subtract','minimum','sort_order','status','viewed','date_added','date_modified','location','quantity','shipping','price'));

                                                 if($columns_product){
                                                     foreach ($columns_product as $column_product_field) {

                                                         $options[$type_data_this][$column_product_field] = $column_product_field;

                                                     }
                                                 }

                                             }
                                             if($type_data_this=='option_value'){

                                                 $columns_option_value = $this->model_tool_csv_ocext_dmpro->getOnlyColumnsName($type_data_this,array('option_id','image','sort_order'));

                                                 if($columns_option_value){

                                                     foreach ($columns_option_value as $column_option_field) {

                                                         $options[$type_data_this][$column_option_field] = $column_option_field;

                                                     }

                                                 }

                                             }

                                         }

                                         $options = $options[str_replace('_identificator', '', $db_table)];

                                         $i = 0;

                                         foreach ($options as $value_option => $text_option) {

                                             $additinal_settings[$j]['options'][$i]['value'] = $value_option;

                                             $additinal_settings[$j]['options'][$i]['text'] = $text_option;

                                             if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $value_option){

                                                 $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                             }else{

                                                 $additinal_settings[$j]['options'][$i]['selected'] = '';

                                             }

                                             $i++;
                                         }

                                     }

                                     /*
                                      * identificator_insert
                                      */
                                    elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='identificator_insert'){

                                         for($i=0;$i<2;$i++){

                                             $additinal_settings[$j]['options'][$i]['value'] = $i;

                                             $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_identificator_insert_'.$i);

                                             if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                                 $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                             }else{

                                                 $additinal_settings[$j]['options'][$i]['selected'] = '';

                                             }

                                         }
                                         
                                         
                                         
                                         
                                         
                                         
                                         
                                         
                                        $left_id_prefix = '';

                                        $right_id_prefix = '';
                                        
                                        if($data['type_process']=='import'){

                                            $style = "";

                                        }else{

                                            $style = "display:none";

                                        }

                                        if(isset($odmpro_tamplate_data['group_id_box']['right_prefix'])){

                                            $right_id_prefix = trim($odmpro_tamplate_data['group_id_box']['right_prefix']);

                                        }

                                        if(isset($odmpro_tamplate_data['group_id_box']['left_prefix'])){

                                            $left_id_prefix = trim($odmpro_tamplate_data['group_id_box']['left_prefix']);

                                        }
                                        
                                        $j++; 
                                        
                                        $additinal_settings[$j]['name'] = 'odmpro_tamplate_data[group_id_box][right_prefix]';

                                        $additinal_settings[$j]['placeholder'] = 'Префикс справа';

                                        $additinal_settings[$j]['data-original-title'] = 'Установите префикс, если необходимо идентифицировать товар с учетом префикса. Если товар будет добавляться, то этот префикс будет также добавлен';
                                        
                                        $additinal_settings[$j]['element'] = 'input';

                                        $additinal_settings[$j]['type'] = 'text';

                                        $additinal_settings[$j]['value'] = $right_id_prefix;
                                        
                                        $additinal_settings[$j]['style'] = $style;
                                        
                                        $j++; 

                                        $additinal_settings[$j]['name'] = 'odmpro_tamplate_data[group_id_box][left_prefix]';

                                        $additinal_settings[$j]['placeholder'] = 'Префикс слева';

                                        $additinal_settings[$j]['data-original-title'] = 'Установите префикс, если необходимо идентифицировать товар с учетом префикса. Если товар будет добавляться, то этот префикс будет также добавлен';
                                        
                                        $additinal_settings[$j]['element'] = 'input';

                                        $additinal_settings[$j]['type'] = 'text';

                                        $additinal_settings[$j]['value'] = $left_id_prefix;
                                        
                                        $additinal_settings[$j]['style'] = $style;
                                        
                                        
                                         
                                         

                                     }
                                     
                                     $j++; 

                                }
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
                
                /*
                * Формы, которые должны быть при выборе любых данных - например обязательно, не обязательно поле
                */
                $j++; 
                foreach ($abstract_field['abstract_field_for_all_data'] as $type_abstract_field_for_all_data => $db_column_params) {
                   
                   foreach ($db_column_params['additinal_settings'] as $additinal_column => $additinal_column_param) {
                       
                       $additinal_settings[$j]['help'] = '';

                        if(isset($db_column_params['help']) && $db_column_params['help']){

                            $additinal_settings[$j]['help'] = $db_column_params['help'];

                        }

                        $additinal_settings[$j]['onchange'] = '';

                        if(isset($additinal_column_param['onchange']) && $additinal_column_param['onchange']){

                            $additinal_settings[$j]['onchange'] = $additinal_column_param['onchange'];

                        }

                        $additinal_settings[$j]['style'] = '';

                        if(isset($additinal_column_param['style']) && $additinal_column_param['style']){

                            $additinal_settings[$j]['style'] = $additinal_column_param['style'];

                        }

                        $additinal_settings[$j]['class'] = '';

                        if(isset($additinal_column_param['class']) && $additinal_column_param['class']){

                            $additinal_settings[$j]['class'] = $additinal_column_param['class'];

                        }

                        $additinal_settings[$j]['id'] = '';

                        if(isset($additinal_column_param['id']) && $additinal_column_param['id']){

                            $additinal_settings[$j]['id'] = $additinal_column_param['id'];

                        }

                        $additinal_settings[$j]['name'] = 'odmpro_tamplate_data[type_data_column]['.$field.'][additinal_settings]['.$additinal_column.']';

                        $additinal_settings[$j]['placeholder'] = '';

                        if(isset($additinal_column_param['placeholder']) && $additinal_column_param['placeholder']){

                            $additinal_settings[$j]['placeholder'] = $additinal_column_param['placeholder'];

                        }

                        $additinal_settings[$j]['data-original-title'] = '';

                        if(isset($additinal_column_param['data-original-title']) && $additinal_column_param['data-original-title']){

                            $additinal_settings[$j]['data-original-title'] = $additinal_column_param['data-original-title'];

                        }


                        $additinal_settings[$j]['element'] = $additinal_column_param['element'];

                        if($additinal_settings[$j]['element']=='input'){

                            $additinal_settings[$j]['type'] = 'text';

                            if(isset($additinal_column_param['type']) && $additinal_column_param['type']){

                                $additinal_settings[$j]['type'] = $additinal_column_param['type'];

                            }

                            if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]){

                                $additinal_settings[$j]['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column];

                            }else{

                                $additinal_settings[$j]['value'] = '';

                            }

                        }
			elseif($additinal_settings[$j]['element']=='textarea'){

                            $additinal_settings[$j]['type'] = '';

                            if(isset($additinal_column_param['type']) && $additinal_column_param['type']){

                                $additinal_settings[$j]['type'] = $additinal_column_param['type'];

                            }

                            if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]){

                                $additinal_settings[$j]['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column];

                            }else{

                                $additinal_settings[$j]['value'] = '';

                            }
			    
			    $additinal_settings[$j]['function-title'] = $additinal_column_param['function-title'];
			    
			    $additinal_settings[$j]['function-title-help'] = $additinal_column_param['function-title-help'];
			    
			    if(isset($additinal_column_param['export']) && !$additinal_column_param['export'] && $data['type_process']=='export'){
				
				$additinal_settings[$j]['style'] .= ';display:none;';
				
			    }

                        }
                        elseif($additinal_settings[$j]['element']=='textarea2'){

                            $additinal_settings[$j]['type'] = '';

                            if(isset($additinal_column_param['type']) && $additinal_column_param['type']){

                                $additinal_settings[$j]['type'] = $additinal_column_param['type'];

                            }

                            if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]){

                                $additinal_settings[$j]['value'] = $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column];

                            }else{

                                $additinal_settings[$j]['value'] = '';

                            }
			    
			    $additinal_settings[$j]['function-title'] = $additinal_column_param['function-title'];

                        }
                        /*
                         * У select'ов свой набор options
                         */
                        
                        /*
                         * column_request
                         */
                        elseif($additinal_settings[$j]['element']=='select' && $additinal_column=='column_request'){

                            $count_column_request = 4;
                            
                            if(isset($additinal_column_param['export']) && !$additinal_column_param['export'] && $data['type_process']=='export'){
                                
                                $count_column_request = 2;
                                
                            }
                            
                            for($i=0;$i<$count_column_request;$i++){
                                
                                $additinal_settings[$j]['options'][$i]['value'] = $i;
                                
                                $additinal_settings[$j]['options'][$i]['text'] = $this->language->get('entry_type_data_column_request_'.$i);
                                
                                if(isset($odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column]) && $odmpro_tamplate_data['type_data_column'][$field]['additinal_settings'][$additinal_column] == $i){

                                    $additinal_settings[$j]['options'][$i]['selected'] = 'selected=""';

                                }else{

                                    $additinal_settings[$j]['options'][$i]['selected'] = '';

                                }
                            }

                        }
			
			$j++;
                       
                   }
                   
               }
               
                $data['type_data_and_column_additional'] = $additinal_settings;
                
                $data['entry_type_data_column_title'] = $this->language->get('entry_type_data_column_title');
                
                $data['entry_type_data_column_title_help'] = $this->language->get('entry_type_data_column_title_help');
                
            }
            
            $data['tamplate_data_selected'] = $odmpro_tamplate_data;
            
            return $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/ocext_dmpro_types_data_column'.$this->ftype, $data));
        }
	
	public function loadTemplateSetting(){
	    
	    $this->load->language('catalog/download');

	    $json = array();
	    
	    $template_setting = array();
	    
	    if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
		    
		    $content = base64_decode(file_get_contents($this->request->files['file']['tmp_name']));

		    if (preg_match('/\<\?php/i', $content)) {
			$json['error'] = "Неверный тип файла";
		    }
		    elseif(!strstr($content, 'save_anycsv')){
			$json['error'] = "Ошибка чтения файла";
		    }
		    else{
			
			$odmpro_tamplates_data_source = json_decode($content,TRUE);
			
			if($odmpro_tamplates_data_source && is_array($odmpro_tamplates_data_source)){
			    
			    $odmpro_tamplate_data_id = md5(time());
			    
			    $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');

			    $this->load->model('tool/csv_ocext_dmpro');
			    
			    $odmpro_tamplates_data['odmpro_tamplate_data'] = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
                
			    $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id] = current($odmpro_tamplates_data_source);

			    $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['id'] = $odmpro_tamplate_data_id;

                            if(isset($odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['export_field_name'])){
                                
                                $last_export_field_name = $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['export_field_name'];
                                
                                foreach ($last_export_field_name as $last_odmpro_tamplate_data_id => $last_export_field) {
                                    
                                    $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['export_field_name'][$odmpro_tamplate_data_id] = $last_export_field;
                                    unset($odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['export_field_name'][$last_odmpro_tamplate_data_id]);
                                    
                                }
                                
                            }
                            
                            
                            
			    if ($this->validate()) {

				$this->model_tool_csv_ocext_dmpro->editSetting('odmpro', $odmpro_tamplates_data,TRUE);

			    }
			    
			    $json['success'] = "ok";
			    
			}
			
		    }

		    // Return any upload error
		    if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
			    $json['error'] = "Ошибка загрузки файла";
		    }
	    } else {
		    $json['error'] = "Ошибка загрузки файла";
	    }

	    $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
	    
	}


	public function saveTemplateSetting() {
	    
	    $tid = $this->request->get['tid'];

	    $templates_setting = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);

            $this->model_tool_csv_ocext_dmpro->saveTemplateSetting($tid,$templates_setting);
	    
	}
        
        public function getFileByFileName() {
	    
            $this->model_tool_csv_ocext_dmpro->getTFileByFileName($this->request->get);
	    
	}
        
        public function setTemplateData() {
            
            $this->load->model('setting/setting');
            
            $odmpro_tamplate_data_id = $this->request->post['odmpro_tamplate_data']['id'];
            
            $odmpro_tamplate_data_name = '';
            
            if(isset($this->request->post['odmpro_tamplate_data']['name'])){
                $odmpro_tamplate_data_name = $this->request->post['odmpro_tamplate_data']['name'];
            }
            
            if(isset($this->request->post['odmpro_format_data'])){
                $format_data = $this->request->post['odmpro_format_data'];
            }else{
                $format_data = 'csv';
            }
            
            $type_action = $this->request->get['type_action'];
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
            
            if($config_odmpro_tamplate_data){
                
                $odmpro_tamplates_data['odmpro_tamplate_data'] = $config_odmpro_tamplate_data;
                
            }else{
                
                $odmpro_tamplates_data['odmpro_tamplate_data'] = array();
                
            }
            
            //новый шаблон
            if(!$odmpro_tamplate_data_id){
                
                $odmpro_tamplate_data_id = md5(time());
                
                $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id] = $this->request->post['odmpro_tamplate_data'];
                
                $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['id'] = $odmpro_tamplate_data_id;
                
                if(isset($this->request->post['odmpro_tamplate_data']['export_field_name'][0])){
                    
                    $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['export_field_name'][$odmpro_tamplate_data_id] = $this->request->post['odmpro_tamplate_data']['export_field_name'][0];
                    
                }
                
            }elseif($odmpro_tamplate_data_id && $type_action=='update'){
                
                $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id] = $this->request->post['odmpro_tamplate_data'];
                
            }elseif($odmpro_tamplate_data_id && $type_action=='save'){
                
                $export_field_name = '';
                
                if(isset($this->request->post['odmpro_tamplate_data']['export_field_name'][$odmpro_tamplate_data_id])){
                    
                    $export_field_name = $this->request->post['odmpro_tamplate_data']['export_field_name'][$odmpro_tamplate_data_id];
                    
                }
                
                $odmpro_tamplate_data_id = md5(time());
                
                $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id] = $this->request->post['odmpro_tamplate_data'];
                
                $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['id'] = $odmpro_tamplate_data_id;
                
                if($export_field_name){
                    
                    $odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]['export_field_name'][$odmpro_tamplate_data_id] = $export_field_name;
                    
                }
                
            }elseif($odmpro_tamplate_data_id && $type_action=='delete'){
                
                unset($odmpro_tamplates_data['odmpro_tamplate_data'][$odmpro_tamplate_data_id]);
                
                $result['odmpro_tamplate_data_id_delete'] = $odmpro_tamplate_data_id;
                
                $odmpro_tamplate_data_id = 0;
                
                $odmpro_tamplate_data_name = '';
                
            }
            
            $result['error'] = '';
            
            $result['success'] = '';
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            if ($this->validate()) {
                
                $this->model_tool_csv_ocext_dmpro->editSetting('odmpro', $odmpro_tamplates_data,TRUE);
                
            }else{
                
               $result['error'] = $this->language->get('error_permission'); 
               
            }
            
            $result['odmpro_tamplate_data_id'] = $odmpro_tamplate_data_id;
            
            $result['odmpro_tamplate_data_name'] = $odmpro_tamplate_data_name;
            
            if($format_data=='csv' && !$result['error']){
                
                $result['success'] = $this->language->get('entry_odmpro_tamplate_data_done');
                
            }
            
            $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($result));
            
        }
        
        private function checkCURL(){
            
            if(function_exists('curl_version')){
                
                return TRUE;
                
            }else{
                
                return FALSE;
                
            }
        }
        
        private function validate() {
            
		if (!$this->user->hasPermission('modify', $this->path_oc_version.'/csv_ocext_dmpro')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
        
        public function startExport() {
            
            $format_data = $this->request->post['odmpro_tamplate_data']['format_data'];
            
            $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
            
            $type_process = $this->request->post['type_process'];
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $start = (int)$this->request->get['start'];
            
            $limit = (int)$this->request->post['odmpro_tamplate_data']['limit'];
            
            $num_process = $this->request->get['num_process'];
            
            $log_data = array(
                'start' => $start,
                'limit' => $limit,
                'num_process'   => $num_process,
                'type_process'  => $type_process,
                'format_data'   => $format_data,
                'file_url'   => '',
                'file_upload'   => $odmpro_tamplate_data['export_file_name'],
            );
            
            $json['error'] = '';
            
            if(!isset($odmpro_tamplate_data['type_data'])){
                
                $json['error'] .= $this->language->get($type_process.'_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get($type_process.'_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }
            
            $type_data_columns = FALSE;
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data){
                    
                    $type_data_columns = TRUE;
                    
                }
                
            }
            
            if(!$type_data_columns){
                
                $json['error'] .= $this->language->get($type_process.'_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get($type_process.'_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }
            
            $type_data_columns_by_type_data = array();
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data && $field!=='' && isset($odmpro_tamplate_data['type_data_column'][$field]) && $odmpro_tamplate_data['type_data_column'][$field]['db_table___db_column']){
                    
                    $type_data_columns_by_type_data[$type_data]['column_settings'][$field] = $odmpro_tamplate_data['type_data_column'][$field];
                    
                    $type_data_columns_by_type_data[$type_data]['general_settings'] = array();
                    
                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data]){
                        
                        $type_data_columns_by_type_data[$type_data]['general_settings'] = $odmpro_tamplate_data['type_data_general_settings'][$type_data];
                        
                    }
                    
                }
                
            }
            
            if(!$type_data_columns_by_type_data){
                
                $json['error'] .= $this->language->get($type_process.'_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get($type_process.'_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }else{
                
                foreach ($type_data_columns_by_type_data as $type_data => $settings) {
                    
                    foreach ($settings['column_settings'] as $field => $setting) {
                        
                        $db_column_or_advanced_column_name_parts = explode('___', $setting['db_table___db_column']);
                        
                        $db_column_or_advanced_column_name = $db_column_or_advanced_column_name_parts[1];
                        
                        if($db_column_or_advanced_column_name=='identificator'){
                            
                            $type_data_columns_by_type_data[$type_data]['identificator'][$field] = array(
                                'field'=>$field,
                                'additinal_settings'=>$setting['additinal_settings'],
                                'identificator_type'=>$setting['additinal_settings']['identificator_type'],
                            );
                            
                        }
                        
                    }
                    
                }
                
            }
            
            if (!$this->validate()) {
                
                $json['error'] .= '<p>'.$this->language->get('error_permission').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('error_permission')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            /*
            
            $json['success'] = "Экспорт в данной версии будет доступен в обновлении от 15.01.2017 г.";

            $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
            
            exit();
            
             * 
             */
            
            if (!$odmpro_tamplate_data['csv_enclosure']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_enclosure').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_enclosure')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!$odmpro_tamplate_data['language_id']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_language_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_language_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!$odmpro_tamplate_data['csv_delimiter']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_delimiter').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_delimiter')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!isset($odmpro_tamplate_data['store_id'])) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_store_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_store_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            } 
            
            $file = FALSE;
            
            if($odmpro_tamplate_data['export_file_name'] && $odmpro_tamplate_data['export_file_name']){
                
                $file = trim($odmpro_tamplate_data['export_file_name']);
                
            }
            
            if(!$file){
                
                $json['error'] .= '<p>'.$this->language->get('entry_file_exits_export').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_file_exits_export')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            $json['success'] = '';
            
            $result['count_rows'] = 0;
            
            if($format_data=='csv' && !$json['error']){
                
                $result = $this->model_tool_csv_ocext_dmpro->exportCSV($odmpro_tamplate_data,$type_data_columns_by_type_data,$log_data);
                
            }
            
            $json['total'] = $result['count_rows'];
            
            if(($start+$limit)>$result['count_rows'] && $result['count_rows']>0){
                
                $ef = 'csv';
                
                $fn = base64_encode($file);
                
                if(isset($odmpro_tamplate_data['export_format_selected']) && $odmpro_tamplate_data['export_format_selected']=='xls'){
                    
                    $fn = $odmpro_tamplate_data['export_file_name_xls'];
        
                    if($odmpro_tamplate_data['file_name_write_time_xls']){

                        $fn.= date("Y-m-d_H:i:s");

                    }
                    
                    $fn = base64_encode($fn);
                    
                    $ef = 'xlsx';
                    
                }
                
                $get_file_by_file_name = $this->url->link($this->path_oc_version.'/csv_ocext_dmpro/getFileByFileName', 'ef='.$ef.'&fn='.$fn.'&'.$this->token_name.'=' . $this->request->get[$this->token_name], 'SSL');
                
                $json['success'] = $this->language->get('import_success_accomplished').' <a style="color:white;" href="'.$get_file_by_file_name.'" target="_blank"><b>Скачать</b></a>';
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog($type_process,array('success'=>$this->language->get('import_success_accomplished')),$odmpro_tamplate_data,$log_data);
                
            }elseif(!$result['count_rows']){
                
                $json['error'] = $this->language->get('export_empty_data');
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog($type_process,array('error'=>$this->language->get('export_empty_data')),$odmpro_tamplate_data,$log_data);
                
            }
            
            $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
            
        }
        
        public function startImport() {
            
            $this->setMaxMemoryUsage();
            
            $format_data = $this->request->post['odmpro_tamplate_data']['format_data'];
            
            $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
            
            $type_process = $this->request->post['type_process'];
            
            $type_change = $odmpro_tamplate_data['type_change'];
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $start = (int)$this->request->get['start'];
            
            $limit = (int)$this->request->post['odmpro_tamplate_data']['limit'];
            
            $num_process = $this->request->get['num_process'];
            
            $log_data = array(
                'start' => $start,
                'limit' => $limit,
                'num_process'   => $num_process,
                'type_process'  => $type_process,
                'format_data'   => $format_data
            );
            
            $json['error'] = '';
            
            $json['start_time'] = time();
            
            if(!isset($odmpro_tamplate_data['type_data'])){
                
                $json['error'] .= $this->language->get('import_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }
            
            
            /*
             * проверяем есть ли колонки файла для импорта
             */
            $type_data_columns = FALSE;
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data){
                    
                    $type_data_columns = TRUE;
                    
                }
                
            }
            
            if(!$type_data_columns){
                
                $json['error'] .= $this->language->get('import_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }
            
            if(!$type_change){
                
                $json['error'] .= $this->language->get('entry_type_change_error');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_type_change_error')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }
            
            
            /*
             * Данные разбираем с учетом принадложености к основному типу: товары, к товарам, категории к категориям и т.п.
             */
            $type_data_columns_by_type_data = array();
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                $field_to_file = $field;
                
                $field = str_replace('\\', '', $field);
                
                if($type_data && $field!=='' && isset($odmpro_tamplate_data['type_data_column'][$field]) && $odmpro_tamplate_data['type_data_column'][$field]['db_table___db_column']){
                    
                    $type_data_columns_by_type_data[$type_data]['column_settings'][$field_to_file] = $odmpro_tamplate_data['type_data_column'][$field];
                    
                    $type_data_columns_by_type_data[$type_data]['general_settings'] = array();
                    
                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data]){
                        
                        $type_data_columns_by_type_data[$type_data]['general_settings'] = $odmpro_tamplate_data['type_data_general_settings'][$type_data];
                        
                    }
                    
                }
                
            }
            
            
            if(!$type_data_columns_by_type_data){
                
                $json['error'] .= $this->language->get('import_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
                
                return;
                
            }
            
            /*
             * Проходим по всем типам, проверяем настройки по каждому типу на основные ошибки, без которых невозможен обмен данными
             */
            
            foreach ($type_data_columns_by_type_data as $type_data => $settings) {
                
                if($type_change=='update_data' || $type_change=='only_update_data' || $type_change=='only_new_data'){
                    
                    $identificator = FALSE;
                    
                    foreach ($settings['column_settings'] as $field => $setting) {
                        
                        $db_column_or_advanced_column_name_parts = explode('___', $setting['db_table___db_column']);
                        
                        $db_column_or_advanced_column_name = $db_column_or_advanced_column_name_parts[1];
                        
                        if($db_column_or_advanced_column_name=='identificator'){
                            
                            $identificator = TRUE;
                            
                            /*
                             * Идентификаторов может быть несколько, например, ошибочно или для поиска хотя бы одного
                             */
                            
                            $type_data_columns_by_type_data[$type_data]['identificator'][$field] = array(
                                'field'=>$field,
                                'additinal_settings'=>$setting['additinal_settings'],
                                'identificator_type'=>$setting['additinal_settings']['identificator_type'],
                            );
                            
                        }
                        
                    }
                    
                    if(!$identificator){
                
                        $json['error'] .= sprintf($this->language->get('import_error_no_identificator'),$type_data,$type_data); 

                        $log_data['__line__'] = __LINE__; 

                        $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>sprintf($this->language->get('import_error_no_identificator'),$type_data,$type_data)),$odmpro_tamplate_data,$log_data);

                        if($log_error){

                            $json['error'] .= $log_error;

                        }

                        $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                        return;

                    }
                    
                }
                
                foreach ($settings['column_settings'] as $field => $setting) {
                        
                    $db_column_or_advanced_column_name_parts = explode('___', $setting['db_table___db_column']);

                    $db_column_or_advanced_column_name = $db_column_or_advanced_column_name_parts[1];

                    if($db_column_or_advanced_column_name=='image_advanced' && isset($setting['additinal_settings']['image_upload']) && $setting['additinal_settings']['image_upload']){
                        
                        $check_curl = $this->checkCURL();
                
                        if(!$check_curl){

                            $json['error'] .= '<p>'.$this->language->get('entry_curl_exits').'</p>'; 
                            
                            $log_data['__line__'] = __LINE__; 

                            $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_curl_exits')),$odmpro_tamplate_data,$log_data);

                            if($log_error){

                                $json['error'] .= $log_error;

                            }

                            $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                            return;

                        }

                    }

                }
                
            }
            
            if (!$this->validate()) {
                
                $json['error'] .= '<p>'.$this->language->get('error_permission').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('error_permission')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!$odmpro_tamplate_data['csv_enclosure']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_enclosure').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_enclosure')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!$odmpro_tamplate_data['language_id']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_language_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_language_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!$odmpro_tamplate_data['csv_delimiter']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_delimiter').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_delimiter')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            if (!isset($odmpro_tamplate_data['store_id'])) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_store_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_store_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            } 
            
            $this->setMaxMemoryUsage();
            
            $file = '';
            
            if(isset($odmpro_tamplate_data['new_file_upload']) && $odmpro_tamplate_data['new_file_upload'] && !is_array($odmpro_tamplate_data['new_file_upload'])){
                
                $file = $this->model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['new_file_upload']);
                
            }elseif(isset($odmpro_tamplate_data['new_file_upload']) && $odmpro_tamplate_data['new_file_upload'] && is_array($odmpro_tamplate_data['new_file_upload'])){
                
                if(!isset($this->request->get['nfu']) || $this->request->get['nfu']==='no_data'){
                    
                    $this->request->get['nfu'] = 0;

                }
                
                $new_file_upload = '';
                
                if(isset($odmpro_tamplate_data['new_file_upload'][$this->request->get['nfu']])){
                    
                    $new_file_upload = $odmpro_tamplate_data['new_file_upload'][$this->request->get['nfu']];
                    
                }
                
                $file = $this->model_tool_csv_ocext_dmpro->getFileByFileName($new_file_upload);
                
            }elseif($odmpro_tamplate_data['file_url'] && $odmpro_tamplate_data['file_url']){
                
                $file = $this->model_tool_csv_ocext_dmpro->getFileByURL($odmpro_tamplate_data['file_url'],FALSE,FALSE,$odmpro_tamplate_data);
                
            }elseif($odmpro_tamplate_data['file_upload']){
                
                $file = $this->model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['file_upload']);
                
            }
            
            $this->setMaxMemoryUsage();
            
            if(!$file){
                
                $json['error'] .= '<p>'.$this->language->get('entry_file_exits').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_file_exits')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }

                $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));

                return;
                
            }
            
            $json['success'] = '';
            
            $import_result['count_rows'] = 0;
            
            $this->setMaxMemoryUsage();
            
            $start_row_by_add_first_row = 1;
            
            if(isset($odmpro_tamplate_data['add_first_row']) && $odmpro_tamplate_data['add_first_row']){
                    
                    $start_row_by_add_first_row = 0;
                    
            }
            
            if(!$json['error']){
                
                $process_log_file = DIR_CACHE.'anycsv_process_log_'.$odmpro_tamplate_data['id'].'-'.$num_process.'.json';
                
                if(!file_exists($process_log_file)){
                    
                    $this->unlinkProcessLog($odmpro_tamplate_data['id']);
                    
                    $h = fopen($process_log_file, 'w+');
                    
                    fclose($h);
                    
                }
                
                $import_result = $this->model_tool_csv_ocext_dmpro->getCsvRows($file,$start+$start_row_by_add_first_row,$limit,$odmpro_tamplate_data);
                
                unset($file);
                
                $import_result2 = $this->model_tool_csv_ocext_dmpro->importCSV($odmpro_tamplate_data,$type_data_columns_by_type_data,$import_result,$log_data);
                
            }
            
            $this->setMaxMemoryUsage();
            
            $json['finish_time'] = time();
            
            $json['import_time'] = $json['finish_time'] - $json['start_time'];
            
            if(!$json['import_time']){
                
                $json['import_time'] = "<1";
                
            }
            
            $json['memory_usage_txt'] = $this->max_memory_usage['memory_usage_txt'];
            
            $json['image_download_interval'] = '';
            
            if(isset($import_result2['max_memory_usage']['image_download_interval'])){
                
                $json['image_download_interval'] = $import_result2['max_memory_usage']['image_download_interval'];
                
                if(!$json['image_download_interval']){

                    $json['image_download_interval'] = "<1";

                }
                
            }
            
            $json['total'] = $import_result['count_rows'];
            
            $json['nfu'] = 'no_data';
            
            $json['new_nfu'] = 'no_data';
            
            if(isset($this->request->get['nfu'])){
                
                $json['nfu'] = $this->request->get['nfu'];
                
            }
            
            if($json['nfu'] === 'no_data' && ($start+$limit)>$import_result['count_rows'] && $import_result['count_rows']>0){
                
                $json['success'] = $this->language->get('import_success_accomplished');
                
                if(file_exists($process_log_file)){
                    
                    $this->unlinkProcessLog($odmpro_tamplate_data['id']);
                    
                }
                
            }elseif($json['nfu'] !== 'no_data' && !isset($odmpro_tamplate_data['new_file_upload'][ ($json['nfu']+1) ]) && ($start+$limit)>$import_result['count_rows'] && $import_result['count_rows']>0){
                
                $json['success'] = $this->language->get('import_success_accomplished');
                
                if(file_exists($process_log_file)){
                    
                    $this->unlinkProcessLog($odmpro_tamplate_data['id']);
                    
                }
                
            }elseif($json['nfu'] !== 'no_data' && isset($odmpro_tamplate_data['new_file_upload'][ ($json['nfu']+1) ]) && ($start+$limit)>$import_result['count_rows'] && $import_result['count_rows']>0){
                
                $json['success'] = '';
                
                $json['nfu'] += 1;
                
                $json['new_nfu'] = 'new_data';
                
            }
            
            $json['result_demo'] = '';
            
            if(isset($import_result2['result_demo']) && isset($import_result2['result_demo']['results'])){
                
                $json['result_demo'] = $this->getHTMLDemoResults($import_result2['result_demo']['results']);
                
            }
            
            $this->response->addHeader('Content-Type: application/json');$this->response->setOutput(json_encode($json));
            
        }
        
        public function getHTMLDemoResults($array){
        
            $result = '';
            
            if(is_array($array) && $array){

                $result = '<table class="table table-bordered table-hover">';

                foreach($array as $array_name => $array_row){

                    $array_name = str_replace('_id', '', $array_name);
                    
                    $result .= '<tr><td colspan="4" style="color:white; background:#2E8B57; font-size: 16px;">Сущность данных: <b>'.$array_name.'</b></td></tr>';
                    
                    if(is_array($array_row) && $array_row){

                        foreach($array_row as $array_name2 => $array_row2){
                            
                            if(trim($array_name2)>=2147483700){
                                
                                $array_name2 = "Пока неизвестен";
                                
                            }
                            
                            if(is_array($array_row2) && $array_row2){
                                
                                $result .= '<tr><td><table class="table table-bordered table-hover" style="margin-bottom:0px;"><tr><td>'.$array_name2.'</td><td><table class="table table-bordered table-hover" style="margin-bottom:0px;">';
                                
                                foreach($array_row2 as $array_name3 => $array_row3){
                                    
                                    $result .= '<tr><td colspan="4" style="color:white; background:#3CB371; font-size: 14px;">Таблица: <b>'.$array_name3.'</td></tr>';
                                    
                                    $result_by_action = array();
                                    
                                    if(is_array($array_row3)){
                                
                                        foreach($array_row3 as $sql_action => $array_row4){
                                            
                                            $result_by_action[$sql_action] = $array_row4;
                                            
                                        }
                                        
                                    }
                                    
                                    if($result_by_action){
                                        
                                        foreach ($result_by_action as $action => $columns) {
                                            
                                            $result .= '<tr><td colspan="4" ';
                                            
                                            if($action=='insert'){
                                                
                                                
                                                $result .= 'style="color:white; background:orange; font-size: 12px;">Действие: <b>INSERT - вставка строк с данными, которых ранее не было';
                                                
                                                
                                            }
                                            elseif($action=='update'){
                                                
                                                $result .= 'style="color:white; background:#66CDAA; font-size: 12px;">Действие: <b>UPDATE - обновление данных в базе';
                                                
                                            }
                                            elseif($action=='delete'){
                                                
                                                $result .= 'style="color:white; background:red; font-size: 12px;">Действие: <b>DELETE - Удаление строк с данными';
                                                
                                            }
                                            
                                            $result .= '</b></td></tr><tr><td colspan="4"><table  class="table table-bordered table-hover" style="margin-bottom:0px;"><tr style="background:#E0FFFF"><td style="text-align:center;font-weight:bold">Колонка<span data-toggle="tooltip" title="" data-original-title="Название колонки соответствующей таблицы базы данных"></span></td><td style="text-align:center;font-weight:bold">Сейчас<span data-toggle="tooltip" title="" data-original-title="Значение в колонке до того, как оно будет изменено"></span></td><td style="text-align:center;font-weight:bold">Будет вставлено<span data-toggle="tooltip" title="" data-original-title="Значение, на которое будет изменено предыдущее значение"></span></td><td style="text-align:center;font-weight:bold; min-width:150px !important;">Где</td></tr>';
                                            
                                            if($columns){
                                                
                                                foreach ($columns as $sql_value) {

                                                    $result .= '<tr><td>'.$sql_value['column'].'</td><td>'.$sql_value['last_value'].'</td><td>'.$sql_value['new_value'].'</td><td>'.$sql_value['where'].'</td></tr>';

                                                }
                                                
                                            }
                                            else{
                                                
                                                $result .= '<tr><td colspan="4">Нет данных для вставки</td></tr>';
                                                
                                            }
                                            
                                            
                                            $result .= '</td></tr></table>';
                                            
                                        }
                                        
                                    }
                                    
                                }
                                
                                $result .= '</table></td></tr></table></td></tr>';
                                
                            }
                            else{
                                
                                $result .= '<tr><td colspan="4"><div class="alert alert-info">Нет данных для обработки</div></td></tr>';
                                
                            }
                            
                        }

                    }
                    else{

                        $result .= '<tr><td colspan="4"><div class="alert alert-info">Нет данных для обработки</div></td></tr>';

                    }

                }

                $result .= '</table>';

            }else{

                $result = "<div class='alert alert-info'>Нет данных для обработки</div>";

            }

            return $result;

        }
        
        public function getHTMLFromArray($array){
        
            $result = '';

            if(is_array($array)){

                $result = '<table class="table table-bordered table-hover">';

                foreach($array as $array_name => $array_row){

                    $result .= '<tr><td>'.$array_name.'</td>';
                    if(is_array($array_row)){

                        $result .= '<td>'.$this->getHTMLFromArray($array_row).'</td></tr>';

                    }else{

                        $result .= '<td>'.$array_row.'</td></tr>';

                    }

                }

                $result .= '</table>';

            }else{

                $result = $array;

            }

            return $result;

        }
        
        public function unlinkProcessLog($id) {
	
            if(is_dir(DIR_CACHE)){
	    
                $result = scandir(DIR_CACHE);

                foreach ($result as $file_name) {

                    if($file_name!=='.' && $file_name!=='..' && strstr($file_name,'anycsv_process_log_'.$id)){
                        
                        unlink(DIR_CACHE.$file_name);

                    }

                }

            }

            return;

        }
        
        private function getFloat($string){
            
            $find = array('-',',',' ');
            
            $replace = array('.','.','');
            
            $result = (float)str_replace($find, $replace, $string);
            
            return $result;
        }
        
        public function getAttributeOrFilterGroups($language_id,$type_data_column) {
            
            if($type_data_column=='attribute_name' || $type_data_column=='attribute_values_whis_attrubute_name'){
                
                $table = 'attribute_group_description';
                
            }
            
            if($type_data_column=='filter_name' || $type_data_column=='filter_values_whis_filter_name'){
                
                $table = 'filter_group_description';
                
            }
            
            if(!$language_id){
                
                $language_id = (int)$this->config->get('config_language_id');
                
            }
            
            $sql = "SELECT * FROM " . DB_PREFIX . $table." WHERE language_id = '" . $language_id . "' ";

            $query = $this->db->query($sql);

            return $query->rows;
	}
        
        
        
        
        
        public function getOptions($data = array('start'=>0,'limit'=>10000)) {
            
		$sql = "SELECT *, (SELECT agd.name FROM `" . DB_PREFIX . "option_description` agd WHERE agd.option_id = a.option_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS option_name FROM `" . DB_PREFIX . "option` a LEFT JOIN `" . DB_PREFIX . "option_description` ad ON (a.option_id = ad.option_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                
                $sql .= " ORDER BY option_name, ad.name";
                
		$sql .= " ASC";
                
                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                
		$query = $this->db->query($sql);
                
                $result = array();
                
                if($query->rows){
                    
                    foreach ($query->rows as $value) {
                        
                        $result[$value['option_id']] = $value;
                        
                    }
                }
                
                ksort($result);
                
		return $result;
	}
        
        
        
        
        public function getValuesOptions($data = array('start'=>0,'limit'=>10000)) {
            
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "option_description agd WHERE agd.option_id = a.option_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS option_name FROM " . DB_PREFIX . "option a LEFT JOIN " . DB_PREFIX . "option_description ad ON (a.option_id = ad.option_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                
                $sql .= " ORDER BY option_name, ad.name";
                
		$sql .= " ASC";
                
                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                
		$query = $this->db->query($sql);
                
                $result = array();
                
                if($query->rows){
                    
                    foreach ($query->rows as $value) {
                        
                        $sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "option_value_description agd WHERE agd.option_value_id = a.option_value_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS option_value_name FROM " . DB_PREFIX . "option_value a LEFT JOIN " . DB_PREFIX . "option_value_description ad ON (a.option_value_id = ad.option_value_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.option_id=".$value['option_id'];
                
                        $sql .= " ORDER BY option_value_name, ad.name";

                        $sql .= " ASC";

                        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                        
                        $query2 = $this->db->query($sql);
                        
                        if($query2->rows){
                    
                            foreach ($query2->rows as $value2) {

                                $result[$value2['option_value_id']] = $value2;
                                $result[$value2['option_value_id']]['optiongroup'] = $value['name'];
                                
                            }
                            
                        }
                        
                    }
                }
                
                ksort($result);
                
		return $result;
	}
        
        
        
        
        
        
        
        
        public function getCSVAsHTML(){
            
            if(isset($this->request->get['file_name']) && isset($this->request->get['odmpro_tamplate_data_id'])){
                
                $this->load->model('tool/csv_ocext_dmpro');
                
                $odmpro_tamplate_data_id = $this->request->get['odmpro_tamplate_data_id'];

                $config_odmpro_tamplate_data = $this->model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data',TRUE);
                
                $odmpro_tamplate_data = array();

                if(isset($config_odmpro_tamplate_data[$odmpro_tamplate_data_id])){

                    $odmpro_tamplates_data = $config_odmpro_tamplate_data;

                    $odmpro_tamplate_data = $odmpro_tamplates_data[$odmpro_tamplate_data_id];

                }
                
                if(!$odmpro_tamplate_data){
                    
                    exit('Сначала необходимо сохранить профиль настроек');
                    
                }
                
                $file = $this->model_tool_csv_ocext_dmpro->getFileByFileName($this->request->get['file_name']);
                
                $import_result = $this->model_tool_csv_ocext_dmpro->getCsvRows($file,0,10000,$odmpro_tamplate_data);
                
                if(isset($import_result['data'])){
                    
                    ?>
<table>

                        <?php
                    
                    foreach($import_result['data'] as $tr){
                        
                        ?>
    <tr>
        
        
        <?php foreach($tr as $td){ ?>
        
        <td style="font-size:11px; border: 1px solid #ccc; padding: 3px;"><?php echo htmlentities(strip_tags($td),ENT_QUOTES,'UTF-8'); ?></td>
        
        <?php } ?>
        
    </tr>

                        <?php
                        
                    }
                    
                    ?>
                        
</table>
                        <?php
                    
                    
                }else{
                    
                    exit('Ошибка вывода информации');
                    
                }
                
            }else{
                
                exit('Ошибка вывода информации');
                
            }
            
            
            
        }

        public function getCategories() {
            $this->load->language($this->path_oc_version.'/abcxyzanalysis');
            $data['text_no_manufacturers'] = $this->language->get('text_no_manufacturers');
            $data['text_select_all'] = $this->language->get('text_select_all');
            
            $this->load->model('catalog/category');
            $data['categories'] = $this->model_catalog_category->getCategories(array('limit'=>10000,'start'=>0));
            $data['categories_selected'] = array();
            if(isset($this->request->get['c']) && $this->request->get['c']){
                $categories_selected = explode('_', $this->request->get['c']);
                foreach ($categories_selected as $category_selected) {
                    $data['categories_selected'][$category_selected] = $category_selected;
                }
            }
            $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/abcxyzanalysis_categories'.$this->ftype, $data));
        }
        
        public function getManufacturers() {
            $this->load->language($this->path_oc_version.'/abcxyzanalysis');
            $data['text_no_manufacturers'] = $this->language->get('text_no_manufacturers');
            $data['text_select_all'] = $this->language->get('text_select_all');
            
            $this->load->model('catalog/manufacturer');
            $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers(array('limit'=>10000,'start'=>0));
            $data['manufacturers_selected'] = array();
            if(isset($this->request->get['m']) && $this->request->get['m']){
                $manufacturers_selected = explode('_', $this->request->get['m']);
                foreach ($manufacturers_selected as $manufacturer_selected) {
                    $data['manufacturers_selected'][$manufacturer_selected] = $manufacturer_selected;
                }
            }
            $this->response->setOutput($this->{$this->loader_name}->view($this->path_oc_version.'/abcxyzanalysis_manufacturers'.$this->ftype, $data));
        }

        public function getNotifications() {
		sleep(1);
		$this->load->language($this->path_oc_version.'/abcxyzanalysis');
		$response = $this->getNotificationsCurl();
		$json = array();
		if ($response===false) {
			$json['message'] = '';
			$json['error'] = $this->language->get( 'error_notifications' );
		} else {
			$json['message'] = $response;
			$json['error'] = '';
		}
		$this->response->setOutput(json_encode($json));
	}
        
        protected function curl_get_contents($url) {
            if(function_exists('curl_version')){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                $output = curl_exec($ch);
                curl_close($ch);
                return $output;
            }else{
                $output['ru'] = 'Проверка версии недоступна. Включите php расширение - CURL на Вашем хостинге';
                $output['en'] = 'You can not check the version. Enable php extension - CURL on your hosting';
                $language_code = $this->config->get( 'config_admin_language' );
                if(isset($output[$language_code])){
                    return $output[$language_code];
                }else{
                    return $output['en'];
                }
            }
	}

	public function getNotificationsCurl() {
		$language_code = $this->config->get( 'config_admin_language' );
		$result = $this->curl_get_contents("http://".$this->this_ocext_host.".com/index.php?route=information/check_update_version&license=".HTTP_SERVER."&version_opencart=".VERSION."&version_ocext=".$this->this_version."&extension=".$this->this_extension."&language_code=$language_code");
		if (stripos($result,'<html') !== false) {
			return '';
		}
		return $result;
	}
        
        public function getAttributeOrFilterGroupsByDBTable($language_id,$db_table) {
            
            $table = '';
            
            if(stristr($db_table, 'attribute')){
                
                $table = 'attribute_group_description';
                
            }elseif(stristr($db_table, 'filter')){
                
                $table = 'filter_group_description';
                
            }
            
            if(!$language_id){
                
                $language_id = (int)$this->config->get('config_language_id');
                
            }
            
            $reslut = array();
            
            if($language_id && $table){
                
                $sql = "SELECT * FROM `" . DB_PREFIX . $table."` WHERE language_id = '" . $language_id . "' ";

                $query = $this->db->query($sql);
                
                $reslut = $query->rows;
                
            }

            return $reslut;
	}
        
        public function getAttributes($delimeter='_',$data = array('start'=>0,'limit'=>10000)) {
            
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group_name FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                
                $sql .= " ORDER BY attribute_group_name, ad.name";
                
		$sql .= " ASC";
                
                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                
		$query = $this->db->query($sql);
                
                $result = array();
                
                if($query->rows){
                    
                    foreach ($query->rows as $value) {
                        
                        $result[$value['attribute_group_id'].$delimeter.$value['attribute_id']] = $value;
                        
                    }
                }
                
                ksort($result);
                
		return $result;
	}
        
        public function getAnyXLSResult($odmpro_tamplate_data){
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $result = $this->model_tool_csv_ocext_dmpro->getAnyXLSResult($odmpro_tamplate_data);
            
            return $result;
            
        }
        
        public function getAnyYMLResult($odmpro_tamplate_data){
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $result = $this->model_tool_csv_ocext_dmpro->getAnyYMLResult($odmpro_tamplate_data);
            
            return $result;
            
        }
        
        public function getAnyXMLResult($odmpro_tamplate_data){
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $result = $this->model_tool_csv_ocext_dmpro->getAnyXMLResult($odmpro_tamplate_data);
            
            return $result;
            
        }

        public function getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation=0) {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $result = $this->model_tool_csv_ocext_dmpro->getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation);
            
            return $result;
            
        }
        
        public function getWelcomeWindow(){
            
            if(function_exists('curl_version')){
                
                $language_code = $this->config->get( 'config_admin_language' );
                $url = "https://api.e-distributer.com/v1/welcome/?license=".HTTP_SERVER."&version_opencart=".VERSION."&version_ocext=".$this->this_version."&extension=".$this->this_extension."&language_code=".$language_code;
                $curl = curl_init($url);
                $curloptions = array(
                    CURLOPT_CUSTOMREQUEST  =>"GET",  
                    CURLOPT_POST           =>FALSE,      
                    CURLOPT_USERPWD     =>":",
                    CURLOPT_RETURNTRANSFER => TRUE,  
                    CURLOPT_HEADER         => FALSE,
                );
                curl_setopt_array($curl, $curloptions);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
                $welcome = curl_exec($curl);
                $errmsg  = curl_error($curl);
                curl_close($curl);
                if(!$errmsg){
                    
                    echo $welcome;
                    
                }
                
            }else{
                
                echo "РУС: Расширение php CURL выключено. Включите расширение, чтобы получать важную информацию об этом продукте<br>ENG: Extension php CURL off. Turn the extension to receive important information on this product<br>DE: Extension php CURL off. Turn the extension to receive important information on this product<br>FR: Extension cURL de PHP off. Tournez l'extension pour recevoir des informations importantes sur ce produit";
                
            }
            
            exit();
	    
	    
	    
	    
            
        }
        
        public function uninstall() {
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->anyxls = $this->model_tool_csv_ocext_dmpro->uninstall();
            
        }
        
        public function getSupplierSettingView() {
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $supplier_setting = $this->model_tool_csv_ocext_dmpro->getSupplierSettingView($this->request->get['source_id'],$this->request->post);
            
            $this->response->setOutput($supplier_setting);
            
        }
        
        
}
?>