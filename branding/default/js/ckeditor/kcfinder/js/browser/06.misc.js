browser.showDialog = function(e) {
    this.shadow();
    if (e) {
        var left = e.pageX - parseInt($('#dialog').outerWidth() / 2);
        var top = e.pageY - parseInt($('#dialog').outerHeight() / 2);
        if (left < 15) left = 15;
        if (top < 15) top = 15;
        if (($('#dialog').outerWidth() + left) > $(window).width() - 30)
            left = $(window).width() - $('#dialog').outerWidth() - 15;
        if (($('#dialog').outerHeight() + top) > $(window).height() - 30)
            top = $(window).height() - $('#dialog').outerHeight() - 15;
        $('#dialog').css('left', left + "px");
        $('#dialog').css('top', top + "px");
    } else {
        $('#dialog').css('left', parseInt(($(window).width() - $('#dialog').outerWidth()) / 2));
        $('#dialog').css('top', parseInt(($(window).height() - $('#dialog').outerHeight()) / 2));
        $('#dialog').css('display', 'block');
    }
};

browser.hideDialog = function() {
    this.unshadow();
    if ($('#clipboard').hasClass('selected'))
        $('#clipboard').removeClass('selected');
    $('#dialog').css('display', 'none');
    $('#dialog').html('');
};

browser.shadow = function() {
    _('shadow').style.display = 'block';
};

browser.unshadow = function() {
    _('shadow').style.display = 'none';
};

browser.showMenu = function(e) {
    var left = e.pageX;
    var top = e.pageY;
    if (($('#dialog').outerWidth() + left) > $(window).width())
        left = $(window).width() - $('#dialog').outerWidth();
    if (($('#dialog').outerHeight() + top) > $(window).height())
        top = $(window).height() - $('#dialog').outerHeight();
    $('#dialog').css('left', left + "px");
    $('#dialog').css('top', top + "px");
    $('#dialog').css('display', 'block');
};

browser.generateDialog = function(post, inputName, inputValue, url, labels, callBack) {
    var html = '<form method="post" action="javascript:;">' +
        '<div class="box"><b>' + this.label(labels.title) + '</b><br />' +
        '<input name="' + inputName + '" value="' + _.htmlValue(inputValue) + '" type="text" /><br />' +
        '<div style="text-align:right">' +
        '<input type="submit" value="' + _.htmlValue(this.label('OK')) + '" />' +
        '<input type="button" value="' + _.htmlValue(this.label('Cancel')) + '" onclick="browser.hideDialog(); return false" />' +
    '</div></div></form>';
    $('#dialog').html(html);
    $('#dialog').unbind();
    $('#dialog').click(function() {
        return false;
    });
    $('#dialog form').submit(function() {
        var name = this.elements[0];
        name.value = $.trim(name.value);
        if (name.value == '') {
            alert(browser.label(labels.errEmpty));
            name.focus();
            return;
        } else if (/\//g.test(name.value)) {
            alert(browser.label(labels.errSlash))
            name.focus();
            return;
        } else if (name.value.substr(0, 1) == ".") {
            alert(browser.label(labels.errDot))
            name.focus();
            return;
        }
        eval('post.' + inputName + ' = name.value;');
        $.post(url + '&langCode=' + browser.lang, post, function(html) {
            if (html.length)
                alert(html);
            else {
                if ($.isFunction(callBack))
                    callBack();
                browser.hideDialog();
            }
        });
        return false;
    });
    $('#dialog input[type="submit"]').click(function() {
        return $('#dialog form').submit();
    });

    $('#dialog input[type="text"]').get(0).focus();
    $('#dialog input[type="text"]').keypress(function(e) {
        if (e.keyCode == 27) browser.hideDialog();
    });
};

