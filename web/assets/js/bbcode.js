
var sceditor = $('.redactor'),
sceditorLimited = $('.redactor-limited'),
sceditorHTML = $('.redactor-html');

// Load code colorization
hljs.initHighlightingOnLoad();



// hr tag
$.sceditor.command.set('horizontalrule', {
    exec: function() {
        this.insert("[hr][/hr]");
    },
    txtExec: function() {
        this.insert("[hr][/hr]");
    },
    tooltip: "Ajouter une ligne horizontal"
});
$.sceditor.plugins.bbcode.bbcode.set('hr', {
    tags: {
        "hr": null
    },
    allowsEmpty: true,
    isSelfClosing: false,
    format: '[hr][/hr]',
    html: '<hr />'
})



// abbr tag
var abbrExec = function (caller, sel) {
    var    editor  = this,
        content =
            $('<div><label for="abbreviation">Abréviation :</label> ' +
                '<input type="text" id="abbreviation" placeholder="U.T.T." /></div>' +
            '<div><label for="description">Description :</label> ' +
                '<input type="text" id="description" size="2" placeholder="Université de Technologie de Troyes"/></div>' +
            '<div><input type="button" class="button" value="Ajouter" />' +
            '</div>');

    content.find('.button').click(function (e) {

    var    abbreviation = content.find('#abbreviation').val(),
        description  = content.find('#description').val();

        if (abbreviation && description) {
            editor.insert('[abbr="'+ description +'"]' + abbreviation + '[/abbr]');
        }

        editor.closeDropDown(true);
        e.preventDefault();
    });

    editor.createDropDown(caller, 'insertabbr', content);
};
$.sceditor.command.set('abbreviation', {
    exec: abbrExec,
    txtExec: abbrExec,
    tooltip: "Insert an abbreviation"
});
$.sceditor.plugins.bbcode.bbcode.set('abbr', {
    quoteType: $.sceditor.BBCodeParser.QuoteType.always,
    tags: {
        'abbr': null
    },
    format: function($element, content) {
        return '[abbr="'+ $element.attr('title') +'"]' + content + '[/abbr]';
    },
    html: function (token, attrs, content) {
        return '<abbr title="'+attrs.defaultattr+'">'+content+'</abbr>';
    },
})



// float tag
var floatExec = function (caller, sel) {
    var    editor  = this,
        selection = sel,
        content =
            $('<div><label for="position">Position: </label> '
                + '<select id="position">'
                + '<option value="right">Droite</option>'
                + '<option value="left">Gauche</option>'
                + '</select></div>'
                + '<div><input type="button" class="button" value="Ajouter" />'
                + '</div>');

    content.find('.button').click(function (e) {

    var    position = content.find('#position').val();

        if (position) {
            if(sel === undefined) {
                editor.wysiwygEditorInsertHtml('<div class="float-'+ position +'" data-sceditor-float="'+ position +'">' , '</div>')
            }
            else {
                editor.insert('[float="'+ position +'"]' + selection + '[/float]');
            }
        }

        editor.closeDropDown(true);
        e.preventDefault();
    });

    editor.createDropDown(caller, 'insertabbr', content);
};
$.sceditor.command.set('float', {
    exec: floatExec,
    txtExec: floatExec,
    tooltip: "Mettre un block en position flotante sur un coté du document."
});
$.sceditor.plugins.bbcode.bbcode.set('float', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    tags: {
        'div': {
            'data-sceditor-float' : [ 'right', 'left' ],
        }
    },
    format: function($element, content) {
        return '[float="'+ $element.data('sceditor-float') +'"]' + content + '[/float]';
    },
    html: function (token, attrs, content) {
        return '<div class="float-'+attrs.defaultattr+'" data-sceditor-float="'+attrs.defaultattr+'">'+content+'</div>';
    },
})

