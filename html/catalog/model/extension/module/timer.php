<?php
/*
  *******************************************************************************
  *  Module: Bulk specials editor + the countdown timer
  *  
  *  Web-site: http://opencart-modules.com
  *  Email: dev.dashko@gmail.com
  *  © Leonid Dashko
  *
  *  Below source-code or any part of the source-code cannot be resold or distributed.
  ******************************************************************************
*/

class ModelExtensionModuleTimer extends Model {
	public function checkExistenceExtension($type, $extension_name) {
	  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($extension_name) . "' ");

	  return $query->rows;
	}

    public function getFullDateTime($date_end, $weekdays = '', $hours = '') {
        $old_date_end = $date_end;

        $newdate = explode('-', $date_end);
        $new_date_end = '';

        $all_weekdays = '0,1,2,3,4,5,6';
        $all_hours = '00,01,02,03,04,05,06,07,08,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23';

        // Looping
        if ($newdate[0] == 0000) $newdate[0] = date("Y");
        if ($newdate[1] == 00) $newdate[1] = date("m");
        if ($newdate[2] == 00) $newdate[2] = date("d");
    
        // To prevent a date error
        if($newdate[2] != date("d"))
            $newdate[2] = $newdate[2]-1;
        
        $date_end = implode("-", $newdate);
        // All days and hours, then return date end
        if (!$weekdays || !$hours || ($weekdays == $all_weekdays && $hours == $all_hours)) {
            $new_date_end = $date_end .  " 23:59:59";

        // The case when selected all hours, but not all weekdays
        } elseif ($weekdays && $weekdays != $all_weekdays) {
            $weekday = date('w');

            $days_offset = $this->getWeekdayOffset($weekday, $weekdays);

            $new_date_end = date('Y-m-d', strtotime('+' . $days_offset . ' day')) . ' 23:59:59';
        }

        // Additional check for hours
        if ($hours && $hours != $all_hours) {
            $hour = date('H', strtotime('+1 hour'));
            $hours_offset = 0;

            // if ($date_end == date('Y-m-d'))
            //     $hour = '00';

            $plus_hours = false;

            // If the hour is not found, go out from the loop
            while (strpos($hours, (string) $hour) !== false) {
                $hour++;
                $hours_offset++;

                if ($hour == 24) {
                    $hour = '00';
                }

                if ($hour == 1) {
                    $plus_hours = true;
                }

                if ($hour < 10 && $hour != '00') {
                    $hour = '0' . $hour;
                }
            }

            $hour--;
            
            if ($hour < 0)
                $hour = 23;

            if ($hour < 10)
                $hour = '0' . $hour;
              
            if ($plus_hours) {
                $days_offset = 1; // + 1 day
                $weekday = date("w", strtotime('+1 day'));

                // If the weekday is found, then go out from the loop
                while (strpos($weekdays, (string) $weekday) === false) { // Not found
                    $weekday++;
                    $days_offset++;

                    if($weekday == 7) {
                        $weekday = 0;
                    }
                }
                  
                $new_date_end = date('Y-m-d ', strtotime('+' . $days_offset . ' day')) . $hour . ':59:59';
            } else {
                $new_date_end = date('Y-m-d ') . $hour . ':59:59';
            }
        }
          
        // Checking on the new date end and old date end exceeding
        if ($old_date_end != '0000-00-00' && strtotime($date_end . ' 23:59:59') < strtotime($new_date_end)) {
            return $old_date_end;
        } else {
            return $new_date_end;
        }
    }

    public function getWeekdayOffset($weekday, $weekdays) {
        $days_offset = 0;
        
        // If the weekday is not found, then go out from the loop
        while (strpos($weekdays, (string) $weekday) !== false) {                  
            $weekday++;
            $days_offset++;

            if($weekday == 7) {
                $weekday = 0;
            }
        }

        if ($days_offset != 0)
            $days_offset--;

        return $days_offset;
    }

	public function getSpecialDateDiff($special_datetime_end) {
        if ($special_datetime_end == '0000-00-00') {
            $special_datetime_end = $this->getFullDateTime($special_datetime_end);
        }

        $special_datetime_end = strtotime($special_datetime_end);
        
        // calculate the difference between dates
	    $special_date_diff = $special_datetime_end - time();
  
		return $special_date_diff;
	}

	public function calculateTotalDiscount($price, $special_price) {
		return round( (1 - ($special_price / $price)) * 100);
	}

	// Loading css style settings that we mentioned in the admin panel
	public function getCustomCSSStyles() {
		$data = array();

		// Loading Additional Settings for Catalog section
		$additional_catalog_settings = $this->config->get('timer_additional_catalog_settings');
		if(!empty($additional_catalog_settings)){
			return $this->generateCustomCSSStyles($additional_catalog_settings);
		} else {
			return '';
		}
	}

	public function is_var_exist($var) {
		$var = trim($var);

		if(!isset($var) || $var == "") {
			return false;
		} else {
			return true;
		}
	}

	public function generateCustomCSSStyles($data) {
		$count_good_params = 0;

		foreach ($data as $key => $value) {
			$$key = $value;

			if($this->is_var_exist($value))
				$count_good_params++;
		}

		$styles = '';
		// if the user didn't choose any parametres in the admin panel, we don't show styles
		if($count_good_params !== 0):
			$styles = '<style>';
			  
			if($this->is_var_exist($badge_color) 
			    || $this->is_var_exist($badge_font_size)
			    || $this->is_var_exist($badge_top)
			    || $this->is_var_exist($badge_font_weight)
			):
				# Detailed product page
			    $styles .= '.discount-sticker {';
					if($this->is_var_exist($badge_color))
						$styles .= 'background-color: '.$badge_color.';';

					if($this->is_var_exist($badge_font_size))
						$styles .= 'font-size: '.$badge_font_size.'px;';

					if($this->is_var_exist($badge_top))
						$styles .= 'top: '.$badge_top.'px;';

					if($this->is_var_exist($badge_font_weight))
						$styles .= 'font-weight: '.$badge_font_weight.';';
			   	$styles .= '} ';
			  
				if($this->is_var_exist($badge_color))   	  
					$styles .= '.discount-sticker::before {border-color: '.str_repeat($badge_color . " ", 3).' transparent;} ';
			endif;


			if($this->is_var_exist($product_timer_price_block_border_size)
				|| $this->is_var_exist($product_timer_price_block_border_color)
				|| $this->is_var_exist($product_timer_price_block_padding)
				|| $this->is_var_exist($product_timer_price_block_margin)
			):
				$styles .= '.product-timer-block {';
				  	if($this->is_var_exist($product_timer_price_block_border_size) && $this->is_var_exist($product_timer_price_block_border_color))
					    $styles .= 'border: '.$product_timer_price_block_border_size.'px solid '.$product_timer_price_block_border_color.';';
				 
					if($this->is_var_exist($product_timer_price_block_padding))
					    $styles .= 'padding: '.$product_timer_price_block_padding.';';

					if($this->is_var_exist($product_timer_price_block_margin))
						$styles .= 'margin: '.$product_timer_price_block_margin.';';
				$styles .= '} ';
			endif;


			if($this->is_var_exist($product_timer_special_price_color)
				|| $this->is_var_exist($product_timer_special_price_font_size)
			):
				$styles .= '.product-timer-block .special-price {';
					if($this->is_var_exist($product_timer_special_price_color))
						$styles .= 'color: '.$product_timer_special_price_color.';';

					if($this->is_var_exist($product_timer_special_price_font_size))
						$styles .= 'font-size: '.$product_timer_special_price_font_size.'px;';
				$styles .= '} ';  
			endif;


			if($this->is_var_exist($product_timer_old_price_color) 
				|| $this->is_var_exist($product_timer_old_price_font_size)
			):
				$styles .= '.product-timer-block .old-price {';
					if($this->is_var_exist($product_timer_old_price_color))
						$styles .= 'color: '.$product_timer_old_price_color.';';

					if($this->is_var_exist($product_timer_old_price_font_size))
						$styles .= 'font-size: '.$product_timer_old_price_font_size.'px;';
				$styles .= '} ';
			endif;


			if($this->is_var_exist($product_timer_special_price_margin))
				$styles .= '.product-timer-block .prices-block .special-price {margin: '.$product_timer_special_price_margin.';} ';


			if($this->is_var_exist($product_timer_old_price_margin))
				$styles .= '.product-timer-block .prices-block .old-price {margin: '.$product_timer_old_price_margin.';} ';


			if(isset($product_timer_price_block_full_width))
				$styles .= '.product-timer-block .timer, .product-timer-block .prices-block {display: block; width: 100%;} ';


			if($this->is_var_exist($product_timer_block_timer_text)
				|| $this->is_var_exist($product_timer_block_timer_text_font_size)
			):
				/* Padding of label (above the timer) */
				$styles .= '.product-timer-block .timer .text {';
				  	if($this->is_var_exist($product_timer_block_timer_text))
				    	$styles .= 'padding: '.$product_timer_block_timer_text.';';
					  
				  	if($this->is_var_exist($product_timer_block_timer_text_font_size))
				    	$styles .= 'font-size: '.$product_timer_block_timer_text_font_size.'px;';
				$styles .= '} ';
			endif;


			if($this->is_var_exist($product_timer_block_countdown_border_size)
				|| $this->is_var_exist($product_timer_block_countdown_border_color)
				|| $this->is_var_exist($product_timer_block_countdown_background_color)
				|| $this->is_var_exist($product_timer_block_countdown_text_color)
				|| $this->is_var_exist($product_timer_block_countdown_margin)
				|| $this->is_var_exist($product_timer_block_countdown_padding)
			):
				/* Timer settings on the detailed product page */
				$styles .= '.product-timer-block .is-opencartCountdown {';
					if($this->is_var_exist($product_timer_block_countdown_border_size) && $this->is_var_exist($product_timer_block_countdown_border_color))
						$styles .= 'border: '.$product_timer_block_countdown_border_size.'px solid '.$product_timer_block_countdown_border_color.';';

					if($this->is_var_exist($product_timer_block_countdown_background_color))
						$styles .= 'background-color: '.$product_timer_block_countdown_background_color.';';

					if($this->is_var_exist($product_timer_block_countdown_text_color))
						$styles .= 'color: '.$product_timer_block_countdown_text_color.';';

					if($this->is_var_exist($product_timer_block_countdown_margin))
						$styles .= 'margin: '.$product_timer_block_countdown_margin.';';

					if($this->is_var_exist($product_timer_block_countdown_padding))
						$styles .= 'padding: '.$product_timer_block_countdown_padding.';';
				  $styles .= '} ';
			endif;


			  /* Timer Settings */
			if($this->is_var_exist($product_timer_block_countdown_amount_font_size))
				$styles .= '.product-timer-block .opencartCountdown-section .opencartCountdown-amount {font-size: '.$product_timer_block_countdown_amount_font_size.'%;} ';


			if($this->is_var_exist($product_timer_block_countdown_period_size))
				$styles .= '.product-timer-block .opencartCountdown-section .opencartCountdown-period {font-size: '.$product_timer_block_countdown_period_size.'%;} ';
			 

			/* Timer in other sections of site like categories */
			if($this->is_var_exist($timer_block_countdown_border_size)
				|| $this->is_var_exist($timer_block_countdown_border_color)
				|| $this->is_var_exist($timer_block_countdown_background_color)
				|| $this->is_var_exist($timer_block_countdown_text_color)
				|| $this->is_var_exist($timer_block_countdown_margin)
				|| $this->is_var_exist($timer_block_countdown_padding)
			):
				$styles .= '.timer-block .is-opencartCountdown {';
					if($this->is_var_exist($timer_block_countdown_border_size) && $this->is_var_exist($timer_block_countdown_border_color))
						$styles .= 'border: '.$timer_block_countdown_border_size.'px solid '.$timer_block_countdown_border_color.';';

					if($this->is_var_exist($timer_block_countdown_background_color))
						$styles .= 'background-color: '.$timer_block_countdown_background_color.';';

					if($this->is_var_exist($timer_block_countdown_text_color))
						$styles .= 'color: '.$timer_block_countdown_text_color.';';

					if($this->is_var_exist($timer_block_countdown_margin))
						$styles .= 'margin: '.$timer_block_countdown_margin.';';

					if($this->is_var_exist($timer_block_countdown_padding))
						$styles .= 'padding: '.$timer_block_countdown_padding.';';
				$styles .= '} ';
			endif;


			if($this->is_var_exist($timer_block_countdown_amount_font_size))
				$styles .= '.timer-block .opencartCountdown-section .opencartCountdown-amount {font-size: '.$timer_block_countdown_amount_font_size.'%;} ';


			if($this->is_var_exist($timer_block_countdown_period_size))
				$styles .= '.timer-block .opencartCountdown-section .opencartCountdown-period {font-size: '.$timer_block_countdown_period_size.'%;} ';


			if(isset($timer_block_percentage_discount_full_width)
				|| $this->is_var_exist($timer_block_countdown_period_size)
				|| $this->is_var_exist($timer_block_percentage_discount_background)
				|| $this->is_var_exist($timer_block_percentage_discount_color)
				|| $this->is_var_exist($timer_block_percentage_discount_font_size)
			):
			    $styles .= '.timer-block .percentage-discount {';
					if(isset($timer_block_percentage_discount_full_width))
						$styles .= 'display: block;';

					if($this->is_var_exist($timer_block_countdown_period_size))
						$styles .= 'margin: '.$timer_block_percentage_discount_margin.';';

					if($this->is_var_exist($timer_block_percentage_discount_background))
						$styles .= 'background: '.$timer_block_percentage_discount_background.';';

					if($this->is_var_exist($timer_block_percentage_discount_color))
						$styles .= 'color: '.$timer_block_percentage_discount_color.';';

					if(isset($timer_block_percentage_discount_font_size))
						$styles .= 'font-size: '.$timer_block_percentage_discount_font_size.'px;';
			    $styles .= '} ';
			endif;


			if($this->is_var_exist($timer_block_special_price_color)
				|| $this->is_var_exist($timer_block_special_price_font_size)
			):
				$styles .= '.timer-block .special-price {';
					if($this->is_var_exist($timer_block_special_price_color))
						$styles .= 'color: '.$timer_block_special_price_color.';';

					if($this->is_var_exist($timer_block_special_price_font_size))
						$styles .= 'font-size: '.$timer_block_special_price_font_size.'px;';
				$styles .= '} ';
			endif;


			if($this->is_var_exist($timer_block_old_price_color)
				|| $this->is_var_exist($timer_block_old_price_font_size)
			):
				$styles .= '.timer-block .old-price {';
					if($this->is_var_exist($timer_block_old_price_color))
						$styles .= 'color: '.$timer_block_old_price_color.';';
					if($this->is_var_exist($timer_block_old_price_font_size))
						$styles .= 'font-size: '.$timer_block_old_price_font_size.'px;';
				$styles .= '} ';
			endif;


			if($this->is_var_exist($timer_block_special_price_margin))
				$styles .= '.timer-block .prices-block .special-price {margin: '.$timer_block_special_price_margin.';} ';


			if($this->is_var_exist($timer_block_old_price_margin))
			$styles .= '.timer-block .prices-block .old-price {margin: '.$timer_block_old_price_margin.';} ';

			$styles .= '</style>';
		endif; 

		return $styles;
	}

}