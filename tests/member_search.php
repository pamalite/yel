<?php
require_once "../private/lib/utilities.php";
require_once "../private/lib/classes/member_search.php";

function show($_message) {
    echo '<pre>';
    if (is_array($_message)) {
        print_r($_message);
    } else {
        echo $_message;
    }
    echo '</pre>';
}



$xml_seed = new XMLDOM();

$keywords_entered = array();
$search_criterias = array();

?><b>Criteria...</b><br><br><?php

$keywords_entered = array(
    'resume' => 'software developer', 
    'notes' => 'qwerty',
    'seeking' => 'asdfg'
);

$search_criterias = array(
    'resume_keywords' => $keywords_entered['resume'],
    'resume_is_boolean' => false,
    'resume_is_use_all_words' => false,
    'notes_keywords' => $keywords_entered['notes'],
    'notes_is_boolean' => false,
    'notes_is_use_all_words' => false,
    'seeking_keywords' => $keywords_entered['seeking'],
    'seeking_is_boolean' => false,
    'seeking_is_use_all_words' => false
);

show('keywords: ');
show($keywords_entered);

echo '<br/>';

show('criteria: ');
show($search_criterias);

echo '<br/>';

?><b>Query #1...</b><br><br><?php
$search = new MemberSearch();

$result = $search->search_using($search_criterias);

show('query: ');
show($search->get_query());

echo '<br/>';

?><b>Query #2...</b><br><br><?php

$search_criterias = array(
    'resume_keywords' => $keywords_entered['resume'], 
    'order_by' => 'score DESC'
);

$search = new MemberSearch();

$result = $search->search_using($search_criterias);

show('query: ');
show($search->get_query());

echo '<br/>';

?><b>Result sorting...</b><br><br><?php
if ($result <= 0 || $result === false) {
    show('no results');
    exit();
}

show('results');
show($result);

echo '<br/>';

?><b>Result sorting ascending...</b><br><br><?php

$search_criterias = array(
    'resume_keywords' => $keywords_entered['resume'], 
    'order_by' => 'score ASC', 
    'offset' => 10
);

$search->reset_query();
$result = $search->search_using($search_criterias);

if ($result <= 0 || $result === false) {
    show('no results');
    exit();
}

show('results');
show($result);

echo '<br/>';

?><b>Result filter members only...</b><br><br><?php

$search_criterias = array(
    'resume_keywords' => $keywords_entered['resume'], 
    'filter' => 'members_only', 
    'order_by' => 'score DESC'
);

$search->reset_query();
$result = $search->search_using($search_criterias);

if ($result <= 0 || $result === false) {
    show('no results');
    exit();
}

show('results');
show($result);

echo '<br/>';

?><b>Result buffer only...</b><br><br><?php

$search_criterias = array(
    'resume_keywords' => $keywords_entered['resume'], 
    'notes_keywords' => 'qwerty',
    'filter' => 'buffer_only', 
    'order_by' => 'score DESC'
);

$search->reset_query();
$result = $search->search_using($search_criterias);
show($search->get_query());

if ($result <= 0 || $result === false) {
    show('no results');
    exit();
}

show('results');
show($result);

echo '<br/>';

?><b>Result with no keywords...</b><br><br><?php

$search_criterias = array(
    // 'hrm_gender' => 'male', 
    // 'expected_salary' => '10',
    // 'can_travel_relocate' => false, 
    // 'current_salary' => '1', 
    // 'is_active_seeking_job' => true, 
    // 'notice_period' => 3, 
    'hrm_ethnicity' => '%malay%'
);

$search->reset_query();
$result = $search->search_using($search_criterias);
show($search->get_query());

if ($result <= 0 || $result === false) {
    show('no results');
    exit();
}

show('results');
show($result);


exit();
?>