// alert tag
var alertExec = function (caller, sel) {
    if(sel === undefined) {
        this.wysiwygEditorInsertHtml('<div class="decoda-alert alert" data-sceditor-alert="true">', '</div>')
    }
    else {
        this.insert('[alert]' + sel + '[/alert]');
    }
};
$.sceditor.command.set('alert', {
    exec: alertExec,
    txtExec: alertExec,
    tooltip: "Ajouter une boiter indiquant un danger."
});
$.sceditor.plugins.bbcode.bbcode.set('alert', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    tags: {
        'div': {
            'data-sceditor-alert' : ['true'],
        }
    },
    format: function($element, content) {
        return '[alert]' + content + '[/alert]';
    },
    html: function (token, attrs, content) {
        return '<div class="decoda-alert alert" data-sceditor-alert="true">'+content+'</div>';
    },
})

// note tag
var noteExec = function (caller, sel) {
    if(sel === undefined) {
        this.wysiwygEditorInsertHtml('<div class="decoda-note alert alert-info" data-sceditor-note="true">', '</div>')
    }
    else {
        this.insert('[note]' + sel + '[/note]');
    }
};
$.sceditor.command.set('note', {
    exec: noteExec,
    txtExec: noteExec,
    tooltip: "Ajouter une boiter indiquant une 'note'."
});
$.sceditor.plugins.bbcode.bbcode.set('note', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    tags: {
        'div': {
            'data-sceditor-note' : ['true'],
        }
    },
    format: function($element, content) {
        return '[note]' + content + '[/note]';
    },
    html: function (token, attrs, content) {
        return '<div class="decoda-note alert alert-info" data-sceditor-note="true">'+content+'</div>';
    },
})


// source tag
$.sceditor.command.set('var', {
    exec: function() {
        this.insert('[source]', '[/source]');
    },
    txtExec: ['[source]', '[/source]'],
    tooltip: "Mettre en forme la selection en tant que code source au sein d'un text (inline)."
});
$.sceditor.plugins.bbcode.bbcode.set('source', {
    tags: {
        'code': null
    },
    format: '[source]{0}[/source]',
    html: '<code>{0}</code>',
})



