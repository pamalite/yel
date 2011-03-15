<?php
require_once "../private/lib/utilities.php";

?><p style="font-weight: bold;">Get all</p><p><?php

echo '<pre>';
print_r(Branch::getAll());
echo '</pre>';

?><p style="font-weight: bold;">Get ID = 1</p><p><?php

echo '<pre>';
print_r(Branch::get('1'));
echo '</pre>';

?><p style="font-weight: bold;">Get currency</p><p><?php

echo '<pre>';
print_r(Branch::getCurrency('1'));
echo '</pre>';

?><p style="font-weight: bold;">Update</p><p><?php

$data = array();
$data['id'] = '2';
$data['address'] = "Suite 3, 22 Council St,\nHawthorn East";
$data['state'] = "Victoria";
$data['zip'] = "3123";
$data['country'] = 'AU';
$data['phone'] = '+61 03 9882 7164';
$data['fax'] = '+61 03 9882 9792';

?>Before...<?php
echo '<pre>';
print_r(Branch::get('2'));
echo '</pre>';

Branch::update($data);

?>After...<?php
echo '<pre>';
print_r(Branch::get('2'));
echo '</pre>';

?><p style="font-weight: bold;">Find</p><p><?php

$criteria = array(
    'columns' => 'branches.branch, branches.address, branches.zip, branches.state, countries.country',
    'match' => 'branches.phone LIKE \'%6363\'', 
    'joins' => 'countries ON countries.country_code = branches.country'
);

echo '<pre>';
print_r(Branch::find($criteria));
echo '</pre>';
?></p><?php
?>