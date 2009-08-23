<?php
require_once "../private/lib/utilities.php";

$xml_seed = new XMLDOM();
$xml_seed->load_from_uri("http://". $GLOBALS['root']. "/members/seed.php");
$data = $xml_seed->get_assoc(array('id', 'seed'));
$sid = $data[0]['id'];
$seed = $data[0]['seed'];
$uid = 'pamalite@gmail.com';
$password = 'testuser';
$hash = sha1($uid. md5($password). $seed);

?><b>Logging in...</b><br><br><?php
echo $sid. ": ". $seed. "<br>";
echo $hash. "<br>";

$member = new Member($uid, $sid);
if ($member->is_registered($hash)) {
    if ($member->session_set($hash)) {
        echo "Success<br>";
    }
} 

if (!$member->is_logged_in($hash)) {
    echo "failed<br>";
    exit();
}

echo "<br>";

?><b>Creating a new resume...</b><br><br><?php
$resume = new Resume($uid);
$first_resume = 0;
$second_resume = 0;

$data = array();
$data['name'] = 'Untitled Resume';
$data['modified_on'] = date('Y-m-d');
$data['cover_note'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

$resume->create($data);
if (($first_resume = $resume->id()) > 0) {
    echo "This resume ID is <b>". $resume->id(). "</b><br>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Creating another new resume...</b><br><br><?php
$resume = new Resume($uid);
$data = array();
$data['name'] = 'Technical';
$data['private'] = 'N';
$data['modified_on'] = date('Y-m-d');
$data['cover_note'] = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.';

$resume->create($data);
if (($second_resume = $resume->id()) > 0) {
    echo "This new resume ID is <b>". $resume->id(). "</b><br>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Update the 2nd resume...</b><br><br><?php
$resume = new Resume($uid, $second_resume);
$data = array();
$data['name'] = 'General Resume';
$data['private'] = 'Y';
$data['modified_on'] = date('Y-m-d');
$data['cover_note'] = 'something to cover';

if ($resume->update($data)) {
    echo $resume->get_cover_note();
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Update the 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['name'] = 'For Eyes Only';
$data['private'] = 'N';
$data['modified_on'] = date('Y-m-d');
$data['cover_note'] = 'nothing to share';

if ($resume->update($data)) {
    echo $resume->get_cover_note();
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Create work experience 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['industry'] = '1';
$data['from'] = '2005-01-02';
$data['place'] = 'Intel Corporation Sdn Bhd';
$data['work_summary'] = 'i worked as a janitor';
$work_experience = array();

if ($resume->create_work_experience($data)) {
    $work_experience = $resume->get_work_experiences();
    echo "<pre>";
    print_r($work_experience);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Update work experience 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['id'] = $work_experience[0]['id'];
$data['industry'] = '2';
$data['work_summary'] = 'i worked as a general manager for facilities.';

if ($resume->update_work_experience($data)) {
    $work_experience = $resume->get_work_experiences();
    echo "<pre>";
    print_r($work_experience);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Create education 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['qualification'] = 'Bachelor in Facility Management';
$data['completed_on'] = '1995';
$data['instituition'] = 'Macha University';
$data['country'] = 'ID';
$education = array();

if ($resume->create_education($data)) {
    $education = $resume->get_educations();
    echo "<pre>";
    print_r($education);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Update education 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['id'] = $education[0]['id'];
$data['qualification'] = 'Master Degree in Facility Management';
$data['completed_on'] = '1996';
$data['instituition'] = 'South Hampton Institute of Technology';
$data['country'] = 'US';

if ($resume->update_education($data)) {
    $education = $resume->get_educations();
    echo "<pre>";
    print_r($education);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Create another education 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['qualification'] = 'Diploma in Janitoring';
$data['completed_on'] = '1985';
$data['instituition'] = 'College of Janitors';
$data['country'] = 'SG';
$educations = array();

if ($resume->create_education($data)) {
    $educations = $resume->get_educations();
    echo "<pre>";
    print_r($educations);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Create skill 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['skill'] = 'singing';
$skills = array();

if ($resume->create_skill($data)) {
    $skills = $resume->get_skills();
    echo "<pre>";
    print_r($skills);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Create another skill 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['skill'] = 'dancing';
$skills = array();

if ($resume->create_skill($data)) {
    $skills = $resume->get_skills();
    echo "<pre>";
    print_r($skills);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Update skill 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['id'] = $skills[0]['id'];
$data['skill'] = 'mopping';

if ($resume->update_skill($data)) {
    $skills = $resume->get_skills();
    echo "<pre>";
    print_r($skills);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

?><b>Create technical skill 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['technical_skill'] = 'Microsoft Office';
$data['level'] = 'A';
$technical_skills = array();

if ($resume->create_technical_skill($data)) {
    $technical_skills = $resume->get_technical_skills();
    echo "<pre>";
    print_r($technical_skills);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Create another technical skill 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['technical_skill'] = 'programming';
$technical_skills = array();

if ($resume->create_technical_skill($data)) {
    $technical_skills = $resume->get_technical_skills();
    echo "<pre>";
    print_r($technical_skills);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Update technical skill 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);
$data = array();
$data['id'] = $technical_skills[1]['id'];
$data['technical_skill'] = 'scripting';

if ($resume->update_technical_skill($data)) {
    $technical_skills = $resume->get_technical_skills();
    echo "<pre>";
    print_r($technical_skills);
    echo "</pre>";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Delete 1st resume...</b><br><br><?php
$resume = new Resume($uid, $first_resume);

if ($resume->delete()) {
    echo "deleted";
} else {
    echo "failed";
    exit();
}

echo "<br>";

?><b>Delete 2nd resume...</b><br><br><?php
$resume = new Resume($uid, $second_resume);

if ($resume->delete()) {
    echo "deleted";
} else {
    echo "failed";
    exit();
}

?>