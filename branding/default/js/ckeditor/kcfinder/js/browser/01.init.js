browser.init = function() {
    if (!this.checkAgent()) return;

    $('body').click(function() {
        browser.hideDialog();
    });
    $('#shadow').click(function() {
        return false;
    });
    $('#dialog').unbind();
    $('#dialog').click(function() {
        return false;
    });
    this.initWYSIWYG();
    this.initSettings();
    this.initFolders();
    this.initToolbar();
};

browser.checkAgent = function() {
    if (!$.browser.version ||
        ($.browser.msie && (parseInt($.browser.version) < 7) && !this.chromeFrame) ||
        ($.browser.opera && (parseInt($.browser.version) < 10)) ||
        ($.browser.mozilla && (parseFloat($.browser.version.replace(/^(\d+(\.\d+)?)([^\d].*)?$/, "$1")) < 1.8))
    ) {
        var html = '<div style="padding:10px">Your browser is not capable to display KCFinder. Please update your browser or install another one: <a href="http://www.mozilla.com/firefox/" target="_blank">Mozilla Firefox</a>, <a href="http://www.apple.com/safari" target="_blank">Apple Safari</a>, <a href="http://www.google.com/chrome" target="_blank">Google Chrome</a>, <a href="http://www.opera.com/browser" target="_blank">Opera</a>.';
        if ($.browser.msie)
            html += ' You may also install <a href="http://www.google.com/chromeframe" target="_blank">Google Chrome Frame ActiveX plugin</a> to use your current browser.';
        html += '</div>';
        $('body').html(html);
        return false;
    }
    return true;
};

browser.initWYSIWYG = function() {
    this.wysiwyg.TinyMCE = (typeof(tinyMCEPopup) != 'undefined');
    this.wysiwyg.FCKeditor =
        (!this.opener || (this.opener == "fckeditor")) &&
        window.opener && window.opener.SetUrl;
    this.wysiwyg.CKEditor = (
        (!this.opener || (this.opener == "ckeditor")) &&
        window.parent && window.parent.CKEDITOR
    )
        ? window.parent.CKEDITOR
        : (((!this.opener || (this.opener == "ckeditor")) &&
            window.opener && window.opener.CKEDITOR
        )
            ? window.opener.CKEDITOR
            : false);
};

browser.initSettings = function() {

    // SHOWING ELEMENTS

    if (!this.shows.length) {
        var showInputs = $('#show input[type="checkbox"]').toArray();
        $.each(showInputs, function (i, input) {
            browser.shows[i] = input.name;
        });
    }

    var shows = this.shows;

    if (!_.kuki.isSet('showname')) {
        _.kuki.set('showname', 'on');
        $.each(shows, function (i, val) {
            if (val != "name") _.kuki.set('show' + val, 'off');
        });
    }

    $('#show input[type="checkbox"]').click(function() {
        var display = $(this).get(0).checked ? 'block' : 'none';
        var kuki = $(this).get(0).checked ? 'on' : 'off';
        _.kuki.set('show' + $(this).get(0).name, kuki)
        $('#files .file div.' + $(this).get(0).name).css('display', display);
    });

    $.each(shows, function(i, val) {
        var checked = (_.kuki.get('show' + val) == 'on') ? 'checked' : '';
        $('#show input[name="' + val + '"]').attr('checked', checked);
    });

    // FILE ORDER

    if (!this.orders.length) {
        var orderInputs = $('#order input[type="radio"]').toArray();
        $.each(orderInputs, function (i, input) {
            browser.orders[i] = input.value;
        });
    }

    var orders = this.orders;

    if (!_.kuki.isSet('order'))
        _.kuki.set('order', 'name');

    if (!_.kuki.isSet('orderDesc'))
        _.kuki.set('orderDesc', 'off');

    $('#order input[value="' + _.kuki.get('order') + '"]').attr('checked', 'checked');
    $('#order input[name="desc"]').attr('checked',
        (_.kuki.get('orderDesc') == 'on') ? 'checked' : ''
    );

    $('#order input[type="radio"]').click(function() {
        _.kuki.set('order', $(this).get(0).value);
        browser.orderFiles();
    });

    $('#order input[name="desc"]').click(function() {
        _.kuki.set('orderDesc', $(this).get(0).checked ? "on" : "off");
        browser.orderFiles();
    });


    // VIEW

    if (!_.kuki.isSet('view'))
        _.kuki.set('view', 'thumbs');

    if (_.kuki.get('view') == "list") {
        $('#show input').attr('checked', 'checked');
        $('#show input').attr('disabled', 'disabled');
    }

    $('#view input[value="' + _.kuki.get('view') + '"]').attr('checked', 'checked');

    $('#view input').click(function() {
        var view = $(this).attr('value');
        if (_.kuki.get('view') != view) {
            _.kuki.set('view', view);
            if (view == 'list') {
                $('#show input').attr('checked', 'checked');
                $('#show input').attr('disabled', 'disabled');
            } else {
                $.each(browser.shows, function(i, val) {
                    if (_.kuki.get('show' + val) != "on")
                        $('#show input[name="' + val + '"]').attr('checked', '');
                });
                $('#show input').attr('disabled', '');
            }
        }
        browser.orderFiles();
    });

    this.orderFiles();
};

