<?php
class ControllerExtensionAnalyticsAdvtags extends Controller {
	public function index() {
			
		$data['advtags_status'] = $this->config->get('advtags_status');
		if(!$data['advtags_status'])
			return false;
        $data['advtags_gtag_status'] = $this->config->get('advtags_gtag_status');
        $data['advtags_gtag_tracker'] = $this->config->get('advtags_gtag_tracker');
        $data['advtags_gtag_conversion'] = $this->config->get('advtags_gtag_conversion');
        $data['advtags_gtag_events'] = $this->config->get('advtags_gtag_events');
        $gtag_events = isset($this->session->data['gtag_events']) ? $this->session->data['gtag_events']:array();
        foreach ($gtag_events as &$event) {
            if($event['type'] == 'conversion') {
                $event['params']['send_to'] = $data['advtags_gtag_conversion'];
            } else {
                $event['params']['send_to'] = $data['advtags_gtag_tracker'];
            }
            if(isset($event['params']['value'])) {
                $event['params']['value'] = round($event['params']['value'],2);
            }
        }
        $data['gtag_events'] = $gtag_events;
        if(isset($this->session->data['gtag_events']))
            unset($this->session->data['gtag_events']);
        return $this->load->view('extension/analytics/advtags', $data);
	} // end method
	public function fbq() {
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');

        $site_url = $this->config->get('config_url');
        $domain = parse_url($site_url, PHP_URL_HOST);

        $yml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $yml .= "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">";
        $yml .= "   <channel>\n";
        $yml .= "       <title>".$domain."</title>\n";
        $yml .= "       <link>".$site_url."</link>\n";

        $all_categories = $this->getAllCategories();
        foreach ($all_categories as $c) {
            $categories_mas[$c['category_id']] = $c['name'];
        }

        $products = $this->getProducts();
        foreach ($products as $product) {
            $product = $this->model_catalog_product->getProduct($product['product_id']);
            
            if($product['price'] == 0 || empty($product['price']) || empty($product['image'])) continue;
            if($product['status'] == 0) continue;
            if($product['quantity'] == 0) continue;

            $categories = $this->getCategories($product['product_id']);

            $category = false;
            if ($categories) {
                foreach ($categories as $c) {
                    $category = $c;
                    if (isset($c['main_category']) && $c['main_category'] == 1) break;
                    //break;
                }
            }
            if (!$category) continue;

            $manufacturer = !empty($product['manufacturer'])?$product['manufacturer']:'';
            $price = round($product['price']) . ' UAH';
            $special = '';
            if (!empty($product['special']))
                $special = round($product['special']) . ' UAH';

            $images = $this->model_catalog_product->getProductImages($product['product_id']);
            $images_mas = array();
            foreach ($images as $img) {
                $ii = sprintf('%simage/%s', $site_url, str_replace(' ', '%20', $img['image']));
                array_push($images_mas, $ii);
            }

            $goods_item = array(
                "g:id" => $product['product_id'],
                "g:title"  => $this->cdata($product['name']),
                "g:description" => $this->cdata($this->utf8_for_xml($product['description'])),
                "g:link" => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                "g:image_link" => sprintf('%simage/%s',$site_url,str_replace('&', '%26', $product['image'])),
                'g:availability' => 'in stock',
                "g:price" => $price,
                "g:product_type" => !empty($categories_mas[$category['category_id']]) ? $categories_mas[$category['category_id']] : '',
                "g:brand"  => empty($manufacturer) ? $domain : $this->cdata($manufacturer),
                "g:condition" => 'new'
            );
            if(!empty($images_mas)) {
                $goods_item['g:additional_image_link'] = implode($images_mas, ',');
            }
            if(!empty($special)) {
                $goods_item['g:sale_price'] = $special;
            }
            $yml_item = "";
            $yml_item .= "          <item>\n";
            foreach ( $goods_item as $cur_nodeName => $cur_nodeValue ) {
                $yml_item .= "              <".$cur_nodeName.">".$cur_nodeValue."</".$cur_nodeName.">\n";
            }
            $yml_item .= "          </item>\n";
            $yml .= $yml_item;
        } // end foreach

        $yml .= "   </channel>\n";
        $yml .= "</rss>\n";

        header("Content-Type: text/xml; charset=utf-8");
        header("Expires: 0");
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Cache-Control: post-check=0,pre-check=0");
        header("Cache-Control: max-age=0");
        header("Pragma: no-cache");
        echo $yml;
        exit;
	} // end method

    protected function getAllCategories() {
        $query = $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '1' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)");
        return $query->rows;
    } // end method

    protected function getProducts($data = array()) {
        $sql = "SELECT DISTINCT *";
        $sql .= " FROM " . DB_PREFIX . "product p";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        $query = $this->db->query($sql);
        return  $query->rows;
    } // end method

    protected function getCategories($product_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        return $query->rows;
    } // end method

    protected function utf8_for_xml($string) {
        return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
    } // end method

    private function cdata($string) {
        return sprintf('<![CDATA[%s]]>',htmlspecialchars_decode($string));
    }
} // end controller