// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @module     filter_multilingual/stringeditor
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Parse HTMl Nodes
 *
 * Parse HTML nodes into a flat object
 * @param {nodes} nodes HTML Nodes
 * @returns {object}
 */
export const parseNodes = (nodes) => {
  let processedNodes = [];

  nodes.forEach((node) => {
    if (node.childElementCount === 0) {
      processedNodes.push(parseNode(node));
    } else {
      node.childNodes.forEach((childNode) => {
        processedNodes.push(processNode(childNode));
      });
    }
  });

  return processedNodes;
};

/**
 * HTML String Editor
 * @param {object} nodes Nodes from parseNodes
 * @returns {object}
 */
export const outputEditor = (nodes) => {
  let editor = document.createElement("form");
  editor.classList.add("container-fluid");

  let specialProcessing = ["iframe", "img"];

  nodes.forEach((node) => {
    let input = "";
    let inputClasses = ["multilingual-editor", "col-10"];

    // Create input element
    input = document.createElement("div");
    input.classList.add(...inputClasses);
    input.contentEditable = true;

    // Input Processing
    if (!specialProcessing.includes(node.localName)) {
      input.innerHTML = node.node.innerHTML;
    } else {
      input.innerHTML = node.node.attributes.src;
    }

    // Indicator
    let localName = document.createElement("div");
    let localNameClasses = [
      "col-2",
      "text-right",
      "bg-light",
      "align-middle",
      "small",
      "pt-2"
    ];
    localName.classList.add(...localNameClasses);
    localName.innerHTML = node.localName;

    // Add input to paragraph element
    let div = document.createElement("div");
    let divClasses = ["mb-2", "row"];
    if (node.node.innerHTML.length === 0 && !specialProcessing.includes(node.localName)) {
      divClasses.push('d-none');
    }
    div.classList.add(...divClasses);
    div.append(input);
    div.append(localName);

    editor.append(div);
  });

  return editor;
};

/**
 * Convert a template string into HTML DOM nodes
 * @param  {String} string The template string
 * @return {Node}       The template HTML
 */
export const stringToHTML = (string) => {
  string = string.trim().replace(/(\r\n|\n|\r)/gm, "");

  // See if DOMParser is supported
  var support = (() => {
    if (!window.DOMParser) {
      return false;
    }
    var parser = new DOMParser();
    try {
      parser.parseFromString("x", "text/html");
    } catch (err) {
      return false;
    }
    return true;
  })();

  // If DOMParser is supported, use it
  if (support) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(string, "text/html");
    return doc.body.childNodes;
  }

  // Otherwise, fallback to old-school method
  var dom = document.createElement("div");
  dom.innerHTML = string;
  return dom;
};

/**
 * Parse HTML Node
 * @param {Node} node HTML Node
 * @returns {Ojbect}
 */
const parseNode = (node) => {
  let parsedNode = {};
  parsedNode.localName = node.localName;
  parsedNode.key = keyGenerator(node.localName, node);
  parsedNode.node = node;

  return parsedNode;
};

/**
 * Process Node
 *
 * Recursively loop to get last child node
 * @param {Node} node HTML Node
 * @returns {Node}
 */
const processNode = (node) => {
  if (node.childElementCount !== 0) {
    node.childNodes.forEach((childNode) => {
      return processNode(childNode);
    });
  }
  return parseNode(node);
};

/**
 * Key Generator
 * @param {String} localName Local Name of Node
 * @param {Node} node HTML Node
 * @returns {String}
 */
const keyGenerator = (localName, node) => {
  if (node.parentNode.localName !== "body") {
    let parentLocalName = node.parentNode.localName + "[" + localName + "]";
    return keyGenerator(parentLocalName, node.parentNode);
  }
  return localName;
};
