browser.expandDir = function(dir, callBack) {
    var url = this.extractDirURL(dir);
    if (dir.children('.brace').hasClass('opened')) {
        dir.parent().children('.folders').detach();
        dir.children('.brace').removeClass('opened');
        dir.children('.brace').addClass('closed');
        if ($.isFunction(callBack))
            callBack();
    } else {
        dir.parent().append('<div id="loadingDirs">' + this.label('Loading folders...') + '</div>');
        $.post('browse.php?act=expand&langCode=' + browser.lang, {dir: url}, function(html) {
            $('#loadingDirs').detach();
            if (html.length) {
                dir.parent().append('<div class="folders">' + html + '</div>');
                browser.initFolders();
            }
            dir.children('.brace').removeClass('closed');
            dir.children('.brace').addClass('opened');
            browser.initTree();
            if ($.isFunction(callBack))
                callBack();
        });
    }
};

browser.changeDir = function(dir) {
    var url = this.extractDirURL(dir);
    var title = "KCFinder: /" + url;
    document.title = title;

    if (dir.children('span.folder').hasClass('regular')) {
        $('div.folder > a > span.folder').removeClass('current');
        $('div.folder > a > span.folder').removeClass('regular');
        $('div.folder > a > span.folder').addClass('regular');
        dir.children('span.folder').removeClass('regular');
        dir.children('span.folder').addClass('current');
        $('#content').html(this.label("Loading files..."));
        $.post('browse.php?act=chDir&langCode=' + this.lang, {dir: url}, function(html) {
            if (html.length) {
                $('#fileList').html(html);
                $('.data .currentDir').html(url);
                browser.orderFiles();
            }
        });
    }

    if (this.wysiwyg.TinyMCE)
        tinyMCEPopup.editor.windowManager.setTitle(window, title);
};

browser.statusDir = function() {
    var files = $('#fileList > div').get();
    for (var i = 0, size = 0; i < files.length; i++)
        size += parseInt($(files[i]).children('.size').html());
    size = this.humanSize(size);
    $('#fileinfo').html($('#fileList > div').size() + ' ' + this.label('files') + ' (' + size + ')');
};

browser.menuDir = function(dir, e) {
    var url = this.extractDirURL(dir);
    var access = dir.attr('target');
    var html = '<div class="menu">';
    if (this.clipboard && this.clipboard.length && (access != 'readonly'))
        html +=
            '<a href="kcact:cpcbd">' + this.label("Copy {count} files", {count: this.clipboard.length}) + '</a>' +
            '<a href="kcact:mvcbd">' + this.label("Move {count} files", {count: this.clipboard.length}) + '</a>' +
            '<div class="delimiter"></div>';
    html +=
        '<a href="kcact:mkdir"' + ((access == 'readonly') ? ' class="denied"' : '') + '">' + this.label('New Subfolder...') + '</a>' +
        '<a href="kcact:mvdir"' + ((access != 'writable') ? ' class="denied"' : '') + '">' + this.label('Rename...') + '</a>' +
        '<a href="kcact:rmdir"' + ((access != 'writable') ? ' class="denied"' : '') + '">' + this.label('Delete') + '</a>' +
    '</div>';
    $('#dialog').html(html);
    this.showMenu(e);

    if (this.clipboard && this.clipboard.length && (access != 'readonly')) {

        $('#dialog a[href="kcact:cpcbd"]').click(function() {
            browser.hideDialog();
            var files = [];
            for (i = 0; i < browser.clipboard.length; i++)
                files[i] = browser.clipboard[i].dir + "/" + browser.clipboard[i].name;
            var dir = $('.data .currentDir').html();
            if (url == dir) {
                $('#content').css('opacity', '0.4');
                $('#content').css('filter', 'alpha(opacity:40)');
            }
            $.ajax({
                type: 'POST',
                url: 'browse.php?act=cp_cbd&langCode=' + browser.lang,
                data: { dir: url, files: files },
                success: function(html) {
                    if (html.length) alert(html);
                    browser.clearClipboard();
                    if (url == dir) browser.refresh();
                }
            });
            return false;
        });

        $('#dialog a[href="kcact:mvcbd"]').click(function() {
            browser.hideDialog();
            var files = [];
            for (i = 0; i < browser.clipboard.length; i++)
                files[i] = browser.clipboard[i].dir + "/" + browser.clipboard[i].name;
            $('#content').css('opacity', '0.4');
            $('#content').css('filter', 'alpha(opacity:40)');
            $.ajax({
                type: 'POST',
                url: 'browse.php?act=mv_cbd&langCode=' + browser.lang,
                data: { dir: url, files: files },
                success: function(html) {
                    if (html.length) alert(html);
                    browser.clearClipboard();
                    browser.refresh();
                }
            });
            return false;
        });
    }

    $('#dialog a[href="kcact:mkdir"]').click(function(e) {
        if ($(this).hasClass('denied'))
            return false;
        browser.generateDialog({dir: url},
            'newDir', '', 'browse.php?act=newDir', {
                title: 'New folder name:',
                errEmpty: 'Please eneter new folder name.',
                errSlash: 'Unallowed characters in folder name.',
                errDot: "Folder name shouldn't begins with '.'"
            }, function() {
                browser.expandDir(dir, function() {
                    if (dir.children('.opened').html() == null)
                        browser.expandDir(dir);
                });
            }
        );
        browser.showDialog(e);
        return false;
    });

    $('#dialog a[href="kcact:mvdir"]').click(function(e) {
        if ($(this).hasClass('denied'))
            return false;
        var oldName = _.basename(url);
        browser.generateDialog({dir: url}, 'newName', oldName, 'browse.php?act=renameDir', {
            title: 'New folder name:',
            errEmpty: 'Please eneter new folder name.',
            errSlash: 'Unallowed characters in folder name.',
            errDot: "Folder name shouldn't begins with '.'"
        }, function() {
            var newDir = dir.parent().parent().parent().children('a').first();
            browser.expandDir(newDir, function() {
                browser.expandDir(newDir);
            });
        });
        browser.showDialog(e);
        $('#dialog input[type="text"]').select();
        return false;
    });

    $('#dialog a[href="kcact:rmdir"]').click(function() {
        if ($(this).hasClass('denied'))
            return false;
        browser.hideDialog();
        if (confirm(browser.label(
            'Are you sure you want to delete this folder and all its content?'
        ))) {
            $.post('browse.php?act=deleteDir&langCode=' + browser.lang, {dir: url},
                function(html) {
                    if (html.length)
                        alert(html);
                    else {
                        var pDir = dir.parent().parent().parent().children('a').first();
                        browser.expandDir(pDir, function() {
                            browser.expandDir(pDir);
                        });
                        if (url == $('.data .currentDir').html())
                            browser.changeDir(pDir);
                    }
                }
            );
        }
        return false;
    });
};
