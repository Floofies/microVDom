<?php
// Node Types
define("ELEMENT_NODE", 1);
define("TEXT_NODE", 3);
define("COMMENT_NODE", 8);
define("DOCUMENT_NODE", 9);
define("DOCUMENT_TYPE_NODE", 10);

interface node {
  public function render ();
}

// The DocumentType interface represents a Node containing a doctype.
class documentType implements node {
  // The node type of the element
  public $nodeType;
  private $docType = "";
  function __construct ($type) {
    $this->nodeType = DOCUMENT_TYPE_NODE;
    $this->docType = $type;
  }
  public function render () {
    return "<!DOCTYPE " . strtoupper($this->docType) . ">\n";
  }
}

// The Text interface represents the textual content of Elements
class text implements node {
  // The node type of the element
  public $nodeType;
  public $data;
  function __construct ($string = "") {
    $this->nodeType = TEXT_NODE;
    $this->data = (string) $string;
  }
  public function render () {
    return htmlspecialchars($this->data);
  }
}

class htmlBlob implements node {
  public $data;
  function __construct ($html) {
    $this->data = (string) $html;
  }
  public function render () {
    return $this->data;
  }
}

class comment implements node {
  public $nodeType;
  public $data;
  function __construct ($string = "") {
    $this->nodeType = COMMENT_NODE;
  }
  public function render () {
    return "<!--" . htmlspecialchars($this->data) . "-->\n";
  }
}

// This type represents a DOM element's attribute as an object.
class attr {
  // The attribute's name.
  public $name;
  // The attribute's value.
  public $value;
  function __construct ($name, $value) {
    $this->name = $name;
    $this->value = $value;
  }
  public function render () {
    return (" " . $this->name . "=\"" . $this->value . "\"");
  }
}

class parentNode {
  // A live HTMLCollection containing all objects of type Element that are children of this ParentNode.
  public $children = [];
  // An unsigned long giving the amount of children that the object has.
  public $childElementCount = 0;
  function __construct () {
    $this->children = [];
  }
  private function appendOneChild ($node) {
    if (is_string($node)) {
      $this->children[] = new text($node);
    } else if (is_subclass_of($node, node)) {
      $this->children[] = $node;
    }
  }
  private function prependOneChild ($node) {
    if (is_string($node)) {
      array_unshift($this->children, new text($node));
    } else if (is_subclass_of($node, node)) {
      array_unshift($this->children, $node);
    }
  }
  private function addChildren ($nodes) {
    if (!is_array($nodes)) {
      $nodesToAdd = [$nodes];
    }
    foreach ($nodesToAdd as $node) {
      $this->{($append ? "append" : "prepend") . "OneChild"}($node);
      $this->childElementCount++;
    }
  }
  // Inserts a Node or an array of Nodes after the last child of the ParentNode.
  public function appendChild ($nodes) {
    $this->addChildren($nodes, true);
  }
  // Inserts a set of Node objects or DOMString objects before the first child of the ParentNode.
  public function prependChild ($nodes) {
    $this->addChildren($nodes, false);
  }
}

class element extends parentNode implements node {
  // An array containing the assigned attributes of the corresponding HTML element.
  public $attributes = [];
  // An array containing the list of class attributes.
  // A String with the name of the tag for the given element.
  public $tagName;
  // A Boolean indicating if the tag is self-closing (can not have children if true).
  public $selfClosing;
  // The node type of the element
  public $nodeType;
  function __construct ($tagName, $attrMap = []) {
    $selfClosingTagNames = [
      "area",
      "base",
      "br",
      "col",
      "command",
      "embed",
      "hr",
      "img",
      "input",
      "keygen",
      "link",
      "meta",
      "param",
      "source",
      "track",
      "wbr"
    ];
    if (in_array($tagName, $selfClosingTagNames)) {
      $this->selfClosing = true;
    } else {
      $this->selfClosing = false;
    }
    $this->tagName = $tagName;
    $this->nodeType = ELEMENT_NODE;
    if (count($attrMap) > 0) {
      foreach ($attrMap as $attrName => $attrValue) {
        $this->setAttribute($attrName, $attrValue);
      }
    }
  }
  // Sets the value of a named attribute of the current node.
  public function setAttribute ($nameOrAttr, $value = "") {
    if (is_a($nameOrAttr, attr)) {
      $name = $nameOrAttr->name;
      $this->attributes[$name] = $nameOrAttr;
    } else if (is_string($nameOrAttr)) {
      $name = $nameOrAttr;
      $this->attributes[$name] = new attr($name, $value);
    }
  }
  public function render () {
    $openTag = "<" . $this->tagName;
    if (count($this->attributes) > 0) {
      foreach ($this->attributes as $attr) {
        $openTag .= $attr->render();
      }
    }
    $openTag .= ">\n";
    if ($this->selfClosing) {
      return $openTag;
    } else {
      $contents = "";
      if (count($this->children) > 0) {
        foreach ($this->children as $childNode) {
          $contents .= $childNode->render();
        }
      }
      $closeTag = "</" . $this->tagName . ">\n";
      return $openTag . $contents . $closeTag;
    }
  }
}

class microDocument extends parentNode implements node {
  // The character set being used by the document.
  public $characterSet = "UTF-8";
  // The Document Type Definition (DTD) of the document.
  public $docType;
  // The node type of the document
  public $nodeType;
  // The Element that is a direct child of the document. For HTML documents, this is normally the <html> element.
  public $documentElement;
  // The document location as a string.
  public $documentURI;
  // The <body> element of the document.
  public $body;
  // The <form> elements within the ocument.
  public $forms;
  // The <head> element of the document.
  public $head;
  function __construct ($docType) {
    $this->nodeType = DOCUMENT_NODE;
    $this->docType = new documentType($docType);
    $this->documentElement = $this->createElement("html");
    $this->head = $this->createElement("head");
    $this->body = $this->createElement("body");
    $this->documentElement->children = [$this->head, $this->body];
    $this->children = [$this->docType, $this->documentElement];
  }
  // Creates a new Attr object and returns it.
  public function createAttribute ($name, $value) {
    return new attr($name, $value);
  }
  // Creates a new comment node and returns it.
  public function createComment($string) {
    return new comment($string);
  }
  // Creates a new element with the given tag name.
  public function createElement ($tagName, $attrMap = []) {
    return new element($tagName, $attrMap);
  }
  // Creates a text node.
  public function createTextNode ($string = "") {
    return new text($string);
  }
  public function createHtmlBlob ($html) {
    return new htmlBlob($html);
  }
  public function render () {
    $html = "";
    foreach ($this->children as $childNode) {
      $html .= $childNode->render();
    }
    return $html;
  }
}
?>
