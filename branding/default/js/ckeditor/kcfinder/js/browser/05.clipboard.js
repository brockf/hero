browser.initClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var size = 0;
    $.each(this.clipboard, function(i, val) {
        size += parseInt(val.size);
    });
    size = this.humanSize(size);
    $('#clipboard').html('<div title="' + this.label('Clipboard') + ' (' + this.clipboard.length + ' files, ' + size + ')" onclick="browser.openClipboard()"></div>');
    var resize = function() {
        $('#clipboard').css('left', $(window).width() - $('#clipboard').outerWidth() + 'px');
        $('#clipboard').css('top', $(window).height() - $('#clipboard').outerHeight() + 'px');
    };
    resize();
    $('#clipboard').css('display', 'block');
    $(window).resize(resize);
};

browser.openClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    if ($('.menu a[href="kcact:cpcbd"]').html()) {
        $('#clipboard').removeClass('selected');
        this.hideDialog();
        return;
    }
    var html = '<div class="menu"><div class="list">';
    $.each(this.clipboard, function(i, val) {
        html += '<a style="background-image:url(' + _.escapeDirs(val.smallIcon) + ')" title="' + browser.label('Click to remove from the Clipboard') + '" onclick="browser.removeFromClipboard(' + i + ')">' + _.basename(val.name) + '</a>';
    });
    html += '</div>' +
        '<div class="delimiter"></div>' +
        '<a href="kcact:cpcbd">' + this.label('Copy files here') + '</a>' +
        '<a href="kcact:mvcbd">' + this.label('Move files here') + '</a>' +
        '<a href="kcact:rmcbd">' + this.label('Delete files') + '</a>' +
        '<div class="delimiter"></div>' +
        '<a href="kcact:clrcbd">' + this.label('Clear the Clipboard') + '</a>' +
    '</div>';
    setTimeout(function() {
        $('#clipboard').addClass('selected');
        $('#dialog').html(html);
        $('#dialog a[href="kcact:cpcbd"]').click(function() {
            browser.copyClipboard();
            browser.hideDialog();
            return false;
        });
        $('#dialog a[href="kcact:mvcbd"]').click(function() {
            browser.moveClipboard();
            browser.hideDialog();
            return false;
        });
        $('#dialog a[href="kcact:rmcbd"]').click(function() {
            browser.hideDialog();
            if (confirm(browser.label('Are you sure you want to delete all files in the clipboard?')))
                browser.deleteClipboard();
            return false;
        });
        $('#dialog a[href="kcact:clrcbd"]').click(function() {
            browser.clearClipboard();
            browser.hideDialog();
            return false;
        });

        var left = $(window).width() - $('#dialog').outerWidth();
        var top = $(window).height() - $('#dialog').outerHeight() - $('#clipboard').outerHeight();
        var lheight = top + _.outerTopSpace('#dialog');
        $('.menu .list').css('max-height', lheight + 'px');
        var top = $(window).height() - $('#dialog').outerHeight() - $('#clipboard').outerHeight();
        $('#dialog').css('left', left + 'px');
        $('#dialog').css('top', top + 'px');
        $('#dialog').css('display', 'block');
    }, 1);
};

browser.removeFromClipboard = function(i) {
    if (!this.clipboard || !this.clipboard[i]) return false;
    if (this.clipboard.length == 1) {
        this.clearClipboard();
        this.hideDialog();
        return;
    }

    if (i < this.clipboard.length - 1) {
        var last = this.clipboard.slice(i + 1);
        this.clipboard = this.clipboard.slice(0, i);
        this.clipboard = this.clipboard.concat(last);
    } else
        this.clipboard.pop();

    this.hideDialog();
    this.openClipboard();
    return true;
};

browser.copyClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    for (i = 0; i < this.clipboard.length; i++)
        files[i] = this.clipboard[i].dir + "/" + this.clipboard[i].name;
    var dir = $('.data .currentDir').html();
    $('#content').css('opacity', '0.4');
    $('#content').css('filter', 'alpha(opacity:40)');
    $.ajax({
        type: 'POST',
        url: 'browse.php?act=cp_cbd&langCode=' + browser.lang,
        data: { dir: dir, files: files },
        success: function(html) {
            if (html.length) alert(html);
            browser.clearClipboard();
            browser.refresh();
        }
    });
};

browser.moveClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    for (i = 0; i < this.clipboard.length; i++)
        files[i] = this.clipboard[i].dir + "/" + this.clipboard[i].name;
    var dir = $('.data .currentDir').html();
    $('#content').css('opacity', '0.4');
    $('#content').css('filter', 'alpha(opacity:40)');
    $.ajax({
        type: 'POST',
        url: 'browse.php?act=mv_cbd&langCode=' + browser.lang,
        data: { dir: dir, files: files },
        success: function(html) {
            if (html.length) alert(html);
            browser.clearClipboard();
            browser.refresh();
        }
    });
};

browser.deleteClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    for (i = 0; i < this.clipboard.length; i++)
        files[i] = this.clipboard[i].dir + "/" + this.clipboard[i].name;
    $('#content').css('opacity', '0.4');
    $('#content').css('filter', 'alpha(opacity:40)');
    $.ajax({
        type: 'POST',
        url: 'browse.php?act=rm_cbd&langCode=' + browser.lang,
        data: { files: files },
        success: function(html) {
            if (html.length) alert(html);
            browser.clearClipboard();
            browser.refresh();
        }
    });
};

browser.clearClipboard = function() {
    $('#clipboard').html('');
    this.clipboard = false;
};
