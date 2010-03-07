<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$mysqli = Database::connect();

function drop_columns_from_table($_columns, $_table) {
    $mysqli = $GLOBALS['mysqli'];
    
    $query = "SHOW CREATE TABLE `". $_table. "`";
    $result = $mysqli->query($query);
    
    if (is_null($result) || empty($result)) {
        return false;
    }
    
    $create_table = $result[0]['Create Table'];
    $lines = explode("\n", $create_table);
    $key_names = array();
    foreach ($lines as $line) {
        $line = trim($line);
        if (substr($line, 0, 10) == 'CONSTRAINT') {
            $words = explode(' ', $line);
            foreach ($words as $i=>$word) {
                $word = str_replace('`', '', $word);
                $word = str_replace('(', '', $word);
                $words[$i] = str_replace(')', '', $word);
            }
            
            $key_name = $words[1];
            foreach ($_columns as $column) {
                if ($words[4] == $column) {
                    $key_names[$column] = $key_name;
                }
            }
        }
    }
    
    return $key_names;
}

// migration starts here

// 1. Move all the primary, secondary and tertiary industries to a new M:N table

$query = "SELECT email_addr, primary_industry AS '0', 
          secondary_industry AS '1', tertiary_industry AS '2' 
          FROM members";

$result = $mysqli->query($query);
if (empty($result) || is_null($result)) {
    echo 'Error: No members found.<br/><br/>';
    exit();
}

$members = $result;
foreach ($members as $member) {
    $id = $member['email_addr'];
    $industries = array();
    $counter = 0;
    for ($i=0; $i < 3; $i++) {
        if (!is_null($member[$i]) && !empty($member[$i])) {
            $industries[$counter] = $member[$i];
            $counter++;
        }
    }
    
    if (!is_null($industries) && !empty($industries)) {
        $tmp = array();
        foreach ($industries as $i=>$industry) {
            $is_duplicate = false;
            $current_value = $industry; 
            foreach ($tmp as $j=>$value) {
                if ($value == $current_value) {
                    $is_duplicate = true;
                    break;
                }
            }
            
            if (!$is_duplicate) {
                $tmp[] = $current_value;
            }
        }
        
        $industries = $tmp;
        foreach ($industries as $industry) {
            $query = "INSERT INTO member_industries SET 
                      member = '". $id. "', 
                      industry = ". $industry;
            echo $query. '<br/>';
            if ($mysqli->execute($query) === false) {
                echo 'failed<br/><br/>';
            } else {
                echo 'success<br/>';
            }
        }
    }
}

$columns = array('primary_industry', 'secondary_industry', 'tertiary_industry');
$keys = drop_columns_from_table($columns, 'members');
if ($keys === false) {
    echo 'Remember to drop the columns manually.<br/><pre>';
    $query = "ALTER TABLE members 
              DROP FOREIGN KEY ???, 
              DROP FOREIGN KEY ???,
              DROP FOREIGN KEY ???,
              DROP COLUMN primary_industry,
              DROP COLUMN secondary_industry,
              DROP COLUMN tertiary_industry";
    echo $query. '</pre><br/><br/>';
} else {
    $query = "ALTER TABLE members ";
    $i = 0;
    foreach ($keys as $col=>$key) {
        $query .= "DROP FOREIGN KEY `". $key. "`, 
                   DROP COLUMN `". $col. "`";
        if ($i < count($keys) - 1) {
            $query .= ", ";
        }
        
        $i++;
    }
    
    if ($mysqli->execute($query) === false) {
        echo 'Failed to drop columns. Drop it manually<br/><pre>'. $query. '</pre><br/><br/>';
    } else {
        echo 'successfully dropped columns.<br/>';
    }
}

// 2. Move recommenders to members
// 2.1 move those who are not in members
$query = "SELECT * FROM recommenders 
          WHERE email_addr NOT IN (SELECT email_addr FROM members)";
$result = $mysqli->query($query);

if (!is_null($result) && !empty($result)) {
    $recommenders = $result;
    $new_members = array();
    foreach ($recommenders as $recommender) {
        $query = "INSERT INTO members SET 
                  email_addr = '". $recommender['email_addr']. "', 
                  phone_num = '". $recommender['phone_num']. "', 
                  firstname = '". $recommender['firstname']. "', 
                  lastname = '". $recommender['lastname']. "', 
                  remarks = '". $recommender['remarks']. "', 
                  state = '". $recommender['region']. "', 
                  password = '(SysGen)', 
                  forget_password_question = 1, 
                  forget_password_answer = '(System Generated)', 
                  active = 'N', 
                  added_by = ". $recommender['added_by']. ", 
                  joined_on = '". $recommender['added_on']. "'";
        echo $query. '<br/>';
        if ($mysqli->execute($query) === false) {
            echo 'Cannot move recommender to member: '. $recommender['email_addr']. '<br/><br/>';
        } else {
            echo 'success<br/>';
            $new_members[] = $recommender['email_addr'];
        }
    }
    
    // 2.2 move their industries to member_industries
    foreach ($new_members as $new_member) {
        $query = "SELECT industry FROM recommender_industries 
                  WHERE recommender = '". $new_member. "'";
        $result = $mysqli->query($query);
        if (is_null($result) || empty($result)) {
            echo 'No industries set for recommender: '. $new_member. '<br/><br/>';
        } else {
            $industries = $result;
            foreach ($industries as $industry) {
                $query = "INSERT INTO member_industries SET 
                          member = '". $new_member. "', 
                          industry = ". $industry['industry'];
                echo $query. '<br/>';
                if ($mysqli->execute($query) === false) {
                    echo 'failed<br/><br/>';
                } else {
                    echo 'success<br/>';
                }
            }
        }
    }
} else {
    echo 'All recommenders are members.<br/><br/>';
}

// 2.3 copy the remarks for recommenders who are already members
$query = "SELECT email_addr, remarks FROM recommenders 
          WHERE email_addr IN (SELECT email_addr FROM members)";
$result = $mysqli->query($query);

if (!is_null($result) && !empty($result)) {
    $recommenders = $result;
    foreach ($recommenders as $recommender) {
        $query = "UPDATE members SET remarks = '". $recommender['remarks']. "' 
                  WHERE email_addr = '". $recommender['email_addr']. "'";
        echo $query. '<br/>';
        if ($mysqli->execute($query) === false) {
            echo 'failed<br/><br/>';
        } else {
            echo 'success<br/>';
        }
    }
} else {
    echo 'All recommenders are not members.<br/><br/>';
}

// 2.4 make references between recommenders and members in member_referees
$query = "INSERT INTO member_referees 
          SELECT recommender, email_addr FROM members 
          WHERE recommender IS NOT NULL;
          DELETE FROM member_referees 
          WHERE member = referee;
          UPDATE members SET recommender = NULL";
echo $query. '<br/>';
if ($mysqli->transact($query) === false) {
    echo 'failed<br/><br/>';
} else {
    echo 'success<br/>';
}

// 2.5 drop the recommender tables as we no longer need it, manually
echo '<br/>Remember to drop the recommender tables, manually.<br/><pre>';
$query = "ALTER TABLE members 
          DROP FOREIGN KEY ??members_ibfk_4??, 
          DROP COLUMN recommender;
          DROP TABLE recommender_industries;
          DROP TABLE recommender_tokens; 
          DROP TABLE recommenders";
echo $query. '</pre><br/>';

echo 'Finish';
?>
