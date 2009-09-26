<?php
require_once dirname(__FILE__). "/private/lib/utilities.php";

function get_text_from_rtf($_file) {
    $content = file_get_contents($_file);
    $text = '';
    $is_tag = false;
    for ($i=0; $i < strlen($content); $i++) {
        $char = substr($content, $i, 1);
        if ($char == "\\" && !$is_tag) {
            $is_tag = true;
            continue;
        } elseif (($char == ' ' || $char == "\n") && $is_tag) {
            $is_tag = false;
            continue;
        }

        if (!$is_tag) {
            if ($char != '{' && $char != '}') {
                $text .= $char;
            }
        }
    }

    return $text;
}

function get_text_from_msword($userDoc) {
    $fileHandle = fopen($userDoc, "r");
    $line = @fread($fileHandle, filesize($userDoc));   
    $lines = explode(chr(0x0D),$line);
    $outtext = "";
    foreach($lines as $thisline)
      {
        $pos = strpos($thisline, chr(0x00));
        if (($pos !== FALSE)||(strlen($thisline)==0))
          {
          } else {
            $outtext .= $thisline." ";
          }
      }
    $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
    return $outtext;
}

$query = "SELECT concat(id, '.', file_hash) AS resume_file, file_type, id, member 
          FROM resumes 
          WHERE file_hash IS NOT NULL AND file_hash <> ''";
$mysqli = Database::connect();
$result = $mysqli->query($query);

foreach ($result as $row) {
    $resume_file = $GLOBALS['resume_dir']. '/'. $row['resume_file'];
    $type = $row['file_type'];
    
    if (file_exists($resume_file)) {
        $resume_text = '';
        switch ($type) {
            case 'text/plain':
                $tmp = file_get_contents($resume_file);
                $resume_text = sanitize($tmp);
                break;
            case 'text/html':
                $tmp = file_get_contents($resume_file);
                $resume_text = sanitize(strip_tags($tmp));
                break;
            case 'application/pdf':
                $cmd = "/usr/local/bin/pdftotext ". $resume_file. " /tmp/". $row['resume_file'];
                shell_exec($cmd);
                $tmp = file_get_contents('/tmp/'. $row['resume_file']);
                $resume_text = sanitize($tmp);
                
                if (!empty($tmp)) {
                    unlink('/tmp/'. $row['resume_file']);
                }
                break;
            case 'application/msword':
                $tmp = get_text_from_msword($resume_file);
                $resume_text = sanitize($tmp);
                break;
            case 'application/rtf':
                $tmp = get_text_from_rtf($resume_file);
                $resume_text = sanitize($tmp);
                break;
        }
        
        if (!empty($resume_text)) {
            $keywords = preg_split("/[\s,]+/", $resume_text);
            $resume_text = '';
            foreach ($keywords as $i=>$keyword) {
                $resume_text .= $keyword;
                
                if ($i < count($keywords)-1) {
                    $resume_text .= ' ';
                }
            }
            
            $query = "SELECT COUNT(*) AS is_exists FROM resume_index 
                      WHERE resume = ". $row['id']. " AND member = '". $row['member']. "'";
            $result = $mysqli->query($query);
            if ($result[0]['is_exists'] == '0') {
                $query = "INSERT INTO resume_index SET 
                          resume = ". $row['id']. ", 
                          member = '". $row['member']. "', 
                          file_text = '". $resume_text. "'";
            } else {
                $query = "UPDATE resume_index SET file_text = '". $resume_text. "' 
                          WHERE resume = ". $row['id']. " AND 
                          member = '". $row['member']. "'";
            }
            
            echo '<pre>';
            print_r($query);
            echo '</pre>';
            if ($mysqli->execute($query)) {
                echo 'ok<br/>';
            } else {
                echo 'ko<br/>';
            }
        }
    }
}
?>
