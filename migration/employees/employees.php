<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO employees SELECT 
          employee.id, 
          employee.joined_date, 
          users.password, 
          employee.given_name, 
          employee.family_name, 
          concat(employee.addr_line1, ' ', employee.addr_line2, ' ', employee.city) as address, 
          employee.state, 
          employee.poscode, 
          'MY', 
          employee.phone, 
          employee.mobile, 
          employee.email, 
          employee.alternate_email, 
          employee.designation, 
          employee.business_entityid, 
          users.created_by, 
          users.created_date
          from yel_business.employee
          left join yel_business.users on users.employeeid = employee.id";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    echo "ok";
} else {
    echo "ko";
}
?>
