<?php
require_once "../private/lib/utilities.php";

function print_array($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre><br><br>";
}

?><p style="font-weight: bold;">Get all employers... </p><p><?php
$employers = Employer::get_all();

echo "There are ". count($employers). " employers in the database.<br><br>";

?></p><p style="font-weight: bold;">Get all employers by limit... </p><p><?php
$employers = Employer::get_all_with_limit(3, 6);

echo "There are ". count($employers). " employers queried.<br><br>";

?></p><p style="font-weight: bold;">Find an employer... </p><p><?php
$criteria = array(
    'columns' => 'employers.name, countries.country, currencies.symbol, 
                  employers.contact_person, employers.email_addr',
    'match' => 'id = \'quad567\'',
    'joins' => 'countries ON countries.country_code = employers.country, 
                currencies ON currencies.country_code = countries.country_code'
);
$employers = Employer::find($criteria);

print_array($employers);
?></p><p style="font-weight: bold;">Add an employer... </p><p><?php
$employer_id = 'ken123';
$data = array();
$data['password'] = md5('new_password');
$data['license_num'] = 'my license';
$data['name'] = 'Fatt Choy Shopping Center';
$data['phone_num'] = '888 888 888 888';
$data['email_addr'] = 'fatt.choy@fatt.choy.com';
$data['contact_person'] = 'Kenny Lee';
$data['zip'] = '11399';
$data['country'] = 'MY';
$data['registerby'] = 'M';

$id = 0;
$employer = new Employer($employer_id);
if ($employer->create($data)) {
    print_array(Employer::find(array('match' => 'id = \''. $employer->id(). '\'')));
} else {
    echo "failed";
    exit();
}


?></p><p style="font-weight: bold;">Add a stack of employer fees for ken123... </p><p><?php
$fees = array();
$data = array();
$data['employer'] = $employer_id;
$data['service_fee'] = 1.50;
$data['premier_fee'] = 0.00;
$data['discount'] = 0.05;
$data['salary_start'] = 1.00;
$data['salary_end'] = 30000.00;
$fees[0] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['service_fee'] = 3.00;
$data['premier_fee'] = 0.00;
$data['discount'] = 0.04;
$data['salary_start'] = 30001.00;
$data['salary_end'] = 45000.00;
$fees[1] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['service_fee'] = 4.50;
$data['premier_fee'] = 1.00;
$data['discount'] = 0.03;
$data['salary_start'] = 45001.00;
$data['salary_end'] = 50000.00;
$fees[2] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['service_fee'] = 6.00;
$data['premier_fee'] = 1.00;
$data['discount'] = 0.02;
$data['salary_start'] = 50001.00;
$data['salary_end'] = 65000.00;
$fees[3] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['service_fee'] = 7.5;
$data['premier_fee'] = 1.00;
$data['discount'] = 0.01;
$data['salary_start'] = 65001.00;
$data['salary_end'] = 70000.00;
$fees[4] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['service_fee'] = 8;
$data['premier_fee'] = 1.00;
$data['discount'] = 0;
$data['salary_start'] = 70001.00;
$data['salary_end'] = 85000.00;
$fees[5] = $data;

if ($employer->create_fees($fees)) {
    print_array($employer->get_fees());
} else {
    echo "failed";
    exit();
}


?></p><p style="font-weight: bold;">Add a stack of employer extras for ken123... </p><p><?php
$extras = array();
$data = array();
$data['employer'] = $employer_id;
$data['charges'] = -10;
$extras[0] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['charges'] = 0.5;
$extras[1] = $data;
$data = array();
$data['employer'] = $employer_id;
$data['charges'] = -0.457;
$extras[3] = $data;

if ($employer->create_extras($extras)) {
    print_array($employer->get_extras());
} else {
    echo "failed";
    exit();
}


?></p><p style="font-weight: bold;">Update one of the fees for stanley.ch... </p><p><?php
$employer = new Employer('stanley.ch');
$fees = $employer->get_fees();
$id = $fees[5]['id'];
$data = array();
$data['id'] = $id;
$data['service_fee'] = 11.50;
$data['premier_fee'] = 0.5;

if ($employer->update_fee($data)) {
    print_array($employer->get_fees());
} else {
    echo "failed";
    exit();
}


?></p><p style="font-weight: bold;">Delete employer extras for ken123... </p><p><?php
$employer = new Employer('ken123');

if ($employer->delete_extras()) {
    print_array($employer->get_extras());
} else {
    echo "failed";
    exit();
}


?></p><p style="font-weight: bold;">Delete employer fees for ken123... </p><p><?php

if ($employer->delete_fees()) {
    print_array($employer->get_fees());
} else {
    echo "failed";
    exit();
}


?></p><?php
?>