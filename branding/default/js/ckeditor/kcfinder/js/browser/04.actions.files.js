browser.selectFile = function(file) {
    if (!file.children) {
        var files = $('#content .file').get();
        for (var i = 0; i < files.length; i++)
            if ($(files[i]).children('.name').html() == file) {
                file = $(files[i]);
                break;
            }
        if (!file.children)
            return;
    }
    $('.file').removeClass('current');
    file.addClass('current');
    var name = file.children('.name').html();
    var time = file.children('.time').html();
    var size = file.children('.size').html();
    $('#fileinfo').html(name + ' (' + size + ', ' + time + ')');
};

browser.returnFile = function(file) {
    var fileURL = file.substr ? file : this.extractFileFullURL(file);
    fileURL = _.escapeDirs(fileURL);

    if (this.wysiwyg.CKEditor) {
        this.wysiwyg.CKEditor.tools.callFunction(this.CKfuncNum, fileURL, '');
        window.close();

    } else if (this.wysiwyg.FCKeditor) {
        window.opener.SetUrl(fileURL) ;
        window.close() ;

    } else if (this.wysiwyg.TinyMCE) {
        var win = tinyMCEPopup.getWindowArg('window');
        win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = fileURL;
        if (win.getImageData) win.getImageData();
        if (typeof(win.ImageDialog) != "undefined") {
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(fileURL);
        }
        tinyMCEPopup.close();

    } else if (window.opener &&
        window.opener.KCFinder &&
        window.opener.KCFinder.callBack
    ) {
        window.opener.KCFinder.callBack(fileURL);
        window.close();

    } else if (
        window.parent &&
        window.parent.KCFinder &&
        window.parent.KCFinder.callBack
    ) {
        var button = $('#toolbar a[href="kcact:maximize"]');
        if (button.hasClass('selected'))
            this.maximize(button);

        window.parent.KCFinder.callBack(fileURL);
    }
};

browser.menuFile = function(file, e) {
    var url = this.extractFileURL(file);
    var access = file.children('.access').html();
    var html = '<div class="menu">' +
        '<a href="kcact:pick">' + this.label('Select') + '</a>';

    if (file.children('.hasThumb').html() == "yes")
        html +=
            '<a href="kcact:pick_thumb">' + this.label('Select Thumbnail') + '</a>' +
            '<div class="delimiter"></div>' +
            '<a href="kcact:view">' + this.label('View') + '</a>';
    else
        html += '<div class="delimiter"></div>';

    html +=
        '<a href="kcact:download">' + this.label('Download') + '</a>' +
        '<div class="delimiter"></div>' +
        '<a href="kcact:clpbrdadd">' + this.label('Add to Clipboard') + '</a>' +
        '<div class="delimiter"></div>' +
        '<a href="kcact:mv"' + ((access != 'writable') ? ' class="denied"' : '') + '">' + this.label('Rename...') + '</a>' +
        '<a href="kcact:rm"' + ((access == 'readonly') ? ' class="denied"' : '') + '">' + this.label('Delete') + '</a>' +
    '</div>';

    $('#dialog').html(html);
    $('#dialog a[href="kcact:pick"]').click(function() {
        browser.returnFile(file);
        browser.hideDialog();
        return false;
    });

    $('#dialog a[href="kcact:pick_thumb"]').click(function() {
        var url = browser.thumbsURL + "/" + browser.extractFileURL(file);
        browser.returnFile(url);
        browser.hideDialog();
        return false;
    });

    $('#dialog a[href="kcact:view"]').click(function() {
        browser.hideDialog();
        $('#loading').html(browser.label('Loading image...'));
        $('#loading').css('display', 'inline');
        var img = new Image();
        var url = _.escapeDirs(browser.extractFileFullURL(file));
        img.src = url;
        img.onload = function() {
            $('#loading').css('display', 'none');
            $('#dialog').html('<img />');
            $('#dialog img').attr('src', url);
            var o_w = $('#dialog').outerWidth();
            var o_h = $('#dialog').outerHeight();
            var f_w = $(window).width() - 30;
            var f_h = $(window).height() - 30;
            if ((o_w > f_w) || (o_h > f_h)) {
                if ((f_w / f_h) > (o_w / o_h))
                    f_w = parseInt((o_w * f_h) / o_h);
                else if ((f_w / f_h) < (o_w / o_h))
                    f_h = parseInt((o_h * f_w) / o_w);
                $('#dialog img').attr('width', f_w);
                $('#dialog img').attr('height', f_h);
            }
            $('#dialog').click(function() {
                browser.hideDialog();
            });
            browser.showDialog();
        }
        return false;
    });

    $('#dialog a[href="kcact:download"]').click(function() {
        var dir = $('.data .currentDir').html();
        var fileName = file.children('.name').html();
        var html = '<form id="downloadForm" method="post" action="browse.php?act=download&amp;langCode=' + browser.lang + '">' +
            '<input type="hidden" name="dir" />' +
            '<input type="hidden" name="file" />' +
        '</form>';
        $('#dialog').html(html);
        $('#downloadForm input').get(0).value = dir;
        $('#downloadForm input').get(1).value = fileName;
        $('#downloadForm').submit();
        return false;
    });

    $('#dialog a[href="kcact:clpbrdadd"]').click(function() {
        if (!browser.clipboard)
            browser.clipboard = [];
        var dir = $('.data .currentDir').html();
        var name = file.children('.name').html();

        for (i = 0; i < browser.clipboard.length; i++)
            if ((browser.clipboard[i].name == name) && (browser.clipboard[i].dir == dir)) {
                browser.hideDialog();
                alert(browser.label('This file is already added to the Clipboard.'));
                return false;
            }
        browser.clipboard[browser.clipboard.length] = browser.getFileInfo(file.children('.name').html());
        browser.clipboard[browser.clipboard.length - 1].dir = $('.data .currentDir').html();
        browser.initClipboard();
        browser.hideDialog();
        return false;
    });

    $('#dialog a[href="kcact:mv"]').click(function(e) {
        if ($(this).hasClass('denied'))
            return false;
        var dir = $('.data .currentDir').html();
        var fileName = file.children('.name').html();
        browser.generateDialog({dir: dir, file: fileName}, 'newName', fileName, 'browse.php?act=rename', {
            title: 'New file name:',
            errEmpty: 'Please eneter new file name.',
            errSlash: 'Unallowed characters in file name.',
            errDot: "File name shouldn't begins with '.'"
        }, function() {
            browser.refresh();
        });
        browser.showDialog(e);
        $('#dialog input[type="text"]').select();
        return false;
    });

    $('#dialog a[href="kcact:rm"]').click(function() {
        if ($(this).hasClass('denied'))
            return false;
        var dir = $('.data .currentDir').html();
        var fileName = file.children('.name').html();
        browser.hideDialog();
        if (confirm(browser.label(
            'Are you sure you want to delete this file?'
        ))) {
            $.post('browse.php?act=delete&langCode=' + browser.lang, {dir: dir, file: fileName},
                function(html) {
                    if (html.length)
                        alert(html);
                    else
                        browser.refresh();
                }
            );
        }
        return false;
    });

    this.showMenu(e);
};
