<?php

class ControllerExtensionModuleEeEsputnik extends Controller {

    private function getCategoryFullPath($category_id) {
        $query = $this->db->query("SELECT GROUP_CONCAT(cp.path_id SEPARATOR '_') AS path FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = c.category_id) WHERE c.category_id = '".(int)$category_id."' AND c2s.store_id = '".$this->config->get('config_store_id')."' AND c.status = '1' ORDER BY cp.level ASC;");

        return isset($query->row['path']) ? $query->row['path'] : false;
    }


    public function statusCart() {

        $products = $this->cart->getProducts();

        $data['es_event'] = '';

        if($products) {

            //$data['es_event'] .= "<script>" . "\n";
                    
            $data['es_event'] .= "eS('sendEvent', 'StatusCart', {";

            $data['es_event'] .= "'StatusCart': [";

            $i = 1;
            $count_products = count($products);

            foreach($products as $product) {

                $product['price'] = $this->currency->format($product['price'], $this->config->get('remarketing_ecommerce_currency'), '', false);

                $data['es_event'] .= "{'productKey': '" . $product['product_id'] . "',";
                $data['es_event'] .= "'price': '" . number_format($product['price'], 2, '.', '') . "',";                               
                $data['es_event'] .= "'quantity': " . (int)$product['quantity'] . ",";
                $data['es_event'] .= "'currency': 'UAH'}";
                if($i != $count_products) $data['es_event'] .= ",";

                $i++;

            }

            $data['es_event'] .= "],". "\n";

            $data['es_event'] .= "'GUID': '" . $this->getNewClientId() . "'";

            $data['es_event'] .= "});". "\n";

            $data['es_event'] .= "console.log('eSputnik: statusCart send');". "\n";

            //$data['es_event'] .= "</script>" . "\n";

        // Если пустая корзина
        } else {
            $data['es_event'] .= "eS('sendEvent', 'StatusCart', {";

            $data['es_event'] .= "'StatusCart': [],". "\n";

            $data['es_event'] .= "'GUID': '" . $this->getNewClientId() . "'";

            $data['es_event'] .= "});". "\n";

            $data['es_event'] .= "console.log('eSputnik: statusCart empty send');". "\n";
        }

        return $this->response->setOutput($data['es_event']);

    }

    public function getClientId() {

        $client_id = '';
        
        if (isset($_COOKIE['_escid'])) {
            $client_id = $_COOKIE['_escid'];
        } else {
            $client_id = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                mt_rand( 0, 0xffff ),
                mt_rand( 0, 0x0fff ) | 0x4000,
                mt_rand( 0, 0x3fff ) | 0x8000,
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
            setcookie('_escid', $client_id, time() + 31536000, '/');
        }

        $this->session->data['remarketing_esputnik_cart_id'] = $client_id;

        return $client_id;
    }

    public function getNewClientId() {
        $client_id = '';

        $client_id = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
        setcookie('_escid', $client_id, time() + 31536000, '/');

        $this->session->data['remarketing_esputnik_cart_id'] = $client_id;

        return $client_id;
    }

}