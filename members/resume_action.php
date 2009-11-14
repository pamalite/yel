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
    $resume = new Resume($_POST['member'], $_POST['id']);
    $response =  array('resume' => $resume->get());

    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'privatize') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $data = array();
    $data['private'] = 'Y';
    
    if ($resume->update($data) == false) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'unprivatize') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $data = array();
    $data['private'] = 'N';
    
    if ($resume->update($data) == false) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'delete') {
    if (!isset($_POST['payload'])) {
        echo "ko";
        exit();
    }
    
    $xml_dom->load_from_xml($_POST['payload']);
    $resumes = $xml_dom->get('id');
    $query = "UPDATE resumes SET deleted = 'Y' WHERE id IN (";
    $i = 0;
    foreach ($resumes as $id) {
        $query .= $id->nodeValue;
        
        if ($i < $resumes->length-1) {
            $query .= ", ";
        }
        
        $i++;
    }
    $query .= ")";
    
    $mysqli = Database::connect();
    
    if (!$mysqli->execute($query)) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'upload') {
    $resume = NULL;
    $is_update = false;
    $data = array();
    $data['modified_on'] = now();
    $data['name'] = $_FILES['my_file']['name'];
    $data['private'] = 'N';
    
    if ($_POST['id'] == '0') {
        $resume = new Resume($_POST['member']);
        if (!$resume->create($data)) {
            ?>
                <script type="text/javascript">top.stop_upload(<?php echo "0"; ?>);</script>
            <?php
            exit();
        }
    } else {
        $resume = new Resume($_POST['member'], $_POST['id']);
        $is_update = true;
        if (!$resume->update($data)) {
            ?>
                <script type="text/javascript">top.stop_upload(<?php echo "0"; ?>);</script>
            <?php
            exit();
        }
    }
    
    $data = array();
    $data['FILE'] = array();
    $data['FILE']['type'] = $_FILES['my_file']['type'];
    $data['FILE']['size'] = $_FILES['my_file']['size'];
    $data['FILE']['name'] = $_FILES['my_file']['name'];
    $data['FILE']['tmp_name'] = $_FILES['my_file']['tmp_name'];
    
    if (!$resume->upload_file($data, $is_update)) {
        $query = "DELETE FROM resume_index WHERE resume = ". $resume->id(). ";
                  DELETE FROM resumes WHERE id = ". $resume->id();
        $mysqli = Database::connect();
        $mysqli->transact($query);
        ?><script type="text/javascript">top.stop_upload(<?php echo "0"; ?>);</script><?php
        exit();
    }
    
    ?><script type="text/javascript">top.stop_upload(<?php echo "1"; ?>);</script><?php
    exit();
}

if ($_POST['action'] == 'get_cover_note') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $cover_note = $resume->get_cover_note();
    $response = array('resume' => $cover_note);
    $responses[0]['cover_note'] = desanitize($responses[0]['cover_note']);
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'save_cover_note') {
    $resume = null;
    $data = array();
    $response = array();
    $data['modified_on'] = now();
    
    if ($_POST['id'] == "0") {
        $resume = new Resume($_POST['member']);
        //$data['private'] = $_POST['private'];
        $data['name'] = $_POST['name'];
        $data['cover_note'] = sanitize($_POST['cover_note']);
        if (!$resume->create($data)) {
            echo "ko";
            exit();
        }
        
        header('Content-type: text/xml');
        $response['resume']['id'] = $resume->id();
        echo $xml_dom->get_xml_from_array($response);
        
    } else {
        $resume = new Resume($_POST['member'], $_POST['id']);
        //$data['private'] = $_POST['private'];
        $data['name'] = $_POST['name'];
        $data['cover_note'] = $_POST['cover_note'];
        if (!$resume->update($data)) {
            echo "ko";
            exit();
        }
        
        header('Content-type: text/xml');
        $response['resume']['id'] = $resume->id();
        echo $xml_dom->get_xml_from_array($response);
        
    }
    
    exit();
}

