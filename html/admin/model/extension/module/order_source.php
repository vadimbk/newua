<?php
class ModelExtensionModuleOrderSource extends Model {
	public function getChanelOrderList() {
	    
	   if(!isset($_POST['date_start'])){
	       $datebegin = date('Y'). '-' . date('m'). '-01';
	   } else {
	        $datebegin = $_POST['date_start'];
	        
	   }
	   
	   if(!isset($_POST['date_end'])){
	       $dateend = date('Y-m-d');
	       $dateend = strtotime($dateend. ' + 1 days');
	       $dateend = date('Y-m-d', $dateend);
	   } else {
	       $dateend = $_POST['date_end'];
	       $dateend = strtotime($dateend. ' + 1 days');
	       $dateend = date('Y-m-d', $dateend);
	   } 
	   if(isset($_POST['filter_parametr'])){
	       $parametr = $_POST['filter_parametr'];
	   } else {
	       $parametr = 'referer';
	   } 

		$implode = array();

		foreach ($this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$query = $this->db->query("SELECT order_id, order_status_id, SUM(total) as total, DATE_FORMAT(date_added, '%Y-%m-%d') as date, ".$parametr.", COUNT(*) as quantity FROM ".DB_PREFIX."order where date_added BETWEEN STR_TO_DATE('".$datebegin."', '%Y-%m-%d') AND STR_TO_DATE('".$dateend."', '%Y-%m-%d') AND order_status_id IN(" . implode(",", $implode) . ") group by ".$parametr." ORDER BY `total` DESC");
           
	    return $query->rows;
	    
	}	

	public function getReferer() {

	   if(!isset($_POST['date_start'])){
	       $datebegin = date('Y'). '-' . date('m'). '-01';
	   } else {
	       $datebegin = $_POST['date_start'];
	        
	   }
	   
	   if(!isset($_POST['date_end'])){
	       $dateend = date('Y-m-d');
	       $dateend = strtotime($dateend. ' + 1 days');
	       $dateend = date('Y-m-d', $dateend);
	   } else {
	       $dateend = $_POST['date_end'];
	       $dateend = strtotime($dateend. ' + 1 days');
	       $dateend = date('Y-m-d', $dateend);
	   } 

	   if(isset($_POST['filter_parametr'])){
	       $parametr = $_POST['filter_parametr'];
	   } else {
	       $parametr = 'referer';
	   } 
		$implode = array();

		foreach ($this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}

		$query = $this->db->query("SELECT ".$parametr." FROM ".DB_PREFIX."order where date_added BETWEEN STR_TO_DATE('".$datebegin."', '%Y-%m-%d') AND STR_TO_DATE('".$dateend."', '%Y-%m-%d') AND order_status_id IN(" . implode(",", $implode) . ") group by ".$parametr." ORDER BY `total` DESC");
		
		

		return $query->rows;
	}
	public function getOrderReferer($getreferer) {
	    
	   if(!isset($_POST['date_start'])){
	       $datebegin = date('Y'). '-' . date('m'). '-01';
	   } else {
	        $datebegin = $_POST['date_start'];
	   }
	   
	   if(!isset($_POST['date_end'])){
	       $dateend = date('Y-m-d');
	       $dateend = strtotime($dateend. ' + 1 days');
	       $dateend = date('Y-m-d', $dateend);
	   } else {
	       $dateend = $_POST['date_end'];
	       $dateend = strtotime($dateend. ' + 1 days');
	       $dateend = date('Y-m-d', $dateend);
	   } 

		$datebegin = new DateTime($datebegin);
	    $dateend = new DateTime($dateend);
	    $dateend->modify('+1 day');
	    $period = new DatePeriod($datebegin, new DateInterval('P1D'), $dateend);
        $arrayOfDates = array_map(
            function($item){
                return $item->format('Y-m-d');
            }, iterator_to_array($period)
        );
          

		$implode = array();

		foreach ($this->config->get('config_complete_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}


		
		
		$arrayorders = array();
		$r = 0;
		foreach($getreferer as $referer){
			
		if(isset($referer['referer'])) {
			$ref = $referer['referer'];
        } elseif(isset($referer['utm_source'])) {                  
    		$ref = $referer['utm_source'];
        } elseif(isset($referer['utm_medium'])) {
      		$ref = $referer['utm_medium'];
        } elseif(isset($referer['utm_campaign'])) {
 			$ref = $referer['utm_campaign'];
        } elseif(isset($referer['utm_content'])) {
  			$ref = $referer['utm_content'];
        } elseif(isset($referer['utm_term'])) {
        	$ref = $referer['utm_term'];
		}
                         
						
		
		
			

	        $i = 0;
	        foreach($arrayOfDates as $date){
	            $arrayorders[$r][$i] = array(
	                'date' => $date,
	                'name' => $ref,
	                'quantity' => '0',
	                'total' => '0'
	            );   

	        	$sql = "SELECT order_id, order_status_id, SUM(total) as total, DATE_FORMAT(date_added, '%Y-%m-%d') as date, ";


	        	
	        	
				if(isset($referer['referer'])) {
					$sql .= "referer as name";
		        } elseif(isset($referer['utm_source'])) {                  
		    		$sql .= "utm_source as name";
		        } elseif(isset($referer['utm_medium'])) {
		      		$sql .= "utm_medium as name";
		        } elseif(isset($referer['utm_campaign'])) {
		 			$sql .= "utm_campaign as name";
		        } elseif(isset($referer['utm_content'])) {
		  			$sql .= "utm_content as name";
		        } elseif(isset($referer['utm_term'])) {
		        	$sql .= "utm_term as name";
				}

	        	$sql .= ", COUNT(*) as quantity FROM ".DB_PREFIX."order where ";


				if(isset($referer['referer'])) {
					$sql .= "referer = '".$ref."'";
		        } elseif(isset($referer['utm_source'])) {                  
		    		$sql .= "utm_source = '".$ref."'";
		        } elseif(isset($referer['utm_medium'])) {
		      		$sql .= "utm_medium = '".$ref."'";
		        } elseif(isset($referer['utm_campaign'])) {
		 			$sql .= "utm_campaign = '".$ref."'";
		        } elseif(isset($referer['utm_content'])) {
		  			$sql .= "utm_content = '".$ref."'";
		        } elseif(isset($referer['utm_term'])) {
		        	$sql .= "utm_term = '".$ref."'";
				}

	        	$sql .= " AND DATE_FORMAT(date_added, '%Y-%m-%d') = '".$date."' AND order_status_id IN(" . implode(",", $implode) . ") group by date";

	            $query = $this->db->query($sql);
	           
	                foreach ($query->rows as $query) {
	                        $arrayorders[$r][$i] = array(
	                            'date' => $query['date'],
	                            'name' => $query['name'],
	                            'quantity' => $query['quantity'],
	                            'total' => $query['total']
	                            
	                        );	                        
	                }                     
	         $i++;   
	        }
			$r++;

	    }

	    return $arrayorders;   
	}	


	public function install() {
		$oquery = $this->db->query("DESCRIBE " . DB_PREFIX . "order");
	
		foreach ($oquery->rows as $oresult) {
			$ofields[] = $oresult['Field'];
		}
		if (!in_array('referer', $ofields)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `referer` VARCHAR(50) NOT NULL AFTER `date_modified`");
		}
		if (!in_array('utm_source', $ofields)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `utm_source` VARCHAR(50) NOT NULL AFTER `referer`");
		}
		if (!in_array('utm_medium', $ofields)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `utm_medium` VARCHAR(50) NOT NULL AFTER `utm_source`");
		}
		if (!in_array('utm_campaign', $ofields)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `utm_campaign` VARCHAR(50) NOT NULL AFTER `utm_medium`");
		}
		if (!in_array('utm_content', $ofields)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `utm_content` VARCHAR(50) NOT NULL AFTER `utm_campaign`");
		}
		if (!in_array('utm_term', $ofields)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD `utm_term` VARCHAR(50) NOT NULL AFTER `utm_content`");
		}
	}






	
}
