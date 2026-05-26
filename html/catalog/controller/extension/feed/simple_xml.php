<?php

class ControllerExtensionFeedSimpleXml extends Controller {
	private $currencies = array();
	private $categories = array();
	private $eol = ""; 

	public function index() {
		if ($this->config->get('config_remarketing_code')) {
		
		$this->load->model('extension/feed/simple_xml');
		$this->load->model('tool/image');		
			
		$this->eol = "\n";
		$output  = '<?xml version="1.0" encoding="UTF-8"?>' . $this->eol;
		$output .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . $this->eol;
		$output .= '<channel>' . $this->eol;
		$output .= '<title>' . $this->config->get('config_name') . '</title>' . $this->eol ;
		$output .= '<link>' . HTTPS_SERVER . '</link>' . $this->eol ;
		$output .= '<description>' . $this->config->get('config_name') . '</description>' . $this->eol ;
		
		$offers_currency = $this->config->get('remarketing_google_currency');

		$this->load->model('localisation/currency');
		$this->load->model('tool/image');
		
		if (!$this->currency->has($offers_currency)) exit();
		$decimal_place = $this->currency->getDecimalPlace($offers_currency);
		$shop_currency = $this->config->get('config_currency');
		$decimal = (int)$this->currency->getDecimalPlace($offers_currency);
		
		$this->setCurrency($offers_currency, 1);
		$currencies = $this->model_localisation_currency->getCurrencies();
		
		$products = $this->model_extension_feed_simple_xml->getProducts();
		
		foreach ($products as $product) { 
			$output .= '<item>'. $this->eol; 
			$output .= '<g:id>' . ($this->config->get('remarketing_google_id') == 'id' ? $product['product_id'] : $product['model']) . '</g:id>' . $this->eol;
			$output .= '<g:title><![CDATA[' . $this->prepareField($product['name']) . ']]></g:title>' . $this->eol;
			$output .= '<g:description><![CDATA[' . $this->prepareField($product['description']) . ']]></g:description>' . $this->eol; 
			$output .= '<g:link>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</g:link>' . $this->eol;
			$output .= '<g:image>' . $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_popup_width'), $this->config->get($this->config->get('config_theme') . '_image_popup_height')) . '</g:image>' . $this->eol;
			$output .= '<g:condition>new</g:condition>' . $this->eol;
			$output .= '<g:availability>' . ($product['quantity'] > 0 ? 'in stock' : 'out of stock') . '</g:availability>' . $this->eol;
			$output .= '<g:price>' . number_format($this->currency->convert($this->tax->calculate($product['price'], $product['tax_class_id']), $shop_currency, $offers_currency), $decimal, '.', '') . ' ' . $offers_currency . '</g:price>';
			$output .= '<g:brand>' . $product['manufacturer'] . '</g:brand>';
			$output .= '</item>'. $this->eol;
		} 
		
		$output .= '</channel>'. $this->eol;
		$output .= '</rss>';

		$this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);
		
		}
	}
	
	private function setCurrency($id, $rate = 'CBRF', $plus = 0) {
		$allow_id = array('RUR', 'RUB', 'USD', 'BYR', 'KZT', 'EUR', 'UAH');
		if (!in_array($id, $allow_id)) {
			return false;
		}
		$allow_rate = array('CBRF', 'NBU', 'NBK', 'CB');
		if (in_array($rate, $allow_rate)) {
			$plus = str_replace(',', '.', $plus);
			if (is_numeric($plus) && $plus > 0) {
				$this->currencies[] = array(
					'id'=>$this->prepareField(strtoupper($id)),
					'rate'=>$rate,
					'plus'=>(float)$plus
				);
			} else {
				$this->currencies[] = array(
					'id'=>$this->prepareField(strtoupper($id)),
					'rate'=>$rate
				);
			}
		} else {
			$rate = str_replace(',', '.', $rate);
			if (!(is_numeric($rate) && $rate > 0)) {
				return false;
			}
			$this->currencies[] = array(
				'id'=>$this->prepareField(strtoupper($id)),
				'rate'=>(float)$rate
			);
		}

		return true;
	}
	
	private function setCategory($name, $id, $parent_id = 0) {
		$id = (int)$id;
		if ($id < 1 || trim($name) == '') {
			return false;
		}
		if ((int)$parent_id > 0) {
			$this->categories[$id] = array(
				'id'=>$id,
				'parentId'=>(int)$parent_id,
				'name'=>$this->prepareField($name)
			);
		} else {
			$this->categories[$id] = array(
				'id'=>$id,
				'name'=>$this->prepareField($name)
			);
		}

		return true;
	}
	
	private function getElement($attributes, $element_name, $element_value = '') {
		$retval = '<' . $element_name . ' ';
		foreach ($attributes as $key => $value) {
			$retval .= $key . '="' . $value . '" ';
		}
		$retval .= $element_value ? '>' . $this->eol . $element_value . '</' . $element_name . '>' : '/>';
		$retval .= $this->eol;

		return $retval;
	}
	
	private function prepareField($field) {

		$field = htmlspecialchars_decode($field);
		$field = strip_tags($field);
		
		$from = array('"', '&', '>', '<', '°', '\'');
		$to = array('&quot;', '&amp;', '&gt;', '&lt;', '&#176;', '&apos;');
		$field = str_replace($from, $to, $field);
		
		$field = preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F]+#is', ' ', $field);

		return trim($field);
	}
}