if ($_POST['action'] == 'get_work_experiences') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $work_experiences = $resume->get_work_experiences();
    foreach ($work_experiences as $i=>$row) {
        $work_experiences[$i]['description'] = desanitize($row['description']);
    }
    $response = array('resume' => array('work_experiences' => $work_experiences));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'delete_work_experience') {
    if (!isset($_POST['experience']) || is_null($_POST['experience']) || empty($_POST['experience'])) {
        echo "ko";
        exit();
    } 
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    if (!$resume->delete_work_experience($_POST['experience'])) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'save_work_experience') {
    if (!isset($_POST['experience']) || !isset($_POST['industry']) || 
        !isset($_POST['from']) || !isset($_POST['role']) || 
        !isset($_POST['place']) || !isset($_POST['work_summary'])) {
        echo 'ko';
        exit();
    }
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    $to = "";
    if (isset($_POST['to'])) {
        $to = $_POST['to'];
    }
    
    $data = array();
    $data['id'] = $_POST['experience'];
    $data['industry'] = $_POST['industry'];
    $data['from'] = $_POST['from'];
    $data['to'] = $to;
    $data['role'] = $_POST['role'];
    $data['place'] = $_POST['place'];
    $data['work_summary'] = sanitize($_POST['work_summary']);
    $data['reason_for_leaving'] = $_POST['reason_for_leaving'];
    
    if ($_POST['experience'] == "0") {
        if (!$resume->create_work_experience($data)) {
            echo 'ko1';
            exit();
        }
    } else {
        if (!$resume->update_work_experience($data)) {
            echo 'ko2';
            exit();
        }
    }
    
    echo 'ok';
    exit();
}

if ($_POST['action'] == 'get_educations') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $educations = $resume->get_educations();
    $response = array('resume' => array('educations' => $educations));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'delete_education') {
    if (!isset($_POST['education']) || is_null($_POST['education']) || empty($_POST['education'])) {
        echo "ko";
        exit();
    } 
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    if (!$resume->delete_education($_POST['education'])) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'save_education') {
    if (!isset($_POST['education']) || !isset($_POST['country']) || 
        !isset($_POST['completed_on']) || !isset($_POST['qualification']) || 
        !isset($_POST['institution'])) {
        echo "ko";
        exit();
    }
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    
    $data = array();
    $data['id'] = $_POST['education'];
    $data['country'] = $_POST['country'];
    $data['qualification'] = $_POST['qualification'];
    $data['institution'] = $_POST['institution'];
    $data['completed_on'] = $_POST['completed_on'];
    
    if ($_POST['education'] == "0") {
        if (!$resume->create_education($data)) {
            echo "ko";
            exit();
        }
    } else {
        if (!$resume->update_education($data)) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_skills') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $educations = $resume->get_skills();
    $response = array('resume' => array('educations' => $educations));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'save_skills') {
    if (!isset($_POST['skill']) || !isset($_POST['skill'])) {
        echo "ko";
        exit();
    }
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    
    $data = array();
    $data['id'] = $_POST['skill'];
    $data['skill'] = sanitize($_POST['skills']);
    
    if ($_POST['skill'] == "0") {
        if (!$resume->create_skill($data)) {
            echo "ko";
            exit();
        }
    } else {
        if (!$resume->update_skill($data)) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'delete_technical_skill') {
    if (!isset($_POST['technical_skill']) || is_null($_POST['technical_skill']) || empty($_POST['technical_skill'])) {
        echo "ko";
        exit();
    } 
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    if (!$resume->delete_technical_skill($_POST['technical_skill'])) {
        echo "ko";
        exit();
    }
    
    echo "ok";
    exit();
}

if ($_POST['action'] == 'get_technical_skills') {
    $resume = new Resume($_POST['member'], $_POST['id']);
    $technical_skills = $resume->get_technical_skills();
    $response = array('resume' => array('technicals' => $technical_skills));
    header('Content-type: text/xml');
    echo $xml_dom->get_xml_from_array($response);
    exit();
}

if ($_POST['action'] == 'save_technical_skill') {
    if (!isset($_POST['technical_skill']) || !isset($_POST['level']) || 
        !isset($_POST['skill'])) {
        echo "ko";
        exit();
    }
    
    $resume = new Resume($_POST['member'], $_POST['id']);
    
    $data = array();
    $data['id'] = $_POST['technical_skill'];
    $data['technical_skill'] = sanitize($_POST['skill']);
    $data['level'] = $_POST['level'];
    
    if ($_POST['technical_skill'] == "0") {
        if (!$resume->create_technical_skill($data)) {
            echo "ko";
            exit();
        }
    } else {
        if (!$resume->update_technical_skill($data)) {
            echo "ko";
            exit();
        }
    }
    
    echo "ok";
    exit();
}

?>