browser.orderFiles = function(callBack) {
    if ($('#content').html().length > 30) {
        $('#content').css('opacity', '0.4');
        $('#content').css('filter', 'alpha(opacity:40)');
    }

    setTimeout(function() {
        var files = $('#fileList > div').get();
        files = files.sort(function(a, b) {
            var order = _.kuki.get('order');
            var desc = (_.kuki.get('orderDesc') == 'on');
            if (!order) order = 'name';
            a1 = $(a).children('.' + order).html().toLowerCase();
            b1 = $(b).children('.' + order).html().toLowerCase();
            if (order == 'size') {
                var a1 = parseInt(a1 ? a1 : '');
                var b1 = parseInt(b1 ? b1 : '');
                if (a1 < b1)
                    return desc ? 1 : -1;
                else if (a1 > b1)
                    return desc ? -1 : 1;
                else
                    return 0;
            } else {
                if (a1 == b1)
                    return 0;
                var arr = [a1, b1];
                arr = arr.sort();
                if (arr[0] == a1)
                    return desc ? 1 : -1;
                else
                    return desc ? -1 : 1;
            }
        });

        var html = '';
        if (_.kuki.get('view') == 'list')
            html += '<table summary="list">'
        $.each(files, function(i, file) {
            file = $(file);
            var thumb = file.children('.thumb').html();
            var icon = file.children('.bigIcon').html();
            var smallIcon = file.children('.smallIcon').html();
            var name = file.children('.name').html();
            var date = file.children('.date').html();
            var size = browser.humanSize(file.children('.size').html());
            var access = file.children('.access').html();
            var hasThumb = file.children('.hasThumb').html();

            icon = thumb ? thumb : icon;

            html += (_.kuki.get('view') == 'list')
                ? '<tr class="file">' +
                    '<td class="name" style="background-image:url(' + smallIcon + ')">' + name + '</td>' +
                    '<td class="time">' + date + '</td>' +
                    '<td class="size">' + size + '</td>' +
                    '<td class="access">' + access + '</td>' +
                    '<td class="hasThumb">' + (thumb ? "yes" : "no") + '</td>' +
                '</tr>'
                : '<div class="file">' +
                    '<div class="thumb" style="background-image:url(' + icon + ')" ></div>' +
                    '<div class="name">' + name + '</div>' +
                    '<div class="time">' + date + '</div>' +
                    '<div class="size">' + size + '</div>' +
                    '<div class="access">' + access + '</div>' +
                    '<div class="hasThumb">' + hasThumb + '</div>' +
                '</div>';
        });

        if (_.kuki.get('view') == 'list')
            html += '</table>';

        $('#content').html(html);
        browser.initFiles();
        if ($.isFunction(callBack))
            callBack();
        $('#content').css('opacity', '');
        $('#content').css('filter', '');
    }, 100);
};

browser.getFileInfo = function(filename) {
    var info = {};
    var files = $('#fileList > div').get();
    for (i = 0; i < files.length; i++)
        if (filename == $('.name', files[i]).html()) {
            $.each($(files[i]).children().get(), function(j, val) {
                var key = $(val).attr('class');
                eval('info.' + key + ' = $(val).html();');
            });
            break;
        }
    return info;
};

browser.extractDirURL = function(dir) {
    var url = dir.attr('href');
    return url.substr(7, url.length - 7);
};

browser.extractFileURL = function(file) {
    return $('.data .currentDir').html() + "/" + file.children('.name').html();
};

browser.extractFileFullURL = function(file) {
    return this.uploadURL + '/' + this.extractFileURL(file);
};

browser.humanSize = function(size) {
    if (size < 1024) {
        size = size.toString() + ' B';
    } else if (size < 1048576) {
        size /= 1024;
        size = parseInt(size).toString() + ' KB';
    } else if (size < 1073741824) {
        size /= 1048576;
        size = parseInt(size).toString() + ' MB';
    } else if (size < 1099511627776) {
        size /= 1073741824;
        size = parseInt(size).toString() + ' GB';
    } else {
        size /= 1099511627776;
        size = parseInt(size).toString() + ' TB';
    }
    return size;
};

browser.label = function(index, data) {
    var label = this.labels[index] ? this.labels[index] : index;
    if (data)
        $.each(data, function(key, val) {
            label = label.replace('{' + key + '}', val);
        });
    return label;
};
