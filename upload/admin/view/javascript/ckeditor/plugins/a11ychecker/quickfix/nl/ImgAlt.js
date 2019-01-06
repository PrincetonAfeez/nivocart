﻿/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"nl",name:"QuickFix",callback:function(d){function a(a){d.call(this,a)}var f=/^[\s\n\r]+$/g;a.altLengthLimit=100;a.prototype=new d;a.prototype.constructor=a;a.prototype.display=function(a){a.setInputs({alt:{type:"text",label:this.lang.altLabel,value:this.issue.element.getAttribute("alt")||""}})};a.prototype.fix=function(a,b){this.issue.element.setAttribute("alt",a.alt);b&&b(this)};a.prototype.validate=function(c){var b=[];c=c.alt+"";
var d=this.issue&&this.issue.element,e=this.lang;c.match(f)&&b.push(e.errorWhitespace);if(a.altLengthLimit&&c.length>a.altLengthLimit){var g=new CKEDITOR.template(e.errorTooLong);b.push(g.output({limit:a.altLengthLimit,length:c.length}))}d&&String(d.getAttribute("src")).split("/").pop()==c&&b.push(e.errorSameAsFileName);return b};a.prototype.lang={altLabel:"Alternatieve tekst",errorTooLong:"Alternatieve tekst is te lang. Deze mag maximaal {limit} karaktersbevatten terwijl opgegeven tekst {length} bevat",
errorWhitespace:"Alternatieve tekst mag niet alleen uit spaties bestaan",errorSameAsFileName:"Alt-tekst van de afbeelding mag niet hetzelfde zijn als de bestandsnaam"};CKEDITOR.plugins.a11ychecker.quickFixes.add("nl/ImgAlt",a)}})})();
