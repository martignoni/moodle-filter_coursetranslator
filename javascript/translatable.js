(function($) {

    $('.translatable-editor').on('focusout', (e) => {
        let id = $(e.currentTarget).data('id')
        let course_id = $('.translatable-content').data('course-id')
        let content = $(e.currentTarget).html()
        console.log('save the content', course_id, id, content)
    })

})(jQuery);