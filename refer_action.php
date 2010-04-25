<?php
require_once dirname(__FILE__). '/private/lib/utilities.php';

session_start();

// 1. initialize the parameters
$referrer = array();
$referrer['email_addr'] = sanitize($_POST['referrer_email']);
$referrer['phone_num'] = sanitize($_POST['referrer_phone']);
$referrer['name'] = sanitize(stripslashes($_POST['referrer_name']));

$candidate = array();
$candidate['email_addr'] = sanitize($_POST['candidate_email']);
$candidate['phone_num'] = sanitize($_POST['candidate_phone']);
$candidate['name'] = sanitize(stripslashes($_POST['candidate_name']));

$job_id = $_POST['job_id'];
$job = new Job($job_id);

$today = now();
// 2. store the contacts
$data = array();
$data['requested_on'] = $today; 
$data['referrer_email'] = $referrer['email_addr'];
$data['referrer_phone'] = $referrer['phone_num'];
$data['referrer_name'] = $referrer['name'];
$data['candidate_email'] = $candidate['email_addr'];
$data['candidate_phone'] = $candidate['phone_num'];
$data['candidate_name'] = $candidate['name'];
$data['job'] = $job->getId();

$referral_buffer = new ReferralBuffer();
$buffer_id = $referral_buffer->create($data);
if ($buffer_id === false) {
    ?>
        <script type="text/javascript">top.stop_refer(<?php echo '0'; ?>);</script>
    <?php
    exit();
}

// 2. check any files to upload
$has_resume = 'NO';
$file_path = '';
$resume_text = '';
if (!empty($_FILES['candidate_resume']['name'])) {
    $type = $_FILES['candidate_resume']['type'];
    $size = $_FILES['candidate_resume']['size'];
    $name = $_FILES['candidate_resume']['name'];
    $temp = $_FILES['candidate_resume']['tmp_name'];
    
    if ($size <= $GLOBALS['resume_size_limit'] && $size > 0) {
        foreach ($GLOBALS['allowable_resume_types'] as $mime_type) {
            if ($type == $mime_type) {
                $hash = generate_random_string_of(6);
                $new_name = $buffer_id. ".". $hash;
                $file_path = $GLOBALS['buffered_resume_dir']. "/". $new_name;
                
                if (move_uploaded_file($temp, $file_path)) {
                    $data = array();
                    $data['resume_file_name'] = $name;
                    $data['resume_file_type'] = $type;
                    $data['resume_file_hash'] = $hash;
                    $data['resume_file_size'] = $size;
                    
                    if ($referral_buffer->update($data)) {
                        switch ($type) {
                            case 'text/plain':
                                $tmp = file_get_contents($file_path);
                                $resume_text = sanitize($tmp);
                                break;
                            case 'text/html':
                                $tmp = file_get_contents($file_path);
                                $resume_text = sanitize(strip_tags($tmp));
                                break;
                            case 'application/pdf':
                                $cmd = "/usr/local/bin/pdftotext ". $file_path. " /tmp/". $new_name;
                                shell_exec($cmd);
                                $tmp = file_get_contents('/tmp/'. $new_name);
                                $resume_text = sanitize($tmp);
                                
                                if (!empty($tmp)) {
                                    unlink('/tmp/'. $new_name);
                                }
                                break;
                            case 'application/msword':
                                $tmp = Resume::getTextFromMsword($file_path);
                                if (empty($tmp)) {
                                    $tmp = Resume::getTextFromRTF($file_path);
                                }
                                $resume_text = sanitize($tmp);
                                break;
                            case 'application/rtf':
                                $tmp = Resume::getTextFromRTF($file_path);
                                if (empty($tmp)) {
                                    $tmp = Resume::getTextFromMsword($file_path);
                                }
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
                            
                            $data['resume_file_text'] = sanitize(stripslashes($resume_text));
                            if ($referral_buffer->update($data) === true) {
                                $has_resume = 'YES';
                            } else {
                                @unlink($file_path);
                            }
                            break;
                        }
                    }
                }
            }
        }
    } else {
        ?>
            <script type="text/javascript">top.stop_refer(<?php echo '0'; ?>);</script>
        <?php
        exit();
    }
}

// 4 Send email to team.xx@yellowelevator.com
$country_code = strtolower($_SESSION['yel']['country_code']);
if (isset($_SESSION['yel']['member']['id']) && 
    !empty($_SESSION['yel']['member']['id'])) {
    $member = new Member($_SESSION['yel']['member']['id']);
    $country_code = strtolower($member->getCountry());
}
$branch_email = 'team.'. $country_code. '@yellowelevator.com';

$mail_lines = file(dirname(__FILE__). '/private/mail/new_referral.txt');
$message = '';
foreach ($mail_lines as $line) {
    $message .= $line;
}

$message = str_replace('%referrer%', htmlspecialchars_decode(stripslashes($referrer['name'])), $message);
$message = str_replace('%candidate%', htmlspecialchars_decode(stripslashes($candidate['name'])), $message);
$message = str_replace('%referrer_email%', $referrer['email_addr'], $message);
$message = str_replace('%candidate_email%', $candidate['email_addr'], $message);
$message = str_replace('%request_on%', $today, $message);
$message = str_replace('%job_title%', $job->getTitle(), $message);
$message = str_replace('%has_resume%', $has_resume, $message);

$subject = "New Referral for ". $job->getTitle(). " position";
$headers = 'From: YellowElevator.com <admin@yellowelevator.com>' . "\n";
mail($branch_email, $subject, $message, $headers);

// $handle = fopen('/tmp/email_to_'. $branch_email. '.txt', 'w');
// fwrite($handle, 'Subject: '. $subject. "\n\n");
// fwrite($handle, $message);
// fclose($handle);

?>
    <script type="text/javascript">top.stop_refer(<?php echo '1'; ?>);</script>
<?php
exit();
?>