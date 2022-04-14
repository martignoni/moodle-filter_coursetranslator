(function($) {

    // save the translation using a web service
    $('.translatable-editor').on('focusout', (e) => {
        let id = $(e.currentTarget).data('id')
        let course_id = $('.translatable-content').data('course-id')
        let content = $(e.currentTarget).html()
        console.log('save the content', course_id, id, content)
    })

    // navigation to a new language
    $('.translatable-locale-switcher').on('change', (e) => {
        let url = new URL(window.location.href)
        let search_params = url.searchParams
        search_params.set('lang', e.target.value)
        let new_url = url.toString()

        window.location = new_url
    })

})(jQuery);