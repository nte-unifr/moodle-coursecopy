(function($){

    var bodyclasses = $('body').attr('class').split(' ');
    for(var i=0; i < bodyclasses.length; i++) {
        if (bodyclasses[i].indexOf('course-') == 0) {
            var courseid = bodyclasses[i].replace(/^course-/, '');
        }
    }

    var restore_a = $('.block_settings.block')
        .find('a[href*="restorefile.php"]');

    if (restore_a.length < 1) {
        return;
    }

    var restore_li = restore_a.eq(0)
        .parent().parent();

    var duplicate_li = $('<li>');

    duplicate_li
        .attr('class', restore_li.attr('class'))
        .html(restore_li.html());

    var text = restore_li.text(),
        thtml = restore_li.find('a').html();

    if ((text == 'Restauration') || (text == 'Ripristino')) {
	    var newtext = 'Demander une copie' }
	else if (text == 'Restore') {
	    var newtext = 'Ask for a copy' }
	else {
		var newtext = 'Kurs-Kopie anfragen'
	};

    duplicate_li
        .find('a')
        .html(thtml.replace(text, newtext).replace('restore', 'course').replace('navigationitem','course'))
        .attr('href', M.cfg.wwwroot + '/local/coursecopy/archives_request.php?id=' + courseid);

    var import_a = $('.block_settings.block')
        .find('a[href*="import.php"]');

    var import_li = import_a.eq(0)
        .parent().parent();

    import_li
        .before(duplicate_li);

})(jQuery)
