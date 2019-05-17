<?php

include('cms/public/api.php');
$brands = explode("\n", $api->db->select("`fields`", "WHERE id='32' LIMIT 1", 'p3'));

$products = $api->objects->getFullObjectsListByClass(-1, 12);
$counter = 1;
foreach ($products as $product) {

    print_r($product['Брэнд']);
    die;
}