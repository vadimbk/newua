<?php
class ControllerStartupError extends Controller {
	public function index() {
		$this->registry->set('log', new Log($this->config->get('config_error_filename')));
		
		set_error_handler(array($this, 'handler'));	
	}



//// updated - remove excessive PHP NOTICE errors

	public function handler($code, $message, $file, $line) {
	    // error suppressed with @
	    if (error_reporting() === 0) {
	        return false;
	    }
	
	    switch ($code) {
	        // Полностью убираем Notice и Warning из обработки
	        case E_ERROR:
	        case E_USER_ERROR:
	            $error = 'Fatal Error';
	            break;
	        case E_PARSE:
	            $error = 'Parse Error';
	            break;
	        case E_CORE_ERROR:
	            $error = 'Core Error';
	            break;
	        case E_COMPILE_ERROR:
	            $error = 'Compile Error';
	            break;
	        default:
	            // Если это Notice/Warning/Deprecated - просто возвращаем true без логирования
	            return true;
	    }
	
	    if ($this->config->get('config_error_display')) {
	        echo '<b>' . $error . '</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . '</b>';
	    }
	
	    if ($this->config->get('config_error_log')) {
	        $this->log->write('PHP ' . $error . ':  ' . $message . ' in ' . $file . ' on line ' . $line);
	    }
	
	    return true;
	}	


} 
