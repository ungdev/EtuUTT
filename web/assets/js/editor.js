// Remove required from editors
$('.editor, .editor-email').removeAttr('required');

// Convert video to video-js
$('video').addClass('video-js')
    .css('width', '100%')
    .css('height', 'auto')
    .css('max-height', '500px');
$('iframe')
    .css('max-width', '100%');

// File manager popup callback
var editorNamespace = {};
editorNamespace.editorFileField = null;
editorNamespace.popup = null;
window.selectEditorFile = function(filePath) {
    editorNamespace.editorFileField.val(filePath);
    editorNamespace.popup.close();
};


// Default editor
tinymce.init({
    selector: '.editor',
    menubar: false,
    plugins: [
        'autolink autoresize autosave charmap code codesample emoticons fullscreen hr   lists',
        'image imagetools link media paste searchreplace table textpattern toc visualblocks visualchars wordcount'
    ],
    toolbar2: 'formatselect styleselect | bold italic str1ikethrough removeformat | alignleft aligncenter alignright alignjustify '
        + '| bullist numlist outdent indent | charmap emoticons | codesample hr toc blockquote | image link media | table',
    toolbar1: ' undo redo restoredraft | copy cut paste | searchreplace code | visualblocks visualchars',
    content_css: '/assets/min/etuutt.css',

    block_formats: 'Titre 1=h2;Titre 2=h3;Titre 3=h4;Titre 4=h5;Titre 5=h6;Paragraph=p;Preformatted=pre;',

    style_formats: [
        { title: 'Bandeau : Erreur !', block: 'div', classes: 'alert alert-error' },
        { title: 'Bandeau : Attention !', block: 'div', classes: 'alert alert-warning' },
        { title: 'Bandeau : Info', block: 'div', classes: 'alert alert-info' },
        { title: 'Bandeau : Succès !', block: 'div', classes: 'alert alert-success' },
        { title: 'Code : Variable', inline: 'code' },
        { title: 'Flotant droit', block: 'div', classes: 'pull-right' },
        { title: 'Flotant gauche', block: 'div', classes: 'pull-left' },
    ],
    relative_urls: false,
    remove_script_host: true,
    paste_data_images: true,
    autoresize_min_height: 100,
    autoresize_max_height: 500,
    autoresize_bottom_margin: 20,
    autosave_retention: "120m",
    image_caption: true,
    link_assume_external_targets: false,
    link_title: false,
    browser_spellcheck: true,
    textpattern_patterns: [
        {start: '*', end: '*', format: 'italic'},
        {start: '**', end: '**', format: 'bold'},
        {start: '#', format: 'h1'},
        {start: '##', format: 'h2'},
        {start: '###', format: 'h3'},
        {start: '####', format: 'h4'},
        {start: '#####', format: 'h5'},
        {start: '######', format: 'h6'},
        {start: '1. ', cmd: 'InsertOrderedList'},
        {start: '* ', cmd: 'InsertUnorderedList'},
        {start: '- ', cmd: 'InsertUnorderedList'}
    ],
    file_browser_callback: function(field_name, url, type, win) {
        editorNamespace.editorFileField = $('#'+field_name);
        var orga = $('#'+tinyMCE.activeEditor.id).data('organization');
        orga = (orga) ? orga : null;
        editorNamespace.popup = window.open(Routing.generate('upload_index', {'organization': orga}), '', 'width=1000, height=700, top='+((screen.height/2)-(700/2))+', left='+((screen.width/2)-(1000/2))+', toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, copyhistory=no, resizable=yes');
    },
    automatic_uploads: true,
    images_upload_url: Routing.generate('upload_editor', {'organization': null}),
    images_reuse_filename: true,
    imagetools_proxy: Routing.generate('upload_imageproxy'),
    setup: function (editor) {
        // Edit uploading link according to data-organization attr
        var orga = $('#'+editor.id).data('organization');
        editor.settings.images_upload_url = Routing.generate('upload_editor', {'organization': orga});

        // Edit link list url according to organization
        editor.settings.link_list = Routing.generate('wiki_linklist', {'organization': orga});
    },
    codesample_languages: [
        {text: 'Auto', value: 'auto'},
        {text: 'Plain text', value: 'nohighlight'},
        {text: '1C', value: '1c'},
        {text: 'ABNF', value: 'abnf'},
        {text: 'Access logs', value: 'accesslog'},
        {text: 'Ada', value: 'ada'},
        {text: 'ARM assembler', value: 'armasm'},
        {text: 'AVR assembler', value: 'avrasm'},
        {text: 'ActionScript', value: 'actionscript'},
        {text: 'Apache', value: 'apache'},
        {text: 'AppleScript', value: 'applescript'},
        {text: 'AsciiDoc', value: 'asciidoc'},
        {text: 'AspectJ', value: 'aspectj'},
        {text: 'AutoHotkey', value: 'autohotkey'},
        {text: 'AutoIt', value: 'autoit'},
        {text: 'Awk', value: 'awk'},
        {text: 'Axapta', value: 'axapta'},
        {text: 'Bash', value: 'bash'},
        {text: 'Basic', value: 'basic'},
        {text: 'BNF', value: 'bnf'},
        {text: 'Brainfuck', value: 'brainfuck'},
        {text: 'C#', value: 'cs'},
        {text: 'C++', value: 'cpp'},
        {text: 'C/AL', value: 'cal'},
        {text: 'Cache Object Script', value: 'cos'},
        {text: 'CMake', value: 'cmake'},
        {text: 'Coq', value: 'coq'},
        {text: 'CSP', value: 'csp'},
        {text: 'CSS', value: 'css'},
        {text: 'Cap’n Proto', value: 'capnproto'},
        {text: 'Clojure', value: 'clojure'},
        {text: 'CoffeeScript', value: 'coffeescript'},
        {text: 'Crmsh', value: 'crmsh'},
        {text: 'Crystal', value: 'crystal'},
        {text: 'D', value: 'd'},
        {text: 'DNS Zone file', value: 'dns'},
        {text: 'DOS', value: 'dos'},
        {text: 'Dart', value: 'dart'},
        {text: 'Delphi', value: 'delphi'},
        {text: 'Lazarus', value: 'lazarus'},
        {text: 'Diff', value: 'diff'},
        {text: 'Django', value: 'django'},
        {text: 'Dockerfile', value: 'dockerfile'},
        {text: 'dsconfig', value: 'dsconfig'},
        {text: 'DTS (Device Tree)', value: 'dts'},
        {text: 'Dust', value: 'dust'},
        {text: 'EBNF', value: 'ebnf'},
        {text: 'Elixir', value: 'elixir'},
        {text: 'Elm', value: 'elm'},
        {text: 'Erlang', value: 'erlang'},
        {text: 'Excel', value: 'excel'},
        {text: 'F#', value: 'fsharp'},
        {text: 'FIX', value: 'fix'},
        {text: 'Fortran', value: 'fortran'},
        {text: 'G-Code', value: 'gcode'},
        {text: 'Gams', value: 'gams'},
        {text: 'GAUSS', value: 'gauss'},
        {text: 'Gherkin', value: 'gherkin'},
        {text: 'Go', value: 'go'},
        {text: 'Golo', value: 'golo'},
        {text: 'Gradle', value: 'gradle'},
        {text: 'Groovy', value: 'groovy'},
        {text: 'HTML, XML', value: 'xml'},
        {text: 'HTTP', value: 'http'},
        {text: 'Haml', value: 'haml'},
        {text: 'Handlebars', value: 'handlebars'},
        {text: 'Haskell', value: 'haskell'},
        {text: 'Haxe', value: 'haxe'},
        {text: 'Ini', value: 'ini'},
        {text: 'Inform7', value: 'inform7'},
        {text: 'IRPF90', value: 'irpf90'},
        {text: 'JSON', value: 'json'},
        {text: 'Java', value: 'java'},
        {text: 'JavaScript', value: 'javascript'},
        {text: 'Lasso', value: 'lasso'},
        {text: 'Less', value: 'less'},
        {text: 'LDIF', value: 'ldif'},
        {text: 'Lisp', value: 'lisp'},
        {text: 'LiveCode Server', value: 'livecodeserver'},
        {text: 'LiveScript', value: 'livescript'},
        {text: 'Lua', value: 'lua'},
        {text: 'Makefile', value: 'makefile'},
        {text: 'Markdown', value: 'markdown'},
        {text: 'Mathematica', value: 'mathematica'},
        {text: 'Matlab', value: 'matlab'},
        {text: 'Maxima', value: 'maxima'},
        {text: 'Maya Embedded Language', value: 'mel'},
        {text: 'Mercury', value: 'mercury'},
        {text: 'Mizar', value: 'mizar'},
        {text: 'Mojolicious', value: 'mojolicious'},
        {text: 'Monkey', value: 'monkey'},
        {text: 'Moonscript', value: 'moonscript'},
        {text: 'NSIS', value: 'nsis'},
        {text: 'Nginx', value: 'nginx'},
        {text: 'Nimrod', value: 'nimrod'},
        {text: 'Nix', value: 'nix'},
        {text: 'OCaml', value: 'ocaml'},
        {text: 'Objective C', value: 'objectivec'},
        {text: 'OpenGL Shading Language', value: 'glsl'},
        {text: 'OpenSCAD', value: 'openscad'},
        {text: 'Oracle Rules Language', value: 'ruleslanguage'},
        {text: 'Oxygene', value: 'oxygene'},
        {text: 'PF', value: 'pf'},
        {text: 'PHP', value: 'php'},
        {text: 'Parser3', value: 'parser3'},
        {text: 'Perl', value: 'perl'},
        {text: 'Pony', value: 'pony'},
        {text: 'PowerShell', value: 'powershell'},
        {text: 'Processing', value: 'processing'},
        {text: 'Prolog', value: 'prolog'},
        {text: 'Protocol Buffers', value: 'protobuf'},
        {text: 'Puppet', value: 'puppet'},
        {text: 'Python', value: 'python'},
        {text: 'Python profiler results ', value: 'profile'},
        {text: 'Q', value: 'k'},
        {text: 'QML', value: 'qml'},
        {text: 'R', value: 'r'},
        {text: 'RenderMan RIB', value: 'rib'},
        {text: 'RenderMan RSL', value: 'rsl'},
        {text: 'Roboconf', value: 'graph'},
        {text: 'Ruby', value: 'ruby'},
        {text: 'Rust', value: 'rust'},
        {text: 'SCSS', value: 'scss'},
        {text: 'SQL', value: 'sql'},
        {text: 'STEP Part 21', value: 'p21'},
        {text: 'Scala', value: 'scala'},
        {text: 'Scheme', value: 'scheme'},
        {text: 'Scilab', value: 'scilab'},
        {text: 'Smali', value: 'smali'},
        {text: 'Smalltalk', value: 'smalltalk'},
        {text: 'Stan', value: 'stan'},
        {text: 'Stata', value: 'stata'},
        {text: 'Stylus', value: 'stylus'},
        {text: 'SubUnit', value: 'subunit'},
        {text: 'Swift', value: 'swift'},
        {text: 'Test Anything Protocol', value: 'tap'},
        {text: 'Tcl', value: 'tcl'},
        {text: 'TeX', value: 'tex'},
        {text: 'Thrift', value: 'thrift'},
        {text: 'TP', value: 'tp'},
        {text: 'Twig', value: 'twig'},
        {text: 'TypeScript', value: 'typescript'},
        {text: 'VB.Net', value: 'vbnet'},
        {text: 'VBScript', value: 'vbscript'},
        {text: 'VHDL', value: 'vhdl'},
        {text: 'Vala', value: 'vala'},
        {text: 'Verilog', value: 'verilog'},
        {text: 'Vim Script', value: 'vim'},
        {text: 'x86 Assembly', value: 'x86asm'},
        {text: 'XL', value: 'xl'},
        {text: 'XQuery', value: 'xpath'},
        {text: 'Zephir', value: 'zephir'},
    ],
    table_default_attributes: {
        class: 'table table-bordered'
    },
    table_default_styles: {
        width: 'auto'
    },
    language_url: '/assets/js/TinyMCE/lang/fr_FR.js',
});




