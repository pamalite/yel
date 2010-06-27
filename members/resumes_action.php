<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if (!isset($_POST['id'])) {
    echo "ko";
    exit();
    //redirect_to('login.php');
}

$xml_dom = new XMLDOM();

if (!isset($_POST['action'])) {
    redirect_to('resumes.php');
}

if ($_POST['action'] == 'get_resumes') {
    $order_by = 'modified_on desc';

    if (isset($_POST['order_by'])) {
        $order_by = $_POST['order_by'];
    }
    
    $resume = new Resume();
    
    $criteria = array(
        'columns' => 'id, file_name, DATE_FORMAT(modified_on, \'%e %b, %Y\') AS formatted_modified_on',
        'order' => $order_by,
        'match' => 'member = \''. $_POST['id']. '\' AND deleted = \'N\''
    );
    
    $result = $resume->find($criteria);
    $response = array(
        'resumes' => array('resume' => $result)
    );

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    
    exit();
}

if ($_POST['action'] == 'upload') {
    $resume = NULL;
    $member = new Member($_POST['member']);
    $is_update = false;
    $data = array();
    $data['modified_on'] = now();
    $data['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['private'] = 'N';
    
    if ($_POST['id'] == '0') {
        $resume = new Resume($member->getId());
        if (!$resume->create($data)) {
            redirect_to('resumes.php?error=1');
            exit();
        }
    } else {
        $resume = new Resume($member->getId(), $_POST['id']);
        $is_update = true;
        if (!$resume->update($data)) {
            redirect_to('resumes.php?error=2');
            exit();
        }
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = str_replace(array('\'', '"', '\\'), '', basename($_FILES['my_file']['name']));
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];
    
    if ($resume->uploadFile($data, $is_update) === false) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->getId(). ";
                  DELETE FROM resumes WHERE id = ". $resume->getId();
        $mysqli = Database::connect();
        $mysqli->transact($query);
        redirect_to('resumes.php?error=3');
        exit();
    }
    
    redirect_to('resumes.php');
    exit();
}

?>