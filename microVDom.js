// microVDom - JavaScript Version
var microDocument = (function () {
	// Node Types
	const ELEMENT_NODE = 1;
	const TEXT_NODE = 3;
	const COMMENT_NODE = 8;
	const DOCUMENT_NODE = 9;
	const DOCUMENT_TYPE_NODE = 10;
	const SELF_CLOSING_TAGS = [
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
	function compose(parameter, ...functions) {
		for (var curFunc of functions) {
			parameter = curFunc(parameter);
		}
		return parameter;
	}
	function htmlSpecialChars(string) {
		return compose(string,
			(s) => s.replace(/&/g, "&amp;"),
			(s) => s.replace(/"/g, "&quot;"),
			(s) => s.replace(/'/g, "&apos;"),
			(s) => s.replace(/</g, "&lt;"),
			(s) => s.replace(/>/g, "&gt;")
		);
	}
	// The DocumentType interface represents a Node containing a doctype.
	function documentType(type = "html") {
		this.nodeType = DOCUMENT_TYPE_NODE;
		this.docType = type;
	}
	documentType.prototype.render = function () {
		return "<!DOCTYPE " + this.docType.toUpperCase() + ">\n";
	};
	// The Text interface represents the textual content of Elements
	function text(string = "") {
		this.nodeType = TEXT_NODE;
		this.data = string;
	}
	text.prototype.render = function () {
		return htmlSpecialChars(this.data);
	};
	function htmlBlob(tagString) {
		this.data = tagString;
	}
	htmlBlob.prototype.render = function () {
		return this.data;
	};
	function comment(string) {
		this.nodeType = COMMENT_NODE;
		this.data = string;
	}
	comment.prototype.render = function () {
		return "<!--" + htmlSpecialChars(this.data) + "-->\n";
	};
	// This type represents a DOM element's attribute as an object.
	function attr(name, value) {
		this.name = name;
		this.value = value;
	}
	attr.prototype.render = function () {
		return " " + this.name + "=\"" + this.value + "\"";
	};
	function parentNode() {
		// Contains all Elements that are children of this ParentNode.
		this.children = [];
		this.childElementCount = 0;
	}
	parentNode.prototype.appendOneChild = function (node) {
		if ((typeof node) === "string") {
			this.children.push(new text(node));
		} else if ("nodeType" in node) {
			this.children.push(node);
		}
	};
	parentNode.prototype.prependOneChild = function (node) {
		if ((typeof node) === "string") {
			this.children.unshift(new text(node));
		} else if ("nodeType" in node) {
			this.children.unshift(node);
		}
	};
	parentNode.prototype.addChildren = function (nodes, append = true) {
		var nodesToAdd = (Array.isArray(nodes) ? nodes : [nodes]);
		for (var node of nodesToAdd) {
			this[(append ? "append" : "prepend") + "OneChild"](node);
			this.childElementCount++;
		}
	};
	parentNode.prototype.appendChild = function (nodes) {
		this.addChildren(nodes, true);
	};
	parentNode.prototype.prependChild = function (nodes) {
		this.addChildren(nodes, false);
	};
	function element(tagName, attrMap = null) {
		parentNode.call(this);
		this.nodeType = ELEMENT_NODE;
		this.attributes = [];
		this.tagName = tagName;
		this.selfClosing = SELF_CLOSING_TAGS.indexOf(tagName) !== -1;
		if (attrMap !== null && Array.isArray(attrMap)) {
			attrMap.forEach((value, name) => this.setAttribute(value, name));
		}
	}
	element.prototype = Object.create(parentNode.prototype);
	element.prototype.setAttribute = function (attribute, value = "") {
		if (attribute instanceof attr) {
			this.attributes[attribute.name] = attribute;
		} else if ((typeof attribute) === "string") {
			this.attributes[attribute.name] = new attr(attribute, value);
		}
	};
	element.prototype.render = function () {
		var openTag = "<" + this.tagName;
		if (this.attributes.length > 0) {
			for (var attr of this.attributes) {
				openTag += attr.render();
			}
		}
		openTag += ">\n";
		if (this.selfClosing) {
			return openTag;
		}
		var contents = "";
		if (this.children.length > 0) {
			for (var childNode of this.children) {
				contents += childNode.render();
			}
		}
		var closeTag = "</" + this.tagName + ">\n";
		return openTag + contents + closeTag;
	};
	function microDocument(docType = "html") {
		parentNode.call(this);
		this.characterSet = "UTF-8";
		this.docType = new documentType(docType);
		this.nodeType = DOCUMENT_NODE;
		this.documentElement = this.createElement("html");
		this.head = this.createElement("head");
		this.body = this.createElement("body");
		this.documentElement.appendChild([this.head, this.body]);
		this.appendChild([this.docType, this.documentElement]);
	}
	microDocument.prototype = Object.create(parentNode.prototype);
	microDocument.prototype.createAttribute = function (name, value) {
		return new attr(name, value);
	};
	microDocument.prototype.createComment = function (string = "") {
		return new comment(string);
	};
	microDocument.prototype.createElement = function (tagName, attrMap = null) {
		return new element(tagName, attrMap);
	};
	microDocument.prototype.createTextNode = function (string = "") {
		return new text(string);
	};
	microDocument.prototype.createHtmlBlob = function (tagString) {
		return new htmlBlob(tagString);
	};
	microDocument.prototype.render = function () {
		var html = "";
		for (var childNode of this.children) {
			html += childNode.render();
		}
		return html;
	};
	return microDocument;
})();
// NodeJS `require` compatibility
if (typeof module !== "undefined") {
	module.exports = microDocument;
}