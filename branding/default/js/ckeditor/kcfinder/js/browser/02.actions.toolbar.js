browser.uploadFile = function(form) {
    form.elements[1].value = $('.data .currentDir').html();
    $('<iframe id="uploadResponse" name="uploadResponse" src="javascript:;"></iframe>').prependTo(document.body);
    $('#loading').html(this.label('Uploading file...'));
    $('#loading').css('display', 'inline');
    form.submit();
    $('#uploadResponse').load(function() {
        var response = $(this).contents().find('body').html();
        $(this).detach();
        $('#loading').css('display', 'none');
        if (response.substr(0, 1) != '/')
            alert(response);
        else
            browser.refresh(function() {
                browser.selectFile(response.substr(1, response.length - 1));
            });
        $('#upload').detach();
        browser.initUploadButton();
    });
};

browser.maximize = function(button) {
    if (window.opener) {
        window.moveTo(0, 0);
        width = screen.availWidth;
        height = screen.availHeight;
        if ($.browser.opera)
            height -= 50;
        window.resizeTo(width, height);

    } else if (browser.wysiwyg.TinyMCE) {
        var win, ifr, id;

        $('iframe', window.parent.document).each(function() {
            if (/^mce_\d+_ifr$/.test($(this).attr('id'))) {
                id = parseInt($(this).attr('id').replace(/^mce_(\d+)_ifr$/, "$1"));
                win = $('#mce_' + id, window.parent.document);
                ifr = $('#mce_' + id + '_ifr', window.parent.document);
            }
        });

        if ($(button).hasClass('selected')) {
            $(button).removeClass('selected');
            win.css('left', browser.maximizeMCE.left + 'px');
            win.css('top', browser.maximizeMCE.top + 'px');
            win.css('width', browser.maximizeMCE.width + 'px');
            win.css('height', browser.maximizeMCE.height + 'px');
            ifr.css('width', browser.maximizeMCE.width - browser.maximizeMCE.Hspace + 'px');
            ifr.css('height', browser.maximizeMCE.height - browser.maximizeMCE.Vspace + 'px');

        } else {
            $(button).addClass('selected')
            browser.maximizeMCE = {
                width: _.nopx(win.css('width')),
                height: _.nopx(win.css('height')),
                left: win.position().left,
                top: win.position().top,
                Hspace: _.nopx(win.css('width')) - _.nopx(ifr.css('width')),
                Vspace: _.nopx(win.css('height')) - _.nopx(ifr.css('height'))
            };
            var width = $(window.parent).width();
            var height = $(window.parent).height();
            win.css('left', $(window.parent).scrollLeft() + 'px');
            win.css('top', $(window.parent).scrollTop() + 'px');
            win.css('width', width + 'px');
            win.css('height', height + 'px');
            ifr.css('width', width - browser.maximizeMCE.Hspace + 'px');
            ifr.css('height', height - browser.maximizeMCE.Vspace + 'px');
        }

    } else if (window.parent) {
        var ifrm = $('iframe[name="' + window.name + '"]', window.parent.document);
        var parent = ifrm.parent();
        var width, height;

        if ($(button).hasClass('selected')) {
            $(button).removeClass('selected');
            if (browser.maximizeThread) {
                clearInterval(browser.maximizeThread);
                browser.maximizeThread = null;
            }
            if (browser.maximizeW) browser.maximizeW = null;
            if (browser.maximizeH) browser.maximizeH = null;
            $.each($('*', window.parent.document).get(), function(i, e) {
                e.style.display = browser.maximizeDisplay[i];
            });
            ifrm.css('display', browser.maximizeCSS.display);
            ifrm.css('position', browser.maximizeCSS.position);
            ifrm.css('left', browser.maximizeCSS.left);
            ifrm.css('top', browser.maximizeCSS.top);
            ifrm.css('width', browser.maximizeCSS.width);
            ifrm.css('height', browser.maximizeCSS.height);
            $(window.parent).scrollLeft(browser.maximizeLest);
            $(window.parent).scrollTop(browser.maximizeTop);

        } else {
            $(button).addClass('selected');
            browser.maximizeCSS = {
                display: ifrm.css('display'),
                position: ifrm.css('position'),
                left: ifrm.css('left'),
                top: ifrm.css('top'),
                width: ifrm.outerWidth() + 'px',
                height: ifrm.outerHeight() + 'px'
            };
            browser.maximizeTop = $(window.parent).scrollTop();
            browser.maximizeLeft = $(window.parent).scrollLeft();
            browser.maximizeDisplay = [];
            $.each($('*', window.parent.document).get(), function(i, e) {
                browser.maximizeDisplay[i] = $(e).css('display');
                $(e).css('display', 'none');
            });

            ifrm.css('display', 'block');
            ifrm.parents().css('display', 'block');
            var resize = function() {
                width = $(window.parent).width();
                height = $(window.parent).height();
                if (!browser.maximizeW || (browser.maximizeW != width) ||
                    !browser.maximizeH || (browser.maximizeH != height)
                ) {
                    browser.maximizeW = width;
                    browser.maximizeH = height;
                    ifrm.css('width', width + 'px');
                    ifrm.css('height', height + 'px');
                    browser.resize();
                }
            }
            resize();
            browser.maximizeThread = setInterval(resize, 250);
            ifrm.css('position', 'absolute');
            if ((ifrm.offset().left == ifrm.position().left) &&
                (ifrm.offset().top == ifrm.position().top)
            ) {
                ifrm.css('left', '0');
                ifrm.css('top', '0');
            } else {
                ifrm.css('left',  - ifrm.offset().left +'px');
                ifrm.css('top',  - ifrm.offset().top + 'px');
            }
        }
    }
};

browser.refresh = function(callBack) {
    $('#content').css('opacity', '0.4');
    $('#content').css('filter', 'alpha(opacity:40)');
    var url = $('.data .currentDir').html();
    $.post('browse.php?act=chDir&langCode=' + browser.lang, {dir: url}, function(html) {
        if (html.length) {
            $('#fileList').html(html);
            browser.orderFiles(callBack);
            $('#content').css('opacity', '');
            $('#content').css('filter', '');
        }
    });
};
