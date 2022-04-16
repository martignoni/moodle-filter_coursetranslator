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
 * @module     filter_translatable/translatable
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import libs
import ajax from "core/ajax";
import * as str from "core/str";

/**
 * Translation Editor UI
 * @param {Object} config JS Config
 */
export const init = (config) => {

  // Autosave Translation String
  let autsavedMsg = "";
  str.get_string('t_autosaved', 'filter_translatable')
    .done(string => {
      autsavedMsg = string;
    });

  /**
   * Convert a template string into HTML DOM nodes
   * @param  {String} string The template string
   * @return {Node}       The template HTML
   */
  const stringToHTML = (string) => {
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
   * Autotranslate Button Display
   * @returns void
   */
  const autotranslateButton = document.querySelector(
    ".translatable-autotranslate"
  );
  if (config.autotranslate) {
    autotranslateButton.classList.remove("d-none");
  } else {
    autotranslateButton.parentNode.removeChild(autotranslateButton);
  }

  /**
   * Autotranslate Button Click
   * @returns void
   */
  autotranslateButton.addEventListener("click", () => {
    document.querySelectorAll(".translatable-editor").forEach((editor) => {
      let id = editor.getAttribute("data-id");
      let text = editor.innerHTML;
      let originalText = document.querySelector(
        '.filter-translatable__source-text[data-id="' + id + '"]'
      ).innerHTML;

      if (text === originalText) {
        // Build the parms
        let params = {
          text: text,
          source_lang: "en", // eslint-disable-line
          target_lang: config.lang, // eslint-disable-line
          preserve_formatting: 1, // eslint-disable-line
          auth_key: config.apikey, // eslint-disable-line
          tag_handling: "xml", // eslint-disable-line
          split_sentences: "nonewlines", // eslint-disable-line
        };

        let queryString = Object.keys(params)
          .map((key) => key + "=" + params[key])
          .join("&");

        // Update the translation
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = () => {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            var status = xhr.status;
            if (status === 0 || (status >= 200 && status < 400)) {
              // The request has been completed successfully
              let data = JSON.parse(xhr.responseText);
              // Display translation
              editor.innerHTML = data.translations[0].text;
              // Save translation
              saveTranslation(id, data.translations[0].text, editor);
            } else {
              // Oh no! There has been an error with the request!
              window.console.log("error", status);
            }
          }
          // Eeditor.innerHTML = data.translations[0].text;
        };
        xhr.open("POST", "https://api.deepl.com/v2/translate");
        xhr.setRequestHeader(
          "Content-Type",
          "application/x-www-form-urlencoded"
        );
        xhr.send(queryString);
      }
    });
  });

  /**
   * Save Translation to Moodle
   * @param  {Integer} id Translation ID
   * @param  {String} translation Translation Text
   * @param  {Node} editor HTML Editor Node
   */
  const saveTranslation = (id, translation, editor) => {
    // Success Message
    const successMessage = () => {
      editor.classList.add("filter-translatable__success");
      // Add saved indicator
      let indicator =
        '<div class="filter-translatable__success-message" data-id="' +
        id +
        '">' + autsavedMsg + '</div>';
      editor.after(...stringToHTML(indicator));
      // Remove success message after a few seconds
      setTimeout(() => {
        let indicatorNode = document.querySelector(
          '.filter-translatable__success-message[data-id="' + id + '"]'
        );
        editor.parentNode.removeChild(indicatorNode);
      }, 3000);
    };

    // Error Mesage
    const errorMessage = () => {
      editor.classList.add("filter-translatable__error");
    };

    // Submit the request
    ajax.call([
      {
        methodname: "filter_translatable_update_translation",
        args: {
          translation: [
            {
              id: id,
              course_id: config.course_id, // eslint-disable-line
              translation: translation,
            },
          ],
        },
        done: (data) => {
          window.console.log("data: ", data);
          if (data.length > 0) {
            successMessage();
          } else {
            errorMessage();
          }
        },
        fail: (error) => {
          window.console.log("error: ", error);
          errorMessage();
          editor.classList.addClass("filter-translatable__error");
          window.console.log(error);
        },
      },
    ]);
  };

  /**
   * Get the Translation using Moodle Web Service
   * @returns void
   */
  document.querySelectorAll(".translatable-editor").forEach((editor) => {
    // Save translation
    editor.addEventListener("focusout", () => {
      let id = editor.getAttribute("data-id");
      let translation = editor.innerHTML;

      saveTranslation(id, translation, editor);
    });

    // Remove status classes
    editor.addEventListener("click", () => {
      editor.classList.remove("filter-translatable__success");
      editor.classList.remove("filter-translatable__error");
    });
  });

  /**
   * Add Editor to .translatable editor
   */
  document
    .querySelectorAll(".translatable-editor.format-html")
    .forEach((editor) => {
      let id = editor.getAttribute("data-id");

      let controls =
        '<div class="filter-translatable__editor-tools" data-id="' +
        id +
        '">' +
        '<div class="btn-toolbar" role="toolbar" aria-label="Editor Toolbar">' +
        '<div class="btn-group mr-2" role="group" aria-label="Formatting">' +
        '<button data-method="h2" type="button" class="t-editor-button btn btn-light"><i class="bi-type-h2"></i></button>' +
        '<button data-method="h3" type="button" class="t-editor-button btn btn-light"><i class="bi-type-h3"></i></button>' +
        '<button data-method="p" type="button" class="t-editor-button btn btn-light"><i class="bi-paragraph"></i></button>' +
        "</div>" +
        '<div class="btn-group mr-2" role="group" aria-label="Lists">' +
        '<button data-method="ol" type="button" class="t-editor-button btn btn-light"><i class="bi-list-ol"></i></button>' +
        '<button data-method="ul" type="button" class="t-editor-button btn btn-light"><i class="bi-list-ul"></i></button>' +
        "</div>" +
        '<div class="btn-group mr-2" role="group" aria-label="Styles">' +
        '<button data-method="b" type="button" class="t-editor-button btn btn-light"><i class="bi-type-bold"></i></button>' +
        '<button data-method="i" type="button" class="t-editor-button btn btn-light"><i class="bi-type-italic"></i></button>' +
        '<button data-method="u" type="button" class="t-editor-button btn btn-light"><i class="bi-type-underline"></i></button>' +
        "</div>" +
        '<div class="btn-group mr-2" role="group" aria-label="Links">' +
        '<button data-method="l" type="button" class="t-editor-button btn btn-light"><i class="bi-link-45deg"></i></button>' +
        "</div>" +
        // '<div class="btn-group mr-2" role="group" aria-label="Links">' +
        // '<button data-method="html" type="button" class="t-editor-button btn btn-light"><i class="bi-code-slash"></i></button>' +
        // "</div>" +
        "</div>" +
        "</div>";

      editor.parentNode.prepend(...stringToHTML(controls));
    });

  /**
   * Switch Translation Language
   */
  let localeSwitcher = document.querySelector(".translatable-locale-switcher");
  localeSwitcher.addEventListener("change", (e) => {
    let url = new URL(window.location.href);
    let searchParams = url.searchParams;
    searchParams.set("course_lang", e.target.value);
    let newUrl = url.toString();

    window.location = newUrl;
  });

  /**
   * Detect Editor Button Click
   */
  document.querySelectorAll(".t-editor-button").forEach((button) => {
    button.addEventListener("click", () => {
      // @todo let id = button.closest('.filter-translatable__editor-tools').getAttribute('data-id');
      let method = button.getAttribute("data-method");
      // @todo let editor = document.querySelector('.translatable-editor[data-id="' + id + '"]');
      // @todo let html = editor.innerHTML;

      switch (method) {
        case "h2":
          document.execCommand("formatBlock", false, "<h2>");
          break;
        case "h3":
          document.execCommand("formatBlock", false, "<h3>");
          break;
        case "p":
          document.execCommand("formatBlock", false, "<p>");
          break;
        case "ol":
          document.execCommand("insertOrderedList");
          break;
        case "ul":
          document.execCommand("insertUnorderedList");
          break;
        case "b":
          document.execCommand("bold");
          break;
        case "i":
          document.execCommand("italic");
          break;
        case "u":
          document.execCommand("underline");
          break;
        case "l":
          var link = prompt("Enter a URL:", "https://");
          document.execCommand("createLink", false, link);
          break;
        default:
          break;
      }
    });
  });
};