// code tag
var languages =  { '1c' : '1C', 'abnf' : 'ABNF', 'accesslog' : 'Access logs', 'ada' : 'Ada', 'armasm' : 'ARM assembler', 'avrasm' : 'AVR assembler', 'actionscript' : 'ActionScript', 'apache' : 'Apache', 'applescript' : 'AppleScript', 'asciidoc' : 'AsciiDoc', 'aspectj' : 'AspectJ', 'autohotkey' : 'AutoHotkey', 'autoit' : 'AutoIt', 'awk' : 'Awk', 'axapta' : 'Axapta', 'bash' : 'Bash', 'basic' : 'Basic', 'bnf' : 'BNF', 'brainfuck' : 'Brainfuck', 'cs' : 'C#', 'cpp' : 'C++', 'cal' : 'C/AL', 'cos' : 'Cache Object Script', 'cmake' : 'CMake', 'coq' : 'Coq', 'csp' : 'CSP', 'css' : 'CSS', 'capnproto' : 'Cap’n Proto', 'clojure' : 'Clojure', 'coffeescript' : 'CoffeeScript', 'crmsh' : 'Crmsh', 'crystal' : 'Crystal', 'd' : 'D', 'dns' : 'DNS Zone file', 'dos' : 'DOS', 'dart' : 'Dart', 'delphi' : 'Delphi', 'lazarus' : 'Lazarus', 'diff' : 'Diff', 'django' : 'Django', 'dockerfile' : 'Dockerfile', 'dsconfig' : 'dsconfig', 'dts' : 'DTS (Device Tree)', 'dust' : 'Dust', 'ebnf' : 'EBNF', 'elixir' : 'Elixir', 'elm' : 'Elm', 'erlang' : 'Erlang', 'excel' : 'Excel', 'fsharp' : 'F#', 'fix' : 'FIX', 'fortran' : 'Fortran', 'gcode' : 'G-Code', 'gams' : 'Gams', 'gauss' : 'GAUSS', 'gherkin' : 'Gherkin', 'go' : 'Go', 'golo' : 'Golo', 'gradle' : 'Gradle', 'groovy' : 'Groovy', 'xml' : 'HTML, XML', 'http' : 'HTTP', 'haml' : 'Haml', 'handlebars' : 'Handlebars', 'haskell' : 'Haskell', 'haxe' : 'Haxe', 'ini' : 'Ini', 'inform7' : 'Inform7', 'irpf90' : 'IRPF90', 'json' : 'JSON', 'java' : 'Java', 'javascript' : 'JavaScript', 'lasso' : 'Lasso', 'less' : 'Less', 'ldif' : 'LDIF', 'lisp' : 'Lisp', 'livecodeserver' : 'LiveCode Server', 'livescript' : 'LiveScript', 'lua' : 'Lua', 'makefile' : 'Makefile', 'markdown' : 'Markdown', 'mathematica' : 'Mathematica', 'matlab' : 'Matlab', 'maxima' : 'Maxima', 'mel' : 'Maya Embedded Language', 'mercury' : 'Mercury', 'mizar' : 'Mizar', 'mojolicious' : 'Mojolicious', 'monkey' : 'Monkey', 'moonscript' : 'Moonscript', 'nsis' : 'NSIS', 'nginx' : 'Nginx', 'nimrod' : 'Nimrod', 'nix' : 'Nix', 'ocaml' : 'OCaml', 'objectivec' : 'Objective C', 'glsl' : 'OpenGL Shading Language', 'openscad' : 'OpenSCAD', 'ruleslanguage' : 'Oracle Rules Language', 'oxygene' : 'Oxygene', 'pf' : 'PF', 'php' : 'PHP', 'parser3' : 'Parser3', 'perl' : 'Perl', 'pony' : 'Pony', 'powershell' : 'PowerShell', 'processing' : 'Processing', 'prolog' : 'Prolog', 'protobuf' : 'Protocol Buffers', 'puppet' : 'Puppet', 'python' : 'Python', 'profile' : 'Python profiler results ', 'k' : 'Q', 'qml' : 'QML', 'r' : 'R', 'rib' : 'RenderMan RIB', 'rsl' : 'RenderMan RSL', 'graph' : 'Roboconf', 'ruby' : 'Ruby', 'rust' : 'Rust', 'scss' : 'SCSS', 'sql' : 'SQL', 'p21' : 'STEP Part 21', 'scala' : 'Scala', 'scheme' : 'Scheme', 'scilab' : 'Scilab', 'smali' : 'Smali', 'smalltalk' : 'Smalltalk', 'stan' : 'Stan', 'stata' : 'Stata', 'stylus' : 'Stylus', 'subunit' : 'SubUnit', 'swift' : 'Swift', 'tap' : 'Test Anything Protocol', 'tcl' : 'Tcl', 'tex' : 'TeX', 'thrift' : 'Thrift', 'tp' : 'TP', 'twig' : 'Twig', 'typescript' : 'TypeScript', 'vbnet' : 'VB.Net', 'vbscript' : 'VBScript', 'vhdl' : 'VHDL', 'vala' : 'Vala', 'verilog' : 'Verilog', 'vim' : 'Vim Script', 'x86asm' : 'x86 Assembly', 'xl' : 'XL', 'xpath' : 'XQuery', 'zephir' : 'Zephir' };
var codeExec = function (caller, sel) {
    var    editor  = this,
        selection = sel,
        content = '<div><label for="language">Langage :</label> '
                + '<select id="language">'
                + '<option value="null">Détection auto.</option>';
    for(lang in languages) {
        content += '<option value="'+lang+'">'+languages[lang]+'</option>';
    }
    content += '</select></div>'
            + '<div><input type="button" class="button" value="Ajouter" />'
            + '</div>';
    content = $(content);

    content.find('.button').click(function (e) {

        var    lang = content.find('#language').val();
        if (lang) {
            if(sel === undefined) {
                editor.wysiwygEditorInsertHtml('<pre class="decoda-code" data-sceditor-code="'+lang+'">' , '</pre>')
            }
            else {
                if(lang) {
                    editor.insert('[code="'+lang+'"]' + selection + '[/code]');
                }
                else {
                    editor.insert('[code]' + selection + '[/code]');
                }
            }
        }

        editor.closeDropDown(true);
        e.preventDefault();
    });

    editor.createDropDown(caller, 'insertabbr', content);
};
$.sceditor.command.set('code', {
    exec: codeExec,
    txtExec: codeExec,
    tooltip: "Mettre en forme la selection en tant que bloc code source."
});
$.sceditor.plugins.bbcode.bbcode.set('code', {
    quoteType: $.sceditor.BBCodeParser.QuoteType.always,
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    tags: {
        'pre': {
            'data-sceditor-code' : null,
        }
    },
    format: function($element, content) {
        if($element.data('sceditor-code')) {
            return '[code="'+ $element.data('sceditor-code') +'"]' + content + '[/code]';
        }
        return '[code]' + content + '[/code]';
    },
    html: function (token, attrs, content) {
        if(attrs.defaultattr) {
            return '<pre class="decoda-code" data-sceditor-code="'+attrs.defaultattr+'">'+content+'</pre>';
        }
        else {
            return '<pre class="decoda-code" data-sceditor-code="null">'+content+'</pre>';
        }
    },
})


