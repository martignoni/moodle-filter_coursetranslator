(function ($) {

  /**
   * Get Selected Text in Textarea
   * @returns string
   */
  const getSelected = () => {
    var t = "";
    if (window.getSelection) {
      t = window.getSelection();
    } else if (document.getSelection) {
      t = document.getSelection();
    } else if (document.selection) {
      t = document.selection.createRange().text;
    }
    return t.toString();
  };

  /**
   * Get the Translation using Moodle Web Service
   * @returns json
   */
  $(".translatable-editor").on("focusout", (e) => {
    // get params needed
    let id = $(e.currentTarget).data("id");
    let course_id = $(".translatable-content").data("course-id");
    let wstoken = $('.translatable-content').data('wstoken')
    let translation = $(e.currentTarget).html();

    // build the data
    let data = {
      wstoken: wstoken,
      wsfunction: 'filter_translatable_update_translation',
      translation: [
        {
          id: id,
          course_id: course_id,
          translation: translation
        }
      ],
      moodlewsrestformat: 'json'
    }

    // remove success message on click
    $(".translatable-editor").on("click", (e) => {
      $(e.currentTarget).removeClass('filter-translatable__success')
      $(e.currentTarget).removeClass('filter-translatable__error')
    })

    // success message
    const successMessage = () => {
      $(e.currentTarget).addClass('filter-translatable__success')
      $(e.currentTarget).after('<div class="filter-translatable__success-message" data-id="' + id + '">Autosaved</div>')
      $('.filter-translatable__success-message[data-id="' + id + '"]')
        .delay(2000)
        .fadeOut()
    }

    // error message
    const errorMessage = () => {
      $(e.currentTarget).addClass('filter-translatable__error')
    }

    // update the translation
    $.ajax({
      method: "POST",
      url: "https://m3.moodle.local/webservice/rest/server.php",
      data: data
    })
      .done(data => {
        if (data.length > 0) {
          successMessage()
        } else {
          errorMessage()
        }
        console.log(data)
      })
      .fail(error => {
        errorMessage()
        $(e.currentTarget).addClass('filter-translatable__error')
        console.log(error)
      })
  });

  /**
   * Add Editor to .translatable editor
   */
  $(".translatable-editor.format-html").each((i, e) => {
    let id = $(e).data("id");

    let editor =
      '<div class="filter-translatable__editor-tools" data-id="' + id + '">' +
      '<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">' +
      '<div class="btn-group mr-2" role="group" aria-label="Formatting">' +
      '<button data-method="h2" type="button" class="t-editor-button btn btn-light"><i class="bi-type-h2"></i></button>' +
      '<button data-method="h3" type="button" class="t-editor-button btn btn-light"><i class="bi-type-h3"></i></button>' +
      '<button data-method="p" type="button" class="t-editor-button btn btn-light"><i class="bi-paragraph"></i></button>' +
      "</div>" +
      '<div class="btn-group mr-2" role="group" aria-label="Lists">' +
      '<button data-method="ol" type="button" class="t-editor-button btn btn-light"><i class="bi-list-ol"></i></button>' +
      '<button data-method="ul" type="button" class="t-editor-button btn btn-light"><i class="bi-list-ul"></i></button>' +
      "</div>" +
      '<div class="btn-group mr-2" role="group" aria-label="Basic example">' +
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

    $(e).parent().prepend(editor);
  });

  /**
   * Switch Translation Language
   */
  $(".translatable-locale-switcher").on("change", (e) => {
    let url = new URL(window.location.href);
    let search_params = url.searchParams;
    search_params.set("course_lang", e.target.value);
    let new_url = url.toString();

    window.location = new_url;
  });

  /**
   * Detect Editor Button Click
   */
  $(".t-editor-button").on("click", (e) => {
    let id = $(e.currentTarget)
      .closest(".filter-translatable__editor-tools")
      .data("id");
    let method = $(e.currentTarget).data("method");
    let editor = $('.translatable-editor[data-id="' + id + '"] .no-overflow');
    let selected = getSelected();
    let html = $(editor).html();

    let output = "";

    switch (method) {
      case "h2":
				document.execCommand('formatBlock', false, '<h2>');
				break;
			case "h3":
				document.execCommand('formatBlock', false, '<h3>');
				break;
			case "p":
				document.execCommand('formatBlock', false, '<p>');
				break;
			case "ol":
				document.execCommand('insertOrderedList');
				break;
			case "ul":
				document.execCommand('insertUnorderedList');
				break;
			case "b":
				document.execCommand('bold');
				break;
			case "i":
				document.execCommand('italic');
				break;
      case "u":
				document.execCommand('underline');
				break;
			case "l":
				var link = prompt('Enter a URL:', 'https://');
				document.execCommand('createLink', false, link)
				break;
			// case "html":
			// 	$('div.translatable-editor[data-id="' + id + '"]').toggle()
			// 	$('.translatable-editor-textarea[data-id="' + id + '"]').toggle()
			// 	break;
			default:
				output = html
				break;
    }

    $(editor).html(output);
  });
})(jQuery);
