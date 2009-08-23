<?php
require_once "../private/lib/utilities.php";

function print_array($_array) {
    echo "<pre>";
    print_r($_array);
    echo "</pre><br><br>";
}

$referral = 4;

?><p style="font-weight: bold;">Show all invoices... </p><p><?php

print_array(Invoice::get_all());

?></p><p style="font-weight: bold;">Add an invoice... </p><p><?php
$data = array();
$data['issued_on'] = today();
$data['type'] = 'R';
$data['employer'] = 'acme123';
$data['payable_by'] = date_add(today(), 30, 'day');

$id = 0;
if ($id = Invoice::create($data)) {
    echo "This invoice has an ID of <b>". $id. "</b><br><br>";
    print_array(Invoice::get($id));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add an item... </p><p><?php
if (Invoice::add_item($id, 270.98, 23, 'some references')) {
    print_array(Invoice::get_items_of($id));
} else {
    echo "failed";
    exit();
}

?></p><p style="font-weight: bold;">Add another item... </p><p><?php
if (Invoice::add_item($id, 0.05, 93, 'some other charges')) {
    print_array(Invoice::get_items_of($id));
} else {
    echo "failed";
    exit();
} 
?></p><p style="font-weight: bold;">Pay an invoice... </p><p><?php
$data = array();
$data['id'] = $id;
$data['paid_on'] = date_add(today(), 30, 'day');
$data['paid_through'] = 'CHQ';
$data['paid_id'] = 'a_cheque_number';

if (Invoice::update($data)) {
    print_array(Invoice::get($id));
} else {
    echo "failed";
    exit();
}

?></p>
