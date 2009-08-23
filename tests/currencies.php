<?php
require_once "../private/lib/utilities.php";

?><p style="font-weight: bold;">Update rates from ECB feed... </p><p><?php

if (Currency::update_rates()) {
    echo "Success";
} 

?></p><p style="font-weight: bold;">New rates to 1.00 EURO... </p><p><?php
echo "<pre>";
print_r(Currency::get_all());
echo "</pre>";

?></p>
