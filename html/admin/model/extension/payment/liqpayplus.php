<?php
// All rights reserved ART&PR studio -> https://store.pe-art.ru
class ModelExtensionPaymentLiqpayplus extends Model {
    private $pname           = 'liqpayplus';
    private $proname         = 'liqpaypro';
    private $extclass        = 'payment';
    private $ext_name        = 'extension_'; // ''
    private $ext_folder      = 'extension/'; // ''
    private $pnameplus       = ''; // 'payment_'
    private $token_name      = 'token'; // user_token
    private $clone_name      = 'lpclone';
	private $key;

    public function getTotalStatus() {

        $sql = "SELECT COUNT(status_id) AS total FROM " . DB_PREFIX . $this->proname . " WHERE `status` = 1 OR `status` = 2 OR `status` = 3";

        $query = $this->db->query($sql);

        return $query->row['total'];

    }

    public function getStatus($data) {

        $sql = "SELECT * FROM `" . DB_PREFIX . $this->proname . "` WHERE `status` = 1 OR `status` = 2 OR `status` = 3 ORDER BY `status_id` DESC";
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }


    public function getSettings() {
        $setpro = array('license', 'login', 'password', 'name_attach', 'debug', 'returnpage', 'otlog', 'showadmin', 'artprbalance', 'balance_sort', 'artprfpay', 'fpay_sort', 'fixen', 'fixen_amount', 'minpay', 'maxpay', 'instruction_attach', 'success_alert_admin', 'success_alert_customer', 'mail_instruction_attach', 'success_comment_attach', 'success_page_text_attach', 'hrefpage_text_attach', 'waiting_page_text_attach', 'button_later', 'createorder_or_notcreate', 'fail_page_text_attach', 'start_status_id', 'on_status_id', 'order_status_id', 'geo_zone_id', 'status', 'sort_order', 'twostage', 'currency_shop', 'currency_merch');
        return $setpro;
    }

    public function getSettingsExtended() {
        $setpro = array('shippings' => 'all', 'store' => 0, 'currency_pay' => 'all', 'gruppa' => 'all');
        return $setpro;
    }

    public function getLangSettings() {
        $setpro = array('name', 'instruction', 'mail_instruction', 'success_comment', 'success_page_text', 'hrefpage_text', 'waiting_page_text', 'fail_page_text', 'balance_name', 'fpay_name', 'formcomment');
        return $setpro;
    }

    public function getErrSettings() {
        $setpro = array('warning', 'login', 'password', 'dgruppa', 'dshippings', 'license', 'fixen', 'currency_pay');
        return $setpro;
    }

    public function getPoles() {

        $pt = array();
        $pt['currency_merch'] = array('UAH', 'USD', 'EUR', 'RUB', 'BYN', 'KZT');
        return $pt;
    }

    public function getPaymentType($paymentType) {
        if ($paymentType == 'liqpayplus'){
            $pt = 'liqpay';
        }

        if (strpos($paymentType, 'plus'.$this->clone_name)){
            $pt = 'liqpay';
        }

        if (strpos($paymentType, '_card')){
            $pt = 'card';
        }

        if (strpos($paymentType, '_allmet')){
            $pt = '';
        }

        if (strpos($paymentType, '_privat')){
            $pt = 'privat24';
        }

        if (strpos($paymentType, '_qrcode')){
            $pt = 'qr';
        }

        if (strpos($paymentType, '_cash')){
            $pt = 'cash';
        }

        if (strpos($paymentType, '_invoice')){
            $pt = 'invoice';
        }

        if (strpos($paymentType, '_moment')){
            $pt = 'moment_part';
        }

        if (strpos($paymentType, '_masterpass')){
            $pt = 'masterpass';
        }


        return $pt;
    }

    public function getInstalled($type) {
        $extension_data = array();

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "' ORDER BY `code`");

        foreach ($query->rows as $result) {
            $extension_data[] = $result['code'];
        }

        return $extension_data;
    }

    public function getTwostage($paymentType) {

        //if (strpos($paymentType, '_card')) {
        //    $pt = true;
        //} else {
        //    $pt = false;
        //}
        $pt = true;
        return $pt;
    }

    public function getPaymentIdByNum($num) {
        $query = $this->db->query("SELECT `label`, `label6` FROM " . DB_PREFIX . $this->proname . " WHERE status_id = '" . (int)$num . "' ");

        return $query->row;
    }

    public function getPaymentStatus($order_id) {

        $query = $this->db->query("SELECT `status` FROM " . DB_PREFIX . $this->proname . " WHERE status_id = '" . (int) $order_id . "' ");
        return $query->row['status'];

    }

    public function changeStatus($order_id, $status) {

        $this->db->query("UPDATE " . DB_PREFIX . $this->proname . " SET `status` = '" . (int) $status . "' where status_id = '" . (int) $order_id . " '");

    }

    public function getCustomerGroups($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sort_data = array(
            'cgd.name',
            'cg.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY cgd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

	public function getCustomFields($order_info, $varabliesd) {
            $instros = explode('~', $varabliesd);
            $instroz = "";

            foreach ($instros as $instro) {
                if ($instro == 'href'){
                    if ($instro == 'href'){
                       $instro_other = $order_info['store_url'] . 'index.php?route='.$this->ext_folder.$this->extclass.'/'.$this->pname.'/go&code=' . $this->getSecureCode($order_info['order_id']) . '&order_id=' . $order_info['order_id'];   
                    }
                }
                else {
                    $instro_other = nl2br(htmlspecialchars_decode($instro));
                }
                    $instroz .=  $instro_other;
            }
            return $instroz;
    }

    public function getSecureCode($order_id) {
        $code = substr(md5($order_id . $this->config->get('config_encryption')), 0, 12);
        return $code;
    }
}