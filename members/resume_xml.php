<?php
require_once dirname(__FILE__). "/../private/lib/utilities.php";

session_start();

if ($GLOBALS['protocol'] == 'https') {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
        redirect_to('https://'. $GLOBALS['root']. '/members/resume_xml.php?id='. $_GET['id']);
        exit();
    }
}

if (!isset($_SESSION['yel']['member']) || 
    empty($_SESSION['yel']['member']['id']) || 
    empty($_SESSION['yel']['member']['sid']) || 
    empty($_SESSION['yel']['member']['hash'])) {
    echo "An illegal attempt to view resume has been detected.";
    exit();
}


$resume = new Resume(0, $_GET['id']);
$cover = $resume->get();

if (!is_null($cover[0]['file_name'])) {
    $file = $resume->get_file();

    header('Content-length: '. $file['size']);
    header('Content-type: '. $file['type']);
    header('Content-Disposition: attachment; filename="'. $file['name'].'"');

    readfile($GLOBALS['resume_dir']. "/". $_GET['id']. ".". $file['hash']);
    exit();
} 

$xml_dom = new XMLDOM();
$member = new Member($cover[0]['member']);
$contacts = $member->get();
$experiences = $resume->get_work_experiences();
$educations = $resume->get_educations();
$skills = $resume->get_skills();
$technical_skills = $resume->get_technical_skills();

$resume_data = array();
$resume_data['resume']['_ATTRS'] = array('candidate' => $member->get_name());
$resume_data['resume']['DISCLAIMER_NOTE'] = 'Generated from YellowElevator.com. Resume Terms of Use subjected.';
$resume_data['resume']['contacts']['telephone_number'] = $contacts[0]['phone_num'];
$resume_data['resume']['contacts']['email_address'] = $contacts[0]['email_addr'];
$resume_data['resume']['contacts']['address'] = $contacts[0]['address'];
$resume_data['resume']['contacts']['state'] = $contacts[0]['state'];
$resume_data['resume']['contacts']['country'] = Country::getCountryFrom($contacts[0]['country']);

$resume_data['resume']['work_experiences'] = array();
if (count($experiences) > 0) {
    $i = 0;
    foreach($experiences as $experience) {
        $industry = Industry::get($experience['industry']);
        
        $resume_data['resume']['work_experiences']['work_experience'][$i]['industry'] = $industry[0]['industry'];
        $resume_data['resume']['work_experiences']['work_experience'][$i]['from'] = $experience['from'];
        $resume_data['resume']['work_experiences']['work_experience'][$i]['to'] = $experience['to'];
        $resume_data['resume']['work_experiences']['work_experience'][$i]['place'] = $experience['place'];
        $resume_data['resume']['work_experiences']['work_experience'][$i]['role'] = $experience['role'];
        $resume_data['resume']['work_experiences']['work_experience'][$i]['work_summary'] = $experience['work_summary'];
        
        $i++;
    }
}

$resume_data['resume']['educations'] = array();
if (count($educations) > 0) {
    $i = 0;
    foreach($educations as $education) {
        $resume_data['resume']['educations']['education'][$i]['qualification'] = $education['qualification'];
        $resume_data['resume']['educations']['education'][$i]['completion_year'] = $education['completed_on'];
        $resume_data['resume']['educations']['education'][$i]['institution'] = $education['institution'];
        $resume_data['resume']['educations']['education'][$i]['country'] = Country::getCountryFrom($education['country']);
        
        $i++;
    }
}

$resume_data['resume']['skills'] = '';
if (!is_null($skills[0]['skill']) && !empty($skills[0]['skill'])) {
    $resume_data['resume']['skills'] = $skills[0]['skill'];
}

$resume_data['resume']['technical_skills'] = array();
if (count($technical_skills) > 0) {
    $i = 0;
    $levels = array('A' => 'Beginner', 'B' => 'Intermediate', 'C' => 'Advanced');
    foreach($technical_skills as $technical_skill) {
        $resume_data['resume']['technical_skills']['technical_skill'][$i]['tech_skill'] = $technical_skill['technical_skill'];
        $resume_data['resume']['technical_skills']['technical_skill'][$i]['level'] = $levels[$technical_skill['level']];
        
        $i++;
    }
}

$resume_data['resume']['cover_note'] = '';
if (!is_null($cover[0]['cover_note']) && !empty($cover[0]['cover_note'])) {
    $resume_data['resume']['cover_note'] = $cover[0]['cover_note'];
}

header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="resume.xml"');
echo $xml_dom->get_xml_from_array($resume_data);
?>
