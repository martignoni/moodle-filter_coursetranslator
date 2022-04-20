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
 * @module     filter_coursetranslator/coursetranslator
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
  str.get_string("t_autosaved", "filter_coursetranslator").done((string) => {
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
   * Toggle Autotranslate Button
   */
  const toggleAutotranslateButton = () => {
    let checkboxItems = [];
    checkboxes.forEach((e) => {
      checkboxItems.push(e.checked);
    });
    let checked = checkboxItems.find((checked) => checked === true)
      ? true
      : false;
    window.console.log(checked);
    if (config.autotranslate && checked) {
      autotranslateButton.disabled = false;
    } else {
      autotranslateButton.disabled = true;
    }
  };

  /**
   * Select All Checkbox
   */
  const selectAll = document.querySelector(".filter-coursetranslator_select-all");
  if (config.autotranslate) {
    selectAll.disabled = false;
  }
  selectAll.addEventListener("click", (e) => {
    // See if select all is checked
    let checked = e.target.checked;
    let checkboxes = document.querySelectorAll(".filter-coursetranslator_select");

    // Check/uncheck checkboxes
    if (checked) {
      checkboxes.forEach((e) => {
        e.checked = true;
      });
    } else {
      checkboxes.forEach((e) => {
        e.checked = false;
      });
    }
    toggleAutotranslateButton();
  });

  /**
   * Autotranslate Checkboxes
   */
  const checkboxes = document.querySelectorAll(".filter-coursetranslator_select");
  if (config.autotranslate) {
    checkboxes.forEach((e) => {
      e.disabled = false;
    });
  }
  checkboxes.forEach((e) => {
    e.addEventListener("change", () => {
      toggleAutotranslateButton();
    });
  });

  /**
   * Autotranslate Button Display
   * @returns void
   */
  const autotranslateButton = document.querySelector(
    ".coursetranslator-autotranslate"
  );

  /**
   * Autotranslate Button Click
   * @returns void
   */
     autotranslateButton.addEventListener("click", () => {
      document
        .querySelectorAll(".filter-coursetranslator_select:checked")
        .forEach((e) => {
          let id = e.getAttribute("data-id");
          getTranslation(id);
        });
    });

  /**
   * Send for Translation to DeepL
   * @param {Integer} id Translation ID
   */
  const getTranslation = (id) => {
    // Get the editor
    let editor = document.querySelector(
      '.coursetranslator-editor[data-id="' + id + '"]'
    );

    // Get the source text
    let sourceText = document.querySelector(
      '.filter-coursetranslator__source-text[data-id="' + id + '"]'
    ).innerHTML;

    // Build formData
    let formData = new FormData();
    formData.append("text", sourceText);
    formData.append("source_lang", "en");
    formData.append("target_lang", config.lang);
    formData.append("preserve_formatting", 1);
    formData.append("auth_key", config.apikey);
    formData.append("tag_handling", "xml");
    formData.append("split_sentences", "nonewlines");

    // DeepL URL
    let url = "https://api.deepl.com/v2/translate";

    // Update the translation
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = () => {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        var status = xhr.status;
        if (status === 0 || (status >= 200 && status < 400)) {
          // The request has been completed successfully
          let data = JSON.parse(xhr.responseText);
          window.console.log("deepl:", id, data);
          // Display translation
          editor.innerHTML = data.translations[0].text;
          // Save translation
          saveTranslation(id, data.translations[0].text, editor);
        } else {
          // Oh no! There has been an error with the request!
          window.console.log("error", status);
        }
      }
    };
    xhr.open("POST", url);
    xhr.send(formData);
  };

  /**
   * Save Translation to Moodle
   * @param  {Integer} id Translation ID
   * @param  {String} translation Translation Text
   * @param  {Node} editor HTML Editor Node
   */
  const saveTranslation = (id, translation, editor) => {

    // Success Message
    const successMessage = () => {
      editor.classList.add("filter-coursetranslator__success");
      // Add saved indicator
      let indicator =
        '<div class="filter-coursetranslator__success-message" data-id="' +
        id +
        '">' +
        autsavedMsg +
        "</div>";
      editor.after(...stringToHTML(indicator));
      // Remove success message after a few seconds
      setTimeout(() => {
        let indicatorNode = document.querySelector(
          '.filter-coursetranslator__success-message[data-id="' + id + '"]'
        );
        editor.parentNode.removeChild(indicatorNode);
      }, 3000);
    };

    // Error Mesage
    const errorMessage = () => {
      editor.classList.add("filter-coursetranslator__error");
    };

    // Submit the request
    ajax.call([
      {
        methodname: "filter_coursetranslator_update_translation",
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
          window.console.log("ws: ", id, data);
          if (data.length > 0) {
            successMessage();
          } else {
            errorMessage();
          }
        },
        fail: (error) => {
          window.console.log("error: ", error);
          errorMessage();
          editor.classList.addClass("filter-coursetranslator__error");
          window.console.log(error);
        },
      },
    ]);
  };

  /**
   * Get the Translation using Moodle Web Service
   * @returns void
   */
  document.querySelectorAll('.coursetranslator-editor [contenteditable="true"]').forEach((editor) => {

    // Save translation
    editor.addEventListener("focusout", () => {
      let translation = editor.innerHTML;
      let id = editor.closest('.coursetranslator-editor').getAttribute('data-id');
      window.console.log(id, translation);

      saveTranslation(id, translation, editor);
    });

    // Remove status classes
    editor.addEventListener("click", () => {
      editor.classList.remove("filter-coursetranslator__success");
      editor.classList.remove("filter-coursetranslator__error");
    });
  });

};
