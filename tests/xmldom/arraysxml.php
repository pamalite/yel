<?php
require_once "../../private/lib/utilities.php";

$xml_array = array(
    'book' => array(
        '_attrs' => array(
            'attr1' => 'test1',
            'attr2' => 'test2'
        ),
        'myfairlady'
    ),
);

$xml_array = array(
    'properties' => array(
        '_attrs' => array (
            'attr1' => 'lalala',
            'attr2' => 'qwerty'
        ),
        'property' => array(
            array(
                '_attrs' => array(
                    'id' => '01',
                    'type' => 'TEXT'),
                'key' => 'system.database.type',
                'value' => 'MYSQL_DB'
            ),
            array(
                '_attrs' => array(
                    'id' => '02',
                    'type' => 'CHECK'),
                'key' => 'system.database.connection.host',
                'value' => 'localhost',
                'props' => array(
                    'prop' => array(
                        array(
                            '_attrs' => array(
                                'id' => '01',
                                'type' => 'TEXT'),
                            'key' => 'system.database.type',
                            'value' => 'MYSQL_DB'
                        ),
                        array(
                            '_attrs' => array(
                                'id' => '02',
                                'type' => 'CHECK'),
                            'key' => 'system.database.connection.host',
                            'value' => 'localhost'
                        ),
                        array(
                            '_attrs' => array(
                                'id' => '03',
                                'type' => 'BINARY'),
                            'key' => 'system.database.connection.user',
                            'value' => 'root', 
                            'stuffs' => array(
                                'mine' => 'haha',
                                'yours' => 'hehe'
                            )
                        )
                    )
                )
            ),
            array(
                '_attrs' => array(
                    'id' => '03',
                    'type' => 'BINARY'),
                'key' => 'system.database.connection.user',
                'value' => 'root', 
                'stuffs' => array(
                    'mine' => 'haha',
                    'yours' => 'hehe'
                )
            )
        )
    )
);
/*
$xml_array = array(
    'properties' => array(
        '_attrs' => array (
            'attr1' => 'lalala',
            'attr2' => 'qwerty'
        ),
        'property' => array(
            array(
                'key' => 'system.database.type',
                'value' => 'MYSQL_DB'),
            array(
                'key' => 'system.database.connection.host',
                'value' => 'localhost'),
            array(
                'key' => 'system.database.connection.user',
                'value' => 'root')
        )
    )
);*/
/*
$xml_array = array(
    'properties' => array(
        '_attrs' => array (
            'attr1' => 'lalala',
            'attr2' => 'qwerty'
        ),
        'name' => 'system.database.type',
        'value' => 'MYSQL_DB',
        'key' => 'system.database.connection.host'
    )
);
*/
$xml_doc = new XMLDOM ();
//header('Content-type: text/xml');
$start = microtime();
echo $xml_doc->get_xml_from_array($xml_array);
$end = microtime();
list($start_usec, $start_sec) = explode(" ", $start);
list($end_usec, $end_sec) = explode(" ", $end);
$start = ((float)$start_sec + (float)$start_usec);
$end = ((float)$end_sec + (float)$end_usec);
echo "<p>Start: ". $start. "s</p>";
echo "<p>Stop: ". $end. "s</p>";
echo "<p>Elapsed: ". ($end - $start). " seconds</p>";

?>