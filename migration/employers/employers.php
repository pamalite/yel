<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO employers SELECT 
          hirerid, 
          hirerpasswd, 
          license_num, 
          hirername, 
          phone_num, 
          email_addr,
          contact_person, 
          concat(contact_addr_line1, ' ', contact_addr_line2, ' ', contact_addr_line3) as address,
          contact_addr_state,
          contact_addr_zip,
          if ((char_length(contact_addr_cntry) > 2), 'MY', ucase(contact_addr_cntry)) as country,
          website_url,
          logopath,
          about,
          datejoined,
          working_months,
          bonus_months,
          6, 
          registerby, 
          NULL as registered_by
          from yel_dev.hirer";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
