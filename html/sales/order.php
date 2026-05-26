<?php
$user    = 'Rwinner54';
$api_key = 'E98676C8C7620086A2388679A4CEDFCD';

/*
17 июля 13 00
-----------------
28600
*/

/*
INITIALIZED
IN_PROGRESS
IN_PROGRESS
DELIVERED
CANCELLED
ABANDONED_SHOPPING_CART
*/

$sd_statuses = [
    '1'  => 'INITIALIZED', // Pending
    '2'  => 'IN_PROGRESS', // Confirmed
    '3'  => 'IN_PROGRESS', // Invoice
    '28' => 'IN_PROGRESS', // Invoice ready
    '4'  => 'IN_PROGRESS', // Awaiting prepayment
    '9'  => 'IN_PROGRESS', // Awaiting stock
    '10' => 'IN_PROGRESS', // Long-term assembly
    '27' => 'IN_PROGRESS', // Assembly from warehouse
    '11' => 'IN_PROGRESS', // Assembly with pre-order
    '12' => 'IN_PROGRESS', // Assembly
    '13' => 'IN_PROGRESS', // Processing
    '14' => 'IN_PROGRESS', // Delivery
    '15' => 'IN_PROGRESS', // Complaint
    '16' => 'IN_PROGRESS', // Awaiting post-payment
    '5'  => 'DELIVERED',   // Completed
    '6'  => 'CANCELLED',   // Refused
    '7'  => 'CANCELLED',   // Did not pick up - blacklist
    '8'  => 'CANCELLED',   // Deleted
];


$query = file_get_contents('php://input');

if (!empty($query)) {

    $data = json_decode($query, true);

    $order_id  = $data['data']['id'];
    $status_id = (string)$data['data']['statusId'];
    $status    = isset($sd_statuses[$status_id]) ? $sd_statuses[$status_id] : 'IN_PROGRESS';

    if ($data['data']['statusId'] == 7 || $data['data']['statusId'] == 8) {
        echo '===================' . "\r\n";
        echo 'Order is junk' . "\r\n";
        echo '===================' . "\r\n";
        die();
    }

    if ($data['data']['sajt'] == 83) {
        echo '===================' . "\r\n";
        echo 'Order radioshop.com.ua' . "\r\n";
        echo '===================' . "\r\n";
        die();
    }

    $contact = isset($data['data']['contacts'][0]) ? $data['data']['contacts'][0] : [];

    $items = [];
    $order = new stdClass();

    $order->status          = $status;
    $order->externalOrderId = 'sd-' . $order_id;
    $order->phone           = !empty($contact['phone']) ? $contact['phone'][0] : '';
    $order->email           = !empty($contact['email']) ? $contact['email'][0] : '';
    $order->firstName       = isset($contact['fName']) ? $contact['fName'] : '';
    $order->lastName        = isset($contact['lName']) ? $contact['lName'] : '';
    $order->currency        = 'UAH';

    $order->deliveryMethod  = isset($data['meta']['fields']['shipping_method']['options'][0]['text'])
                                ? $data['meta']['fields']['shipping_method']['options'][0]['text'] : '';
    $order->paymentMethod   = isset($data['meta']['fields']['payment_method']['options'][0]['text'])
                                ? $data['meta']['fields']['payment_method']['options'][0]['text'] : '';
    $order->deliveryAddress = isset($data['data']['adresaDostavki']) ? $data['data']['adresaDostavki'] : '';
    $order->date            = date('c', strtotime($data['data']['orderTime']));

    if (!empty($data['data']['products'])) {
        $totalCost = 0;
        foreach ($data['data']['products'] as $product) {
            $item = new stdClass();

            $item->externalItemId = $product['parameter'];
            $item->name           = $product['name'];
            $item->category       = $product['categoryName'];
            $item->quantity       = $product['amount'];
            $item->cost           = $product['price'];
            $item->url            = $product['href'];
            $item->description    = $product['description'];

            $items[] = $item;

            $totalCost = $totalCost + $product['price'] * $product['amount'];
        }

        $order->items     = $items;
        $order->totalCost = $totalCost;
    }

    $orders = new stdClass();
    $orders->orders = [$order];

    print_r($orders);

    echo '===================' . "\r\n";
    print_r($orders);
    echo '===================' . "\r\n";

    $data_string = json_encode($orders);

    $requestURL = 'https://esputnik.com/api/v1/orders';
    $curl = curl_init($requestURL);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type:application/json',
        'Accept:application/json',
        'Authorization: Basic ' . base64_encode("$user:$api_key"),
    ]);
    $response = curl_exec($curl);

    $err       = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    echo '===================' . "\r\n";
    echo $http_code;
    echo '===================' . "\r\n";

    if ($err) {
        echo '===================' . "\r\n";
        echo "ERROR: with cURL Error #:" . htmlentities($err) . "\r\n";
        echo '===================' . "\r\n";
    }

    echo '===================' . "\r\n";
    print_r($response);
    echo '===================' . "\r\n";

    curl_close($curl);
}