// img tag
var imageExec = function (caller, sel) {
    var    editor  = this,
        selection = sel,
        content =
            $('<div><label for="link">URL:</label> ' +
                '<input type="text" id="link" placeholder="http://" /></div>' +
                '<div><label for="width">Largeur (px, optionnel):</label> ' +
                '<input type="text" id="width" size="2" /></div>' +
                '<div><label for="height">Hauteur (px, optionnel):</label> ' +
                '<input type="text" id="height" size="2" /></div>' +
                '<div><input type="button" class="button" value="Insert" />' +
                '</div>');

    content.find('.button').click(function (e) {

        var    link = content.find('#link').val();
        var    width = parseInt(content.find('#width').val());
        var    height = parseInt(content.find('#height').val());

        if (link) {
            var param = '';
            if(width) {
                param += ' width="'+parseInt(width)+'"';
            }
            if(height) {
                param += ' height="'+parseInt(height)+'"';
            }
            editor.insert('[img'+ param +']' + link + '[/img]');
        }

        editor.closeDropDown(true);
        e.preventDefault();
    });

    editor.createDropDown(caller, 'insertImage', content);
};
$.sceditor.command.set('image', {
    exec: imageExec,
    txtExec: imageExec,
    tooltip: "Insert image"
});
$.sceditor.plugins.bbcode.bbcode.set('img', {
    quoteType: $.sceditor.BBCodeParser.QuoteType.always,
    skipLastLineBreak : false,
    isInline: true,
    isHtmlInline: true,
    tags: {
        'img': {
            'data-sceditor-img' : null,
        }
    },
    format: function($element, content) {
        var param = '';
        if($element.attr('width')) {
            param += ' width="'+$element.attr('width')+'"';
        }
        if($element.attr('height')) {
            param += ' height="'+$element.attr('height')+'"';
        }
        return '[img '+ param +']' + $element.attr('src') + '[/img]';
    },
    html: function (token, attrs, content) {
        var param = '';
        var css = '';
        if(attrs.width) {
            param += ' width="'+attrs.width+'"';
            css += 'width: '+attrs.width+'px;';
        }
        if(attrs.height) {
            param += ' height="'+attrs.height+'"';
            css += 'height: '+attrs.height+'px;';
        }
        if(css) {
            param += ' style="'+css+'"';
        }
        return '<img data-sceditor-img="true" src="'+ content +'" alt=""'+param+'/>';
    },
})


