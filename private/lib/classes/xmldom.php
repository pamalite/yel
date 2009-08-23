<?php
class XMLDOM {
    private $xml_doc;
    
    function __construct() {
        $this->xml_doc = new DOMDocument();
    }
    
    /**
     * This method will built the DOM document from a string of XML. 
     * @param   _xml    The XML string to be build upon. 
     * @return  mixed   The constructed DOMDocument object, FALSE otherwise.
     */
    public function load_from_xml($_xml) {
        $this->xml_doc = new DOMDocument();
        $this->data = array();
        return $this->xml_doc->loadXML($_xml);
    }
    
    /**
     * This method will built the DOM document from a string of XML. 
     * @param   _uri    The full path to the XML document. 
     * @return  mixed   The constructed DOMDocument object, FALSE othertise.
     */
    public function load_from_uri($_uri) {
        $this->xml_doc = new DOMDocument();
        $this->data = array();
        
        if (!empty($_uri)) {
            if ($response = @file($_uri)) {
                $xml = "";
                foreach ($response as $line) {
                    $xml .= $line;
                }
                
                return $this->xml_doc->loadXML($xml);
            }
        } 
        
        return false;
    }
    
    /**
     * Get the DOM document object.
     * @return DOMDocument  The build DOMDocument object.
     */
    public function xml_dom() {
        return $this->xml_doc;
    }
    
    /**
     * Get the elements by tagname. 
     * @param tag       The tagname used in the XML string or document. 
     * @return mixed    Returns a DOMNode object, if there is only one node, 
     *                  or DOMNodeList object, if there are more nodes.
     */
    public function get($tag) {
        return $this->xml_doc->getElementsByTagName($tag);
    }
    
    /**
     * Get an array of associative arrays, with the tags as keys. 
     * @param tags      An array of tags to be associated, and can be found in the XML string or document.
     * @return mixed    Returns an array of associative arrays with the tags as keys, FALSE otherwise.
     */
    public function get_assoc($tags) {
        if (empty($tags) || is_null($tags) || !is_array($tags)) {
            return false;
        }
        
        $records = array();
        foreach ($tags as $tag) {
            $elements = $this->xml_doc->getElementsByTagName($tag);
            
            $i = 0;
            foreach ($elements as $element) {
                $records[$i][$tag] = $element->nodeValue;
                $i++;
            }            
        }
        
        return $records;
    }
    
    /**
     * This method recursively add an element being serialized in an array. 
     * @param _key          The tag name. 
     * @param _data         The data stored in associative array.
     * @param _parent_node  The parent node of the _key. 
     * NOTES:
     * - There is no Namespace support. 
     * - To associate attributes to an element, use the '_attrs' or '_ATTRS' key.
     */
    private function add_recursive($_key, $_data, $_parent_node) {
        if (empty($_key) || !is_a($_parent_node, "DOMNode") || !is_array($_data)) {
            return false;
        }
        
        foreach ($_data as $key => $value) {
            if (strtoupper($key) == "_ATTRS") {
                foreach ($value as $attr_key => $attr_value) {
                    $attr = $this->xml_doc->createAttribute($attr_key);
                    $_parent_node->appendChild($attr);
                    $attr_val = $this->xml_doc->createTextNode($attr_value);
                    $attr->appendChild($attr_val);
                } 
            } else {
                $child = $this->xml_doc->createElement($key);
                $child = $_parent_node->appendChild($child);
                
                if (is_array($value)) {
                    $this->add_recursive($key, $value, $child);
                } else {
                    $child_text = $this->xml_doc->createTextNode($value);
                    $child->appendChild($child_text);
                }
            }
        }
    }
    
    /**
     * This method walks the tree-like structure from an array, and then creates the DOM document. 
     * @param _element_tag      The tag name of the element.
     * @param _data             The data stored in associative array.
     * @param _parent           The parent node of _element_tag. 
     * @return boolean          Returns false if the input parameters are faulty, or there is more than 1 parent. It
     *                          will return true otherwise
     * NOTES:
     * - There is no Namespace support. 
     * - To associate attributes to an element, use the '_attrs' or '_ATTRS' key.
     */
    public function add($_element_tag, $_data, $_parent) {
        if (empty($_element_tag) || empty($_parent) || !is_array($_data)) {
            return false;
        }
        
        $parents = $this->xml_doc->getElementsByTagName($_parent);
        if ($parents->length > 1) {
            return false;
        }
        
        $parent = $parents->item(0);
        $child = $this->xml_doc->createElement($_element_tag);
        $parent = $parent->appendChild($child);
        foreach ($_data as $key => $value) {
            if (strtoupper($key) == "_ATTRS") {
                foreach ($value as $attr_key => $attr_value) {
                    $attr = $this->xml_doc->createAttribute($attr_key);
                    $parent->appendChild($attr);
                    $attr_val = $this->xml_doc->createTextNode($attr_value);
                    $attr->appendChild($attr_val);
                } 
            } else {
                $inner_child = $this->xml_doc->createElement($key);
                $child = $parent->appendChild($inner_child);
                
                if (is_array($value)) {
                    $this->add_recursive($key, $value, $inner_child);
                } else {
                    $inner_child_text = $this->xml_doc->createTextNode($value);
                    $inner_child->appendChild($inner_child_text);
                }
            }
        }

        return true;
    }
    
