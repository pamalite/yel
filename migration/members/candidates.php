<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO members SELECT 
          email_addr, 
          userpasswd, 
          nic, 
          1, 
          clue,
          phone_num, 
          firstname, 
          lastname, 
          concat(contact_addr_line1, ' ', contact_addr_line2, ' ', contact_addr_line3) as address,
          contact_addr_state,
          contact_addr_zip,
          if ((char_length(contact_addr_cntry) > 2), 'MY', ucase(contact_addr_cntry)) as country,
          'N', 
          'Y', 
          'N', 
          10, 
          indate
          from yel_dev.prospect where email_addr not in (select email_addr from members)";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
