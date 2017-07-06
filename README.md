# microVDom

**microVDom** is a tiny (under 300 lines) PHP Virtual DOM solution for minimalist creation and rendering of HTML.

Only the bare minimum of DOM methods are included to quickly write a presentation layer, completely eschewing parsing and query methods. Only a small subset of command methods are supported.

Due to the lack of mutating command methods, the `microDocument` is effectively WORM (Write Once, Read Many).

___

# Usage

Here is a small usage example:
```PHP
<?php
  // Include the microVDom file:
  include("microVDom.php");

  // Create a new empty microDocument:
  $document = new microDocument("html");

  // Create a new div with a class attribute:
  $myDiv = $document->createElement("div", ["class" => "myClass"]);

  // Add a text node to the div:
  $myDiv->appendChild("Hello World!");

  // Append the document body with the div:
  $document->body->appendChild($myDiv);

  // Render the HTML:
  $myHTML = $document->render();
?>
```
When the code runs, `$myHTML` will contain the following HTML string:
```HTML
<!DOCTYPE html>
<html>
  <head>
  </head>
  <body>
    <div class="myClass">Hello World&#33;</div>
  </body>
</html>
```

# Documentation

## `microDocument`

###### Class

```JavaScript
new microDocument( string $docType );
```

The primary controller/container class.


### Instantiating
Returns a new instance of the `microDocument` class. The new object has two children, the `DOCTYPE` declaration and `<html>`; which then has two children as well, `<head>` and `<body>`.
```PHP
$document = new microDocument("html");
```

##### Constructor Parameters
- String **`docType`**

  An arbitrary string used in the `<!DOCTYPE ...>` declaration.

### Member Variables
- String **`docType`**

  An arbitrary string used in the `<!DOCTYPE ...>` declaration.

- Element **`documentElement`**

  The direct child of the `microDocument`. Default is an `<html>` element.

- Element **`body`**

  The `<body>` element of the `<html>` element.

- Element **`head`**

  The `<head>` element of the `<html>` element.

### Member Methods
#### `createAttribute`

###### Function
```PHP
microDocument->createAttribute( string $name , string $value );
```
Returns a clone of `object`.

##### Parameters
- String **`name`**

  The name of the new attribute.

- String **`value`**

  The value of the new attribute.

##### Example
```PHP
// Instantiate a new Attr Object
$myAttr = $document->createAttribute("myAttr", "myValue");

// Instantiate a new Element Object
$myDiv = $document->createElement("div");

// Set the new attribute on the Element Object
$myDiv->setAttribute($myAttr);

// Render the HTML
$myHTML = $myDiv->render();
```

`$myHTML` now contains an HTML string:
```HTML
<div myAttr="myValue"></div>
```

# WIP