// quote tag
var quoteExec = function (caller, sel) {
    var    editor  = this,
        selection = sel,
        content =
            $('<div><label for="link">Author (optional):</label> ' +
                '<input type="text" id="author"/></div>' +
                '<div><input type="button" class="button" value="Insert" />' +
                '</div>');

    content.find('.button').click(function (e) {

        var    author = content.find('#author').val();
        if(sel === undefined) {
            var start = '<blockquote><p>&nbsp;';
            var end = '</p></blockquote>';

            if (author) {
                end = '</p><small>' + author +
                    '</small></blockquote>';
            }
            editor.wysiwygEditorInsertHtml(start, end)
        }
        else {
            var param = '';
            if (author) {
                param = '="'+ author +'"';
            }
            editor.insert('[quote'+param+']' + selection + '[/quote]');
        }

        editor.closeDropDown(true);
        e.preventDefault();
    });

    editor.createDropDown(caller, 'insertImage', content);
};
$.sceditor.command.set('quote', {
    exec: quoteExec,
    txtExec: quoteExec,
    tooltip: "Insert quote"
});
$.sceditor.plugins.bbcode.bbcode.set('quote', {
    quoteType: $.sceditor.BBCodeParser.QuoteType.always,
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    tags: {
        blockquote: null
    },
    format: function ($elm, content) {
        var author = $elm.children('small').text();
        var content = $elm.children('p').text();

        if (author.length >= 1) {
            author = '="'+author+'"';
        }
        else {
            author = '';
        }

        return '[quote' + author + ']' + content + '[/quote]';
    },
    html: function (token, attrs, content) {
        content = '<p>' + content + '</p>';
        if (attrs.defaultattr) {
            content += '<small>' + attrs.defaultattr +
                '</small>';
        }
        return '<blockquote>' + content + '</blockquote>';
    }
})


// tag H2-6
var headersExec = function (caller, sel) {
    var editor   = this,
        $content = $("<div>");

    // Create the 1-6 header options
    for (var i=2; i<= 6; i++) {
        $(
            '<a class="sceditor-header-option" href="#">' +
                '<h' + i + '>Titre ' + (i-1) + '</h' + i + '>' +
            '</a>'
        )
        .data('headersize', i)
        .click(function (e) {
            editor.insert('[h' + $(this).data('headersize') + ']', '[/h' + $(this).data('headersize') + ']');
            editor.closeDropDown(true);

            e.preventDefault();
        })
        .appendTo($content);
    }

    editor.createDropDown(caller, "header-picker", $content);
}
$.sceditor.command.set("headers", {
    exec: headersExec,
    txtExec: headersExec,
    tooltip: "Ajouter un titre"
});
$.sceditor.plugins.bbcode.bbcode.set('h2', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    allowsEmpty: true,
    tags: { h2: null },
    format: '[h2]{0}[/h2]',
    html: '<h2>{0}</h2>',
})

$.sceditor.plugins.bbcode.bbcode.set('h3', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    allowsEmpty: true,
    tags: { h3: null },
    format: '[h3]{0}[/h3]',
    html: '<h3>{0}</h3>',
})

$.sceditor.plugins.bbcode.bbcode.set('h4', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    allowsEmpty: true,
    tags: { h4: null },
    format: '[h4]{0}[/h4]',
    html: '<h4>{0}</h4>',
})

$.sceditor.plugins.bbcode.bbcode.set('h5', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    allowsEmpty: true,
    tags: { h5: null },
    format: '[h5]{0}[/h5]',
    html: '<h5>{0}</h5>',
})

$.sceditor.plugins.bbcode.bbcode.set('h6', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    allowsEmpty: true,
    tags: { h6: null },
    format: '[h6]{0}[/h6]',
    html: '<h6>{0}</h6>',
})

$.sceditor.plugins.bbcode.bbcode.set('table', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    tags: { table: null },
    format: '[table]{0}[/table]',
    html: '<table class="decoda-table table table-striped table-responsive table-bordered">{0}</table>',
})

