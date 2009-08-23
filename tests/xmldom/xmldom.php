<?php
require_once "../../private/lib/utilities.php";

$xml_dom = new XMLDOM();

echo "Attributes test: <br><br>";
if ($xml_dom->load_from_uri(dirname(__FILE__). "/text.xml")) {
    $properties = $xml_dom->get("property");
    $propertys = array();
    
    foreach ($properties as $property) {
        $key = "";
        foreach ($property->attributes as $attribute) {
            switch ($attribute->name) {
                case "key":
                    $key = $attribute->value;
                    break;
                default:
                    if (!empty($key)) {
                        $propertys[$key][$attribute->name] = $attribute->value;
                    }
                    break;
            }            
        }
    }
    
    echo "<pre>";
    print_r($propertys);
    echo "</pre>";
} else {
    echo "failed";
}

echo "<br><br>";

echo "Elements test: <br><br>";
if ($xml_dom->load_from_uri(dirname(__FILE__). "/text2.xml")) {
    $properties = $xml_dom->get("property");
    $propertys = array();
    
    foreach ($properties as $property) {
        $key = "";
        foreach ($property->childNodes as $node) {
            switch ($node->nodeName) {
                case "key":
                    $key = $node->nodeValue;
                    $propertys[$key] = array();
                    break;
                default:
                    if (!empty($key)) {
                        if ($node->nodeName != '#text') {
                            $propertys[$key][$node->nodeName] = $node->nodeValue;
                        }
                    }
                    break;
            } 
        }
    }
    
    echo "<pre>";
    print_r($propertys);
    echo "</pre>";
} else {
    echo "failed";
}

echo "<br><br>";

echo "get_assoc test: <br><br>";
if ($xml_dom->load_from_uri(dirname(__FILE__). "/text3.xml")) {
    $propertys = $xml_dom->get_assoc(array('key', 'value', 'default'));
    
    echo "<pre>";
    print_r($propertys);
    echo "</pre>";
} else {
    echo "failed";
}
?>