browser.initFolders = function() {
    $('#folders').scroll(function() {
        browser.hideDialog();
    });
    $('div.folder > a').unbind();
    $('div.folder > a').bind('click', function() {
        browser.hideDialog();
        return false;
    });
    $('div.folder > a > span.brace').unbind();
    $('div.folder > a > span.brace').click(function() {
        browser.expandDir($(this).parent());
    });
    $('div.folder > a > span.folder').unbind();
    $('div.folder > a > span.folder').click(function() {
        browser.changeDir($(this).parent());
    });
    $('div.folder > a > span.folder').rightClick(function(e) {
        browser.menuDir($(this).parent(), e);
    });
    this.initTree();
};

browser.initFiles = function() {
    $('#files').scroll(function() {
        browser.hideDialog();
    });

    $('.file').click(function() {
        _.unselect();
        browser.selectFile($(this));
    });
    $('.file').rightClick(function(e) {
        _.unselect();
        browser.selectFile($(this));
        browser.menuFile($(this), e);
    });
    $('.file').dblclick(function() {
        _.unselect();
        browser.returnFile($(this));
    });
    $('.file').mouseup(function() {
        _.unselect();
    });
    $('.file').mouseout(function() {
        _.unselect();
    });
    $.each(this.shows, function(i, val) {
        var display = (_.kuki.get('show' + val) == 'off')
            ? 'none' : 'block';
        $('#content .file div.' + val).css('display', display);
    });
    this.statusDir();
};

browser.initToolbar = function() {
    $('#toolbar a').click(function() {
        browser.hideDialog();
    });

    if (!_.kuki.isSet('displaySettings'))
        _.kuki.set('displaySettings', 'off');

    if (_.kuki.get('displaySettings') == 'on') {
        $('#toolbar a[href="kcact:settings"]').addClass('selected');
        $('#settings').css('display', 'block');
        browser.resize();
    }

    $('#toolbar a[href="kcact:settings"]').click(function () {
        $('#settings').css('display',
            ($('#settings').css('display') == 'none') ? 'block' : 'none');
        if ($('#settings').css('display') == 'none') {
            $(this).removeClass('selected');
            _.kuki.set('displaySettings', 'off');
        } else {
            $(this).addClass('selected');
            _.kuki.set('displaySettings', 'on');
        }
        browser.resize();
        return false;
    });

    $('#toolbar a[href="kcact:refresh"]').click(function() {
        browser.refresh();
        return false;
    });

    $('#toolbar a[href="kcact:maximize"]').click(function() {
        browser.maximize(this);
        return false;
    });

    $('#toolbar a[href="kcact:about"]').click(function() {
        var html = '<div class="box about">' +
            '<div class="title"><a href="http://kcfinder.sunhater.com" target="_blank">KCFinder 1.7</a></div>' +
            '<div>Licenses: GPLv2 & LGPLv2</div>' +
            '<div>Copyright &copy;2010 Pavel Tzonkov</div>' +
            '<button>' + _.htmlValue(browser.label('OK')) + '</button>' +
        '</div>';
        $('#dialog').html(html);
        browser.showDialog();
        $('#dialog button').get(0).focus();
        var close = function() {
            browser.hideDialog();
            browser.unshadow();
        }
        $('#dialog button').click(close);
        $('#dialog button').keypress(function(e) {
            if (e.keyCode == 27) close();
        });
        $('#dialog').unbind();
        return false;
    });

    this.initUploadButton();
};

