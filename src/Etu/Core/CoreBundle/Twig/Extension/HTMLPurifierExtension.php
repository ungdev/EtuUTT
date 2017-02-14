<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class HTMLPurifierExtension extends \Twig_Extension
{
    private $container;

    private $purifiers = [];

    /**
     * Constructor.
     *
     * @param \HTMLPurifier $purifier
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @see Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('purify', [$this, 'purify'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Filter the input through an HTMLPurifier service.
     *
     * @param string $string
     * @param string $profile
     *
     * @return string
     */
    public function purify($string, $profile = 'default')
    {
        if ($profile == 'email') {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
            $config->set('Cache.SerializerPath', '/tmp');
            // set from param:
            $config->set('HTML.Allowed', 'h3,h4,h5,h6,b,i,strong,em,p[style|align],li,ul,ol,img[src|alt],a[href|title|target|rel]');
            $config->set('CSS.AllowedProperties', 'width,height,text-align,padding-left,max-width,max-height');
            $config->set('Attr.AllowedClasses', '');
        }
        // default profile
        else {
            // Load HTML5 purifier config
            $config = load_htmlpurifier([
                'HTML.Allowed' => 'h2[id],h3[id],h4[id],h5[id],h6[id],pre[class],code,div[class],strong,em,p[style],li,ul,ol,img[src|alt],a[href|title|target|rel],video[controls|width|height],source[src|type],table[class|style],tbody,tr,td,iframe[src|width|height|allowfullscreen]',
                'CSS.AllowedProperties' => 'width,height,text-align,padding-left,max-width,max-height',
                'Attr.AllowedClasses' => 'alert,alert-error,alert-warning,alert-info,alert-success,pull-left,pull-right,mce-toc,table,table-bordered,'
                    .'language-auto,language-nohighlight,language-1c,language-abnf,language-accesslog,language-ada,language-armasm,language-avrasm,language-actionscript,language-apache,language-applescript,language-asciidoc,language-aspectj,language-autohotkey,language-autoit,language-awk,language-axapta,language-bash,language-basic,language-bnf,language-brainfuck,language-cs,language-cpp,language-cal,language-cos,language-cmake,language-coq,language-csp,language-css,language-capnproto,language-clojure,language-coffeescript,language-crmsh,language-crystal,language-d,language-dns,language-dos,language-dart,language-delphi,language-lazarus,language-diff,language-django,language-dockerfile,language-dsconfig,language-dts,language-dust,language-ebnf,language-elixir,language-elm,language-erlang,language-excel,language-fsharp,language-fix,language-fortran,language-gcode,language-gams,language-gauss,language-gherkin,language-go,language-golo,language-gradle,language-groovy,language-xml,language-http,language-haml,language-handlebars,language-haskell,language-haxe,language-ini,language-inform7,language-irpf90,language-json,language-java,language-javascript,language-lasso,language-less,language-ldif,language-lisp,language-livecodeserver,language-livescript,language-lua,language-makefile,language-markdown,language-mathematica,language-matlab,language-maxima,language-mel,language-mercury,language-mizar,language-mojolicious,language-monkey,language-moonscript,language-nsis,language-nginx,language-nimrod,language-nix,language-ocaml,language-objectivec,language-glsl,language-openscad,language-ruleslanguage,language-oxygene,language-pf,language-php,language-parser3,language-perl,language-pony,language-powershell,language-processing,language-prolog,language-protobuf,language-puppet,language-python,language-profile,language-k,language-qml,language-r,language-rib,language-rsl,language-graph,language-ruby,language-rust,language-scss,language-sql,language-p21,language-scala,language-scheme,language-scilab,language-smali,language-smalltalk,language-stan,language-stata,language-stylus,language-subunit,language-swift,language-tap,language-tcl,language-tex,language-thrift,language-tp,language-twig,language-typescript,language-vbnet,language-vbscript,language-vhdl,language-vala,language-verilog,language-vim,language-x86asm,language-xl,language-xpath,language-zephir',
                'Attr.EnableID' => true,
                'Attr.IDBlacklistRegexp' => '/^(?!mcetoc_)/',
            ]);
        }

        // Purify html
        $string = (new \HTMLPurifier($config))->purify($string);

        // Additionnal rules
        if ($profile == 'email') {
            // Add max-width:100% for all images on emails
            $string = str_replace('<img', '<img style="max-width:100%;"', $string);
        }

        return $string;
    }

    /**
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'html_purifier';
    }
}

/**
 * Load HTMLPurifier with HTML5, TinyMCE, YouTube, Video support.
 *
 * Copyright 2014 Alex Kennberg (https://github.com/kennberg/php-htmlpurifier-html5)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @param mixed $setArray
 */
function load_htmlpurifier($setArray)
{
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
    $config->set('Cache.SerializerPath', '/tmp');
    // set from param:
    foreach ($setArray as $key => $value) {
        $config->set($key, $value);
    }
    // Allow iframes from:
    // o YouTube.com
    // o Vimeo.com
    $config->set('HTML.SafeIframe', true);
    $config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
    // Set some HTML5 properties
    $config->set('HTML.DefinitionID', 'html5-definitions'); // unqiue id
    $config->set('HTML.DefinitionRev', 1);
    if ($def = $config->maybeGetRawHTMLDefinition()) {
        // http://developers.whatwg.org/sections.html
        $def->addElement('section', 'Block', 'Flow', 'Common');
        $def->addElement('nav', 'Block', 'Flow', 'Common');
        $def->addElement('article', 'Block', 'Flow', 'Common');
        $def->addElement('aside', 'Block', 'Flow', 'Common');
        $def->addElement('header', 'Block', 'Flow', 'Common');
        $def->addElement('footer', 'Block', 'Flow', 'Common');
        // Content model actually excludes several tags, not modelled here
        $def->addElement('address', 'Block', 'Flow', 'Common');
        $def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
        // http://developers.whatwg.org/grouping-content.html
        $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
        $def->addElement('figcaption', 'Inline', 'Flow', 'Common');
        // http://developers.whatwg.org/the-video-element.html#the-video-element
        $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
            'src' => 'URI',
            'type' => 'Text',
            'width' => 'Length',
            'height' => 'Length',
            'poster' => 'URI',
            'preload' => 'Enum#auto,metadata,none',
            'controls' => 'Bool',
        ]);
        $def->addElement('source', 'Block', 'Flow', 'Common', [
            'src' => 'URI',
            'type' => 'Text',
        ]);
        // http://developers.whatwg.org/text-level-semantics.html
        $def->addElement('s', 'Inline', 'Inline', 'Common');
        $def->addElement('var', 'Inline', 'Inline', 'Common');
        $def->addElement('sub', 'Inline', 'Inline', 'Common');
        $def->addElement('sup', 'Inline', 'Inline', 'Common');
        $def->addElement('mark', 'Inline', 'Inline', 'Common');
        $def->addElement('wbr', 'Inline', 'Empty', 'Core');
        // http://developers.whatwg.org/edits.html
        $def->addElement('ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']);
        $def->addElement('del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']);
        // TinyMCE
        $def->addAttribute('img', 'data-mce-src', 'Text');
        $def->addAttribute('img', 'data-mce-json', 'Text');
        // Others
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('table', 'height', 'Text');
        $def->addAttribute('td', 'border', 'Text');
        $def->addAttribute('th', 'border', 'Text');
        $def->addAttribute('tr', 'width', 'Text');
        $def->addAttribute('tr', 'height', 'Text');
        $def->addAttribute('tr', 'border', 'Text');
    }

    return $config;
}
