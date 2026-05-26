<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
		$this->load->language('checkout/success');
		
		if ( isset($this->session->data['order_id']) && ( ! empty($this->session->data['order_id']))  ) {
		$this->session->data['last_order_id'] = $this->session->data['order_id'];}

		if (isset($this->session->data['order_id'])) {
			
                        
                        $data['id_order'] = $this->session->data['order_id'];
                        
                        $data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			
			
			$data['products'][] = array(
				
				'product_id'   => $product['product_id'],
				'quantity'  => $product['quantity'],
			    'prices'     => round($product['price']*45)
				
			);
			
			}
                  
                         $data['dataLayer']["transactionTotal"] =0;
				
		$data['dataLayer']["transactionProducts"] = "[";
                        $fb_str="";
			foreach ($this->cart->getProducts() as $product) {
			        if(empty($fb_str))$fb_str_pref="";
                                else $fb_str_pref=", ";
                                $fb_str=$fb_str.$fb_str_pref.'\''.$product['product_id'].'\'';
                                                    
				$dynx_itemid[]=$product['model'];
				$dynx_totalvalue[] = number_format($product['total'], 2, '.', '');
				
				$product['~price'] = number_format($product['price'], 2, '.', '');
				
                                
                                $data['price'] = $this->currency->format($product['total'], $this->session->data['currency']);
			        $data['price1'] =  str_replace(iconv("Windows-1251", "UTF-8"," ���"),"",$data['price']) ;
			        $product['total']=  str_replace(' ',"",$data['price1'] ) ;
                                /*
                                $data['price'] = $this->currency->format($product['price'], $this->session->data['currency']);
			        $data['price1'] =  str_replace(iconv("Windows-1251", "UTF-8"," ���"),"",$data['price']) ;
			        $product['~price']=  str_replace(' ',"",$data['price1'] ) ;
                                */
                                
                                $this->load->model('catalog/product');
				$category_ids = $this->model_catalog_product->getCategories($product["product_id"]);
				$category_id = $category_ids[0]["category_id"];
				if($category_id){
					$this->load->model('catalog/category');
					$category_info = $this->model_catalog_category->getCategory($category_id);
				}
			$data['dataLayer']["transactionTotal"] = $data['dataLayer']["transactionTotal"] + $product['total'];
				
				$data['dataLayer']["transactionProducts"] .= '{'."'sku':'{$product['model']}',"."'name':'{$product['name']}',"."'category':'{$category_info['name']}',"."'price':'{$product['~price']}',"."'quantity':'{$product['quantity']}'".'},';
			}
			$data['fb_str']=$fb_str;
			$data['total_order']=$data['dataLayer']["transactionTotal"];
			   
                        
                        
                        
                        
                        
                        $this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
			
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
									/**/
				$last_order_id = isset($this->session->data['last_order_id']) ? $this->session->data['last_order_id'] : '';
				$data['text_order_id'] = '';
				if($last_order_id)
				$data['text_order_id'] = $this->language->get('column_order_id') .' : '.$last_order_id;
		/**/
		
		$data['last_order_id']=$this->session->data['last_order_id'];

		$this->response->setOutput($this->load->view('common/success', $data));
		
	}
}
