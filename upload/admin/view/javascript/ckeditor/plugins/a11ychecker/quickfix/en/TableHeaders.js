﻿/*
 Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or https://ckeditor.com/license
*/
(function(){CKEDITOR.plugins.a11ychecker.quickFixes.get({langCode:"en",name:"QuickFix",callback:function(g){function a(a){g.call(this,a)}a.prototype=new g;a.prototype.constructor=a;a.prototype.display=function(a){var d=this.lang;a.setInputs({position:{type:"select",label:d.positionLabel,value:"row",options:{both:d.positionBoth,row:d.positionHorizontally,col:d.positionVertically}}})};a.prototype.fix=function(a,d){var b=this.issue.element,f=a.position,e,c;if("col"==f||"both"==f)for(c=0;c<b.$.rows.length;c++)b.$.rows[c].cells.length&&
(e=new CKEDITOR.dom.element(b.$.rows[c].cells[0]),e.renameNode("th"),e.setAttribute("scope","row"));if(!b.$.tHead&&("row"==f||"both"==f)){f=new CKEDITOR.dom.element(b.$.createTHead());e=b.getElementsByTag("tbody").getItem(0).getElementsByTag("tr").getItem(0);for(b=0;b<e.getChildCount();b++)c=e.getChild(b),c.type!=CKEDITOR.NODE_ELEMENT||c.data("cke-bookmark")||(c.renameNode("th"),c.setAttribute("scope","col"));f.append(e.remove())}d&&d(this)};a.prototype.lang={positionLabel:"Position",positionHorizontally:"Horizontally",
positionVertically:"Vertically",positionBoth:"Both"};CKEDITOR.plugins.a11ychecker.quickFixes.add("en/TableHeaders",a)}})})();