// Video command
var videoExec = function (caller, sel) {
    var    editor  = this,
        content =
            $('<div><label for="tyoe">Type : </label> '
                + '<select id="type">'
                + '<option value="youtube">YouTube</option>'
                + '<option value="videojs">Fichier mp4</option>'
                + '</select></div>'
                + '<div><label for="link">URL :</label> ' +
                    '<input type="text" id="link" placeholder="http://"/></div>'
                + '<div><input type="button" class="button" value="Ajouter" />'
                + '</div>');

        content.find('.button').click(function (e) {
            var    link = content.find('#link').val();
            var    type = content.find('#type').val();
            if (link) {
                if(type == 'youtube') {
                    var matches = link.match(/(?:youtube\.com\/\S*(?:(?:\/e(?:mbed))?\/|watch\/?\?(?:\S*?&?v\=))|youtu\.be\/)([a-zA-Z0-9_-]{6,11})/i);
                    if (matches) {
                        editor.insert('[youtube]'+matches[1]+'[/youtube]')
                    } else {
                        alert('Lien YouTube invalide');
                        return false;
                    }
                }
                else {
                    editor.insert('[videojs]'+link+'[/videojs]');
                }
            }

            editor.closeDropDown(true);
            e.preventDefault();
        });

    editor.createDropDown(caller, 'insertabbr', content);
}

$.sceditor.command.set("video", {
    exec: videoExec,
    txtExec: videoExec,
    tooltip: "Ajouter une video"
});


$.sceditor.plugins.bbcode.bbcode.set('videojs', {
    skipLastLineBreak : true,
    isInline: false,
    isHtmlInline: false,
    allowsEmpty: true,
    tags:{
        video: {
            'data-sceditor-video': null,
        }
    },
    format: function (element, content) {
        var    video = element.attr('data-sceditor-video');
        return '[videojs]' + video + '[/videojs]';
    },
    html: '<video class="video-js" controls preload="auto" data-setup="{}" data-sceditor-video="{0}" style="width:560px;height:315px;">'
        + '<source src="{0}" type="video/mp4">'
        + '</video>',
});


// Blacklisted tags (because we don't want people to write in pink 'Comic Sans')
$.sceditor.plugins.bbcode.bbcode.set('u', {html:null,format:null});
$.sceditor.plugins.bbcode.bbcode.set('sub', {html:null,format:null});
$.sceditor.plugins.bbcode.bbcode.set('sup', {html:null,format:null});
$.sceditor.plugins.bbcode.bbcode.set('font', {html:null,format:null});
$.sceditor.plugins.bbcode.bbcode.set('size', {html:null,format:null});
$.sceditor.plugins.bbcode.bbcode.set('color', {html:null,format:null});
$.sceditor.plugins.bbcode.bbcode.set('spoiler', {html:null,format:null});

// Load SCeditor
var defaultSceditorHeight = 400;
sceditor.sceditor({
    plugins: "bbcode",

    height: defaultSceditorHeight,
    resizeWidth: false,

    enablePasteFiltering: true,
    parserOptions: {
        breakBeforeBlock: false,
        breakAfterBlock: false,
        breakStartBlock: false,
        breakEndBlock: false,
    },
    style: '/assets/min/etuutt.css',
    emoticonsRoot: '/',
    locale: 'fr',
    toolbar:
        "bold,italic,strike,removeformat|left,center,right,justify" +
            "|bulletlist,orderedlist|link,unlink|emoticon,image,video" +
            "|headers,horizontalrule,abbreviation,float,alert,note,table,quote|code,var|latex|source,maximize",
    emoticons: {
        dropdown: {
            ">:(": "assets/img/emoticons/angry.png",
            ":aw:": "assets/img/emoticons/aw.png",
            "8)": "assets/img/emoticons/cool.png",
            ":D": "assets/img/emoticons/ecstatic.png",
            ">:D": "assets/img/emoticons/furious.png",
            ":O": "assets/img/emoticons/gah.png",
            ":)": "assets/img/emoticons/happy.png",
            "<3": "assets/img/emoticons/heart.png",
            ":/ ": "assets/img/emoticons/hm.png",
            ":3": "assets/img/emoticons/kiss.png",
            ":|": "assets/img/emoticons/meh.png",
            ":x": "assets/img/emoticons/mmf.png",
            ":(": "assets/img/emoticons/sad.png",
            ":P": "assets/img/emoticons/tongue.png",
            ":o": "assets/img/emoticons/what.png",
            ";)": "assets/img/emoticons/wink.png"
        },
        hidden: {
            ">:[": "assets/img/emoticons/angry.png",
            "8]": "assets/img/emoticons/cool.png",
            "D:": "assets/img/emoticons/gah.png",
            ":]": "assets/img/emoticons/happy.png",
            ":\\": "assets/img/emoticons/hm.png",
            "-.-": "assets/img/emoticons/meh.png",
            "-_-": "assets/img/emoticons/meh.png",
            ":X": "assets/img/emoticons/mmf.png",
            ":[": "assets/img/emoticons/sad.png",
            ":\'(": "assets/img/emoticons/sad.png",
            ":\'[": "assets/img/emoticons/sad.png",
            ":p": "assets/img/emoticons/tongue.png",
            ":?": "assets/img/emoticons/what.png",
            ";]": "assets/img/emoticons/wink.png",
            ";D": "assets/img/emoticons/wink.png"
        }
    }
});