// Email editor
// Use : legacyoutput
//relative_urls: false,
//  remove_script_host: false,



tinymce.init({
    selector: '.editor-email',
    menubar: false,
    plugins: [
        'autolink autoresize autosave charmap code emoticons fullscreen hr lists',
        'image imagetools link paste searchreplace textpattern visualblocks visualchars wordcount'
    ],
    toolbar2: 'formatselect | bold italic str1ikethrough removeformat | alignleft aligncenter alignright alignjustify '
    + '| bullist numlist outdent indent | charmap emoticons | hr | image link',
    toolbar1: ' undo redo restoredraft | copy cut paste | fullscreen searchreplace code | visualblocks visualchars',

    block_formats: 'Titre 1=h3;Titre 2=h4;Titre 3=h5;Titre 4=h6;Paragraph=p;',
    relative_urls: false,
    remove_script_host: false,
    paste_data_images: true,
    autoresize_min_height: 100,
    autoresize_max_height: 500,
    autoresize_bottom_margin: 20,
    autosave_retention: "120m",
    image_caption: true,
    link_assume_external_targets: false,
    link_title: false,
    browser_spellcheck: true,
    content_style: 'img {max-width: 100%; max-height:400px;}',

    textpattern_patterns: [
        {start: '*', end: '*', format: 'italic'},
        {start: '**', end: '**', format: 'bold'},
        {start: '#', format: 'h1'},
        {start: '##', format: 'h2'},
        {start: '###', format: 'h3'},
        {start: '####', format: 'h4'},
        {start: '#####', format: 'h5'},
        {start: '######', format: 'h6'},
        {start: '1. ', cmd: 'InsertOrderedList'},
        {start: '* ', cmd: 'InsertUnorderedList'},
        {start: '- ', cmd: 'InsertUnorderedList'}
    ],
    file_browser_callback: function(field_name, url, type, win) {
        editorNamespace.editorFileField = $('#'+field_name);
        var orga = $('#'+tinyMCE.activeEditor.id).data('organization');
        orga = (orga) ? orga : null;
        editorNamespace.popup = window.open(Routing.generate('upload_index', {'organization': orga}), '', 'width=1000, height=700, top='+((screen.height/2)-(700/2))+', left='+((screen.width/2)-(1000/2))+', toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, copyhistory=no, resizable=yes');
    },
    automatic_uploads: true,
    images_upload_url: Routing.generate('upload_editor', {'organization': null}),
    images_reuse_filename: true,
    imagetools_proxy: Routing.generate('upload_imageproxy'),
    setup: function (editor) {
        // Edit uploading link according to data-organization attr
        var orga = $('#'+editor.id).data('organization');
        editor.settings.images_upload_url = Routing.generate('upload_editor', {'organization': orga});
    },
    language_url: '/assets/js/TinyMCE/lang/fr_FR.js',
});
