<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";
require_once dirname(__FILE__). $GLOBALS['openinviter_path']. "/openinviter.php";

session_start();

if (!isset($_POST['id'])) {
    echo 'ko';
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $email_addresses = $_POST['email_addresses'];
    $header = 'From: '. $member->id(). "\n". 'Reply-To: '. $member->id();
    $subject = $member->get_name(). ' writing on behalf of Yellow Elevator - a job referral system';

    $lines = file(dirname(__FILE__). '/../private/mail/member_tell_a_friend.txt');
    $message = '';
    foreach ($lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', htmlspecialchars_decode($member->get_name()), $message);
    $message = str_replace('%member_email_addr%', $member->id(), $message);
    $message = str_replace('%message%', stripslashes(urldecode($_POST['message'])), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);

    if (!mail($email_addresses, $subject, $message, $header)) {
        echo 'ko';
        exit();
    } 

    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_contacts') {
    $raw_contacts = array();
    $_SESSION['yel']['member'][$_POST['id']]['contacts'] = array();
    $inviter = new OpenInviter();
    $oi_services = $inviter->getPlugins();
    
    $plug_type = 'email';
    if (isset($oi_services['social'][$_POST['oi_service']])) {
        $plug_type = 'social';
    }
    
    $inviter->startPlugin($_POST['oi_service']);
    
    $internal_errors = $inviter->getInternalError();
    if ($internal_errors) {
        echo '-1';
        exit();
    } 
    
    if (!$inviter->login($_POST['username'], $_POST['password'])) {
        echo '-2';
        exit();
    } 
    
    if (($raw_contacts = $inviter->getMyContacts()) === false) {
        echo '-3';
        exit();
    } 
    
    $contacts = array();
    $i = 0;
    foreach($raw_contacts as $key => $value) {
        $contacts[$i]['email'] = $key;
        $contacts[$i]['name'] = $value;
        $contacts[$i]['index'] = $i;
        $i++;
    }
    $_SESSION['yel']['member'][$_POST['id']]['contacts'] = $contacts;
    
    $response = array('openinviter' => array(
        'sessionid' => $inviter->plugin->getSessionID(),
        'plugin_type' => $plug_type,
        'contacts' => $contacts
    ));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'smart_send_invites') {
    $raw_contacts = explode('|', $_POST['selected_contacts']);
    $contacts = array();
    foreach($raw_contacts as $index) {
        $contacts[$_SESSION['yel']['member'][$_POST['id']]['contacts'][$index]['email']] = $_SESSION['yel']['member'][$_POST['id']]['contacts'][$index]['name'];
    }
    
    $inviter = new OpenInviter();
    $inviter->startPlugin($_POST['oi_service']);
    $internal_errors = $inviter->getInternalError();
    
    if ($internal_errors) {
        echo '-1';
        exit();
    }
    
    $member = new Member($_POST['id'], $_SESSION['yel']['member']['sid']);
    $subject = htmlspecialchars_decode(urldecode($member->get_name())). ' writing on behalf of Yellow Elevator - a job referral system';
    $header = 'From: '. $_POST['username']. "\n". 'Reply-To: '. $_POST['username'];
    
    $lines = file(dirname(__FILE__). '/../private/mail/member_tell_a_friend.txt');
    $message = '';
    foreach ($lines as $line) {
        $message .= $line;
    }

    $message = str_replace('%member_name%', htmlspecialchars_decode(urldecode($member->get_name())), $message);
    $message = str_replace('%member_email_addr%', $_POST['username'], $message);
    $message = str_replace('%message%', stripslashes(urldecode($_POST['message'])), $message);
    $message = str_replace('%protocol%', $GLOBALS['protocol'], $message);
    $message = str_replace('%root%', $GLOBALS['root'], $message);
    
    $invitation = array(
        'subject' => $subject,
        'body' => $message
    );
    
    // send to social services
    $sendMessage = $inviter->sendMessage($_POST['oi_session_id'], $invitation, $contacts);
    $inviter->logout();
    
    // if not, send via email
    if ($sendMessage === -1) {
        foreach ($contacts as $email => $name) {
            mail($email, $subject, $message, $header);
        }
    } elseif ($sendMessage === false) {
        echo 'ko';
        exit();
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_contacts_from_vcard') {
    if (!isset($_FILES['my_file'])) {
        ?><script type="text/javascript">window.top.window.stop_upload();</script><?php
        exit();
    }
    
    $vcard_file = $_FILES['my_file']['tmp_name'];
    $lines = file($vcard_file);
    if (!$lines) {
        ?><script type="text/javascript">window.top.window.stop_upload();</script><?php
        exit();
    }

    // parse the file into $cards
    $cards = array();
    $card = new VCard();
    while ($card->parse($lines)) {
        $property = $card->getProperty('N');
        if (!$property) {
            ?><script type="text/javascript">window.top.window.stop_upload();</script><?php
            exit();
        }
        $n = $property->getComponents();
        $tmp = array();
        if ($n[3]) $tmp[] = $n[3];      // Mr.
        if ($n[1]) $tmp[] = $n[1];      // John
        if ($n[2]) $tmp[] = $n[2];      // Quinlan
        if ($n[4]) $tmp[] = $n[4];      // Esq.
        $ret = array();
        if ($n[0]) $ret[] = $n[0];
        $tmp = join(" ", $tmp);
        if ($tmp) $ret[] = $tmp;
        $key = join(", ", $ret);
        $cards[$key] = $card;
        // MDH: Create new VCard to prevent overwriting previous one (PHP5)
        $card = new VCard();
    }
    ksort($cards);
    
    // extract the emails
    $contacts = array();
    
    $i = 0;
    foreach ($cards as $card_name=>$card) {
        $emails = array();
        
        $properties = $card->getProperties('EMAIL');
        if ($properties) {
            $count = 0;
            foreach ($properties as $property) {
                $emails[$count] = array($property->value);
                $count++;
            }
        }
        
        if (count($emails) > 0) {
            $contacts[$i] = array(
                'name' => $card_name,
                'emails' => array(
                    'email' => $emails
                )
            );
            $i++;
        }
    }
    
    unlink($vcard_file);
    
    $response = array('contacts' => array('contact' => sanitize($contacts)));
    $xml = $xml_dom->get_xml_from_array($response);
    $xml = substr($xml, strpos($xml, "\n"));
    $xml = str_replace("\n", '', $xml);
    ?>
        <?php echo $xml; ?>
        <script type="text/javascript">
            var txt = '<?php echo $xml; ?>';
            window.top.window.parse_contacts(txt);
        </script>
    <?php
    exit();
}
?>