// Load SCeditor limited
sceditorLimited.sceditor({
    plugins: "bbcode",
    height: defaultSceditorHeight,
    style: "/vendor/SCEditor/minified/jquery.sceditor.default.min.css",
    emoticonsRoot: '/',
    toolbar:
        "source|bold,italic,underline,strike|left,center,right,justify|link,unlink|maximize",
    emoticons: {
        dropdown: {
            ">:(": "assets/img/emoticons/angry.png",
            ":aw:": "assets/img/emoticons/aw.png",
            "8)": "assets/img/emoticons/cool.png",
            ":D": "assets/img/emoticons/ecstatic.png",
            ">:D": "assets/img/emoticons/furious.png",
            ":O": "assets/img/emoticons/gah.png",
            ":)": "assets/img/emoticons/happy.png",
            "<3": "assets/img/emoticons/heart.png",
            ":/": "assets/img/emoticons/hm.png",
            ":3": "assets/img/emoticons/kiss.png",
            ":|": "assets/img/emoticons/meh.png",
            ":x": "assets/img/emoticons/mmf.png",
            ":(": "assets/img/emoticons/sad.png",
            ":P": "assets/img/emoticons/tongue.png",
            ":o": "assets/img/emoticons/what.png",
            ";)": "assets/img/emoticons/wink.png"
        },
        hidden: {
            ">:[": "assets/img/emoticons/angry.png",
            "8]": "assets/img/emoticons/cool.png",
            "D:": "assets/img/emoticons/gah.png",
            ":]": "assets/img/emoticons/happy.png",
            ":\\": "assets/img/emoticons/hm.png",
            "-.-": "assets/img/emoticons/meh.png",
            "-_-": "assets/img/emoticons/meh.png",
            ":X": "assets/img/emoticons/mmf.png",
            ":[": "assets/img/emoticons/sad.png",
            ":\'(": "assets/img/emoticons/sad.png",
            ":\'[": "assets/img/emoticons/sad.png",
            ":p": "assets/img/emoticons/tongue.png",
            ":?": "assets/img/emoticons/what.png",
            ";]": "assets/img/emoticons/wink.png",
            ";D": "assets/img/emoticons/wink.png"
        }
    }
});

