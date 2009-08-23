<?php
require_once "../private/lib/utilities.php";

function print_array($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre><br><br>";
}

?><p style="font-weight: bold;">Get all members... </p><p><?php
$members = Member::get_all();

echo "There are ". count($members). " members in the database.<br><br>";

?></p><p style="font-weight: bold;">Get all members by limit... </p><p><?php
$members = Member::get_all_with_limit(3, 6);

echo "There are ". count($members). " members queried.<br><br>";

?></p><p style="font-weight: bold;">Find a member... </p><p><?php
$criteria = array(
    'columns' => 'members.email_addr, 
                  CONCAT(members.firstname, \' \', members.lastname) AS name, 
                  countries.country, currencies.symbol',
    'match' => 'email_addr = \'pamalite@gmail.com\'',
    'joins' => 'countries ON countries.country_code = members.country, 
                currencies ON currencies.country_code = countries.country_code'
);
$members = Member::find($criteria);

print_array($members);
?></p><p style="font-weight: bold;">Create a new member... </p><p><?php
$member = new Member('bibi@bibi.com');
$data = array();
$data['password'] = md5('bibi');
$data['forget_password_question'] = 1;
$data['forget_password_answer'] = 'bibi answer';
$data['phone_num'] = '222 2234 4566';
$data['firstname'] = 'Bibi';
$data['lastname'] = 'Choon';
$data['zip'] = '0A33Z8';
$data['country'] = 'CA';

if ($member->create($data)) {
    $criteria = array(
        'columns' => 'members.email_addr, 
                      CONCAT(members.firstname, \' \', members.lastname) AS name, 
                      countries.country, currencies.symbol',
        'match' => 'email_addr = \'bibi@bibi.com\'',
        'joins' => 'countries ON countries.country_code = members.country, 
                    currencies ON currencies.country_code = countries.country_code'
    );
    
    print_array(Member::find($criteria));
} else {
    echo "exit";
    exit();
}
?></p><?php
?>