browser.initUploadButton = function() {
    var btn = $('#toolbar a[href="kcact:upload"]');
    var top = btn.get(0).offsetTop;
    var width = btn.outerWidth();
    var height = btn.outerHeight();
    $('#toolbar').prepend('<div id="upload" style="top:' + top + 'px;width:' + width + 'px;height:' + height + 'px">' +
        '<form enctype="multipart/form-data" method="post" target="uploadResponse" action="browse.php?act=upload&amp;langCode=' + this.lang + '">' +
            '<input type="file" name="upload" onchange="browser.uploadFile(this.form)" style="height:' + height + 'px" />' +
            '<input type="hidden" name="dir" value="" />' +
        '</form>' +
    '</div>');
    $('#upload input').css('margin-left', "-" + ($('#upload input').outerWidth() - width) + "px");
    $('#upload').mouseover(function() {
        $('#toolbar a[href="kcact:upload"]').addClass('hover');
    });
    $('#upload').mouseout(function() {
        $('#toolbar a[href="kcact:upload"]').removeClass('hover');
    });
};


browser.initTree = function() {
    if ($.browser.msie && $.browser.version &&
        (parseInt($.browser.version.substr(0, 1)) < 8)
    ) {
        var fls = $('div.folder').get();
        var body = $('body').get(0);
        var div;
        $.each(fls, function(i, folder) {
            div = document.createElement('div');
            div.style.display = 'inline';
            div.style.margin = div.style.border = div.style.padding = '0';
            div.innerHTML='<table style="border-collapse:collapse;border:0;margin:0;width:0"><tr><td nowrap="nowrap" style="white-space:nowrap;padding:0;border:0">' + $(folder).html() + "</td></tr></table>";
            body.appendChild(div);
            $(folder).css('width', $(div).innerWidth() + 'px');
            body.removeChild(div);
        });
    }
};

browser.resize = function() {
    _('toolbar').style.height = $('#toolbar a').outerHeight() + "px";
    _('shadow').style.width = $(window).width() + 'px';
    _('shadow').style.height = $(window).height() + 'px';
    _('left').style.height = _('right').style.height =
        $(window).height() - $('#status').outerHeight() + 'px';

    _('folders').style.height =
        $('#left').outerHeight() - _.outerVSpace('#folders') + 'px';

    _('files').style.height =
        $('#left').outerHeight() - $('#toolbar').outerHeight() - _.outerVSpace('#files') -
        (($('#settings').css('display') != "none") ? $('#settings').outerHeight() : 0) + 'px';

    var width = $('#left').outerWidth() + $('#right').outerWidth();
    _('status').style.width = width + 'px';
    while ($('#status').outerWidth() > width)
        _('status').style.width = _.nopx(_('status').style.width) - 1 + 'px';
    while ($('#status').outerWidth() < width)
        _('status').style.width = _.nopx(_('status').style.width) + 1 + 'px';

    if ($.browser.msie && ($.browser.version.substr(0, 1) < 8))
        _('right').style.width = $(window).width() - $('#left').outerWidth() + 'px';

    _('files').style.width = $('#right').innerWidth() - _.outerHSpace('#files') + 'px';
};