// Load SCeditor html
sceditorHTML.sceditor({

    height: defaultSceditorHeight,
    resizeWidth: false,

    enablePasteFiltering: true,
    parserOptions: {
        breakBeforeBlock: false,
        breakAfterBlock: false,
        breakStartBlock: false,
        breakEndBlock: false,
    },
    style: '/min/etuutt.css',
    emoticonsRoot: '/',
    locale: 'fr',
    toolbar:
        "bold,italic,strike,removeformat|left,center,right,justify" +
            "|bulletlist,orderedlist|link,unlink|emoticon,image,video" +
            "|headers,horizontalrule,abbreviation,float,alert,note,table,quote|code,var|latex|source,maximize",
    emoticons: {
        dropdown: {
            ">:(": "assets/img/emoticons/angry.png",
            ":aw:": "assets/img/emoticons/aw.png",
            "8)": "assets/img/emoticons/cool.png",
            ":D": "assets/img/emoticons/ecstatic.png",
            ">:D": "assets/img/emoticons/furious.png",
            ":O": "assets/img/emoticons/gah.png",
            ":)": "assets/img/emoticons/happy.png",
            "<3": "assets/img/emoticons/heart.png",
            ":/": "assets/img/emoticons/hm.png",
            ":3": "assets/img/emoticons/kiss.png",
            ":|": "assets/img/emoticons/meh.png",
            ":x": "assets/img/emoticons/mmf.png",
            ":(": "assets/img/emoticons/sad.png",
            ":P": "assets/img/emoticons/tongue.png",
            ":o": "assets/img/emoticons/what.png",
            ";)": "assets/img/emoticons/wink.png"
        },
        hidden: {
            ">:[": "assets/img/emoticons/angry.png",
            "8]": "assets/img/emoticons/cool.png",
            "D:": "assets/img/emoticons/gah.png",
            ":]": "assets/img/emoticons/happy.png",
            ":\\": "assets/img/emoticons/hm.png",
            "-.-": "assets/img/emoticons/meh.png",
            "-_-": "assets/img/emoticons/meh.png",
            ":X": "assets/img/emoticons/mmf.png",
            ":[": "assets/img/emoticons/sad.png",
            ":\'(": "assets/img/emoticons/sad.png",
            ":\'[": "assets/img/emoticons/sad.png",
            ":p": "assets/img/emoticons/tongue.png",
            ":?": "assets/img/emoticons/what.png",
            ";]": "assets/img/emoticons/wink.png",
            ";D": "assets/img/emoticons/wink.png"
        }
    }
});

// Resize sceditors on window resize, because width: 100% doesn't work properlyv
var sceditorAutoresize = function() {
    var win = $(this);
    sceditor.each(function() {
        var width = win.width()/2;
        var height = defaultSceditorHeight;
        sceditor.parent().width('99%');
        sceditor.sceditor('instance').width(sceditor.parent().width());
        sceditor.sceditor('instance').height(height);
    })
}
sceditorAutoresize();
$(window).on('resize', function(){
    sceditorAutoresize();
});


// Add bootstrap class on automatic bbcode classes with unmodifiable templates
$('.decoda-alert').addClass('alert');
$('.decoda-note').addClass('alert alert-info');
$('.decoda-spoiler').addClass('accordion-group');
$('.decoda-spoiler-button').addClass('accordion-heading accordion-toggle');
$('.decoda-spoiler-content').addClass('accordion-body accordion-inner');
$('.decoda-code').each(function() {
    var result = $(this).attr('class').match(/lang-([a-z0-9\+-]+)/);
    if(result && result.length >= 2) {
        $(this).children('code').addClass(result[1]);
    }
});
$('.bbcode').find('img').each(function(){
    if($(this).attr('width')) {
        $(this).css('width', $(this).attr('width')+'px');
    }
    if($(this).attr('height')) {
        $(this).css('height', $(this).attr('height')+'px');
    }
});
$(".decoda-quote-author").each(function(){
    $(this).replaceWith('<small>' + $(this).html() +'</small>')
});
$(".decoda-quote-body").each(function(){
    $(this).replaceWith('<p>' + $(this).html() +'</p>')
});
$('.decoda-table').addClass('table table-striped table-responsive table-bordered');

// Update video height according to width
$(window).resize(function(){
    $('.bbcode iframe, .video-js').each(function() {
        $(this).height($(this).width() * (315/560));
    });
});
$('.bbcode iframe, .video-js').each(function() {
    $(this).height($(this).width() * (315/560));
});

// Mathjax configuration
MathJax.Hub.Config({
  tex2jax: {
    processClass: 'mathjax',
    ignoreClass: 'no-mathjax'
  }
});
MathJax.Hub.Register.MessageHook("Math Processing Error",function (message) {
    console.log(message)
});
