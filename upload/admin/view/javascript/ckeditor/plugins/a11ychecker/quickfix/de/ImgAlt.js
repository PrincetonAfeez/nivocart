﻿/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"de",name:"QuickFix",callback:function(d){function a(a){d.call(this,a)}var f=/^[\s\n\r]+$/g;a.altLengthLimit=100;a.prototype=new d;a.prototype.constructor=a;a.prototype.display=function(a){a.setInputs({alt:{type:"text",label:this.lang.altLabel,value:this.issue.element.getAttribute("alt")||""}})};a.prototype.fix=function(a,b){this.issue.element.setAttribute("alt",a.alt);b&&b(this)};a.prototype.validate=function(c){var b=[];c=c.alt+"";
var d=this.issue&&this.issue.element,e=this.lang;c.match(f)&&b.push(e.errorWhitespace);if(a.altLengthLimit&&c.length>a.altLengthLimit){var g=new CKEDITOR.template(e.errorTooLong);b.push(g.output({limit:a.altLengthLimit,length:c.length}))}d&&String(d.getAttribute("src")).split("/").pop()==c&&b.push(e.errorSameAsFileName);return b};a.prototype.lang={altLabel:"Alternativtext",errorTooLong:"Der Alternativtext ist zu lang. Er sollte {limit} Zeichen lang sein, ist aber aktuell {length} Zeichen lang",errorWhitespace:"Der Alternativtext kann nicht nur Leerzeichen enthalten",
errorSameAsFileName:"Der Alternativtext sollte nicht dem Dateinamen entsprechen"};CKEDITOR.plugins.a11ychecker.quickFixes.add("de/ImgAlt",a)}})})();