    /**
     * Remove a node from its parent in the xml_doc this object is currently holding. 
     * @param _parent_tag   The tag name of the parent of _node.
     * @param _node         The node that needs to be removed.
     * @return boolean      Returns false if the parameters are faulty, else true.
     */
    public function remove($_parent_tag, $_node) {
        if (empty($_parent_tag) || !is_a($_node, "DOMNode")) {
            return false;
        }
        
        $parents = $this->xml_doc->getElementsByTagName($_parent_tag);
        foreach ($parents as $parent) {
            if ($parent->hasChildNodes()) {
                foreach ($parent->childNodes as $child) {
                    if ($child->isSameNode($_node)) {
                        $parent->removeChild($child);
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * This method walks the tree-like structure from an array, and then creates the DOM document. 
     * @param _dom      Reference to the DOM document currently written.
     * @param data      The data stored in associative array.
     * @param _parent   (Optional) The parent node of the currently traversed branch. 
     * @return _dom     The referenced DOM document with all the elements appended.
     * NOTES:
     * - There is no Namespace support. 
     * - To associate attributes to an element, use the '_attrs' or '_ATTRS' key.
     * FAULT: 
     * - It will create an empty element like "<element/>". 
     */
    private function make_xml_dom(&$_dom, $data, $_parent = "") {
        $parent = "";
        
        foreach ($data as $key => $value) {
            if (strtoupper($key) == "_ATTRS") {
                foreach ($value as $attr_key => $attr_value) {
                    $attr = $_dom->createAttribute($attr_key);
                    $_parent->appendChild($attr);
                    $attr_val = $_dom->createTextNode($attr_value);
                    $attr->appendChild($attr_val);
                } 
            } else if (is_numeric($key)) {
                if (is_array($value)) {
                    $parent = $_dom->createElement($_parent->nodeName);
                    $parent = $_parent->parentNode->appendChild($parent);
                    $this->make_xml_dom($_dom, $value, $parent);
                } else {
                    $child_text = $_dom->createTextNode($value);
                    $_parent->appendChild($child_text);
                }
            } else {
                $parent = $_dom->createElement($key);
                
                if (empty($_parent)) {
                    $parent = $_dom->appendChild($parent);
                } else {
                    $parent = $_parent->appendChild($parent);
                }
                
                if (is_array($value)) {
                    $this->make_xml_dom($_dom, $value, $parent);
                } else {
                    $child_text = $_dom->createTextNode($value);
                    $parent->appendChild($child_text);
                }
            }
        }
    }
    
    /**
     * This method is mainly to squash away the <element/> tags produced by the
     * make_xml_dom() method. 
     * @param data  A formatted XML string.
     * @return XML  Returns an sterilized XML string, or empty string.
     * NOTE:
     * - This is a non-destructive method. All formattings remain intact. 
     */
    private function sterilize($data) {
        $lines = explode("\n", $data);
        $output = "";
        
        // Look for '<element/>' tags and remove them. 
        foreach ($lines as $row => $line) {
            $tmp = trim($line);
            if (count(explode(" ", $tmp)) == 1 && substr($tmp, -2) == "/>") $line = "";
            if (strlen($line) != 0) $output .= $line. "\n";
        }
        
        return $output;
    }
    
    /**
     * This method outputs the XML string from the associative array. 
     * @param data      The data stored in associative array.
     * @return mixed    Returns formatted XML string or FALSE if an error occured.
     * NOTES:
     * - There is no Namespace support. 
     * - To associate attributes to an element, use the '_attrs' or '_ATTRS' key. 
     */
    public function get_xml_from_array($data) {
        $this->xml_doc = new DOMDocument('1.0', 'UTF-8');
        $this->xml_doc->formatOutput = true;
        
        if (is_a($this->xml_doc, "DOMDocument") && count($data) == 1 && is_array($data)) {
            $this->make_xml_dom($this->xml_doc, $data);
            $this->xml_doc->normalizeDocument();
            return $this->sterilize($this->xml_doc->saveXML());
        }
        
        return false;
    }
}
?>
