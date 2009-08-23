<?php
require_once dirname(__FILE__). "/../../private/lib/utilities.php";

$query = "INSERT INTO resume_index SELECT 
          resumes.id, 
          resumes.member, 
          NULL, 
          resumes.cover_note, 
          NULL, 
          NULL, 
          NULL 
          from resumes";
$mysqli = Database::connect();
if ($mysqli->execute($query)) {
    $query = "SELECT id, member FROM resumes";
    $result = $mysqli->query($query);
    
    foreach ($result as $row) {
        $experiences = array();
        $tmp = "";
        $query = "SELECT work_summary from resume_work_experiences where resume = ". $row['id'];
        $experiences = $mysqli->query($query);
        foreach ($experiences as $xp) {
            $tmp .= $xp['work_summary']. " ";
        }
        $tmp = sanitize(trim($tmp));
        $query = "UPDATE resume_index SET work_summary = '". $tmp. "' WHERE resume = ". $row['id']. " AND member = '". $row['member']. "'";
        if (!$mysqli->execute($query)) {
            echo "ko - xps";
            exit();
        }
    }
    
    foreach ($result as $row) {
        $educations = array();
        $tmp = "";
        $query = "SELECT qualification from resume_educations where resume = ". $row['id'];
        $educations = $mysqli->query($query);
        foreach ($educations as $edu) {
            $tmp .= $edu['qualification']. " ";
        }
        $tmp = sanitize(trim($tmp));
        $query = "UPDATE resume_index SET qualification = '". $tmp. "' WHERE resume = ". $row['id']. " AND member = '". $row['member']. "'";
        if (!$mysqli->execute($query)) {
            echo "ko - edu";
            exit();
        }
    }
    
    foreach ($result as $row) {
        $skills = array();
        $tmp = "";
        $query = "SELECT skill from resume_skills where resume = ". $row['id'];
        $skills = $mysqli->query($query);
        foreach ($skills as $skill) {
            $tmp .= $skill['skill']. " ";
        }
        $tmp = sanitize(trim($tmp));
        $query = "UPDATE resume_index SET skill = '". $tmp. "' WHERE resume = ". $row['id']. " AND member = '". $row['member']. "'";
        if (!$mysqli->execute($query)) {
            echo "ko - skl";
            exit();
        }
    }
    
    echo "ok";
} else {
    echo "ko";
}
?>