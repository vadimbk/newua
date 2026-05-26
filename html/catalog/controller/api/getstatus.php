<?php
class ControllerApiGetStatus extends Controller {
    public function index() {
        // Load language files for error messages
        $this->load->language('api/login');
        $this->load->language('api/order');

        $json = array();

        // Clear any previous API session data to prevent conflicts
        unset($this->session->data['api_id']);

        // Step 1: Validate the API token
        if (isset($this->request->get['token'])) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_session` WHERE session_id = '" . $this->db->escape($this->request->get['token']) . "'");

            if ($query->num_rows) {
                // If the session is found, start it and set the api_id
                $session = new Session($this->config->get('session_engine'), $this->registry);
                $session->start($this->request->get['token']);
                $this->session->data['api_id'] = $query->row['api_id'];
            } else {
                 $json['error'] = $this->language->get('error_permission');
            }
        } else {
            $json['error'] = $this->language->get('error_permission');
        }
        
        // Proceed only if there are no authentication errors
        if (!isset($json['error'])) {
            // Step 2: Correctly read the JSON body of the request
            $request_body = file_get_contents('php://input');
            $request_data = json_decode($request_body, true);

            $order_id = 0;
            if (json_last_error() === JSON_ERROR_NONE && isset($request_data['order_id'])) {
                $order_id = (int)$request_data['order_id'];
            } else {
                $json['error'] = 'Error: Invalid JSON or order_id missing in request body.';
            }

            // Step 3: Get order information if order_id is valid
            if ($order_id > 0) {
                $this->load->model('checkout/order');
                $order_info = $this->model_checkout_order->getOrder($order_id);

                if ($order_info) {
                    // Step 4: If order is found, return its data
                    $json['success'] = true;
                    $json['order'] = $order_info;
                } else {
                    // Return a "not found" error if getOrder returns false
                    $json['error'] = $this->language->get('error_not_found');
                }
            } elseif (!isset($json['error'])) {
                 // This case handles when JSON is valid but order_id is 0 or not numeric
                 $json['error'] = 'Error: order_id must be a positive integer.';
            }
        }

        // Set the response header and output the JSON
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
