!function(e){function n(r){if(t[r])return t[r].exports;var a=t[r]={exports:{},id:r,loaded:!1};return e[r].call(a.exports,a,a.exports,n),a.loaded=!0,a.exports}var t={};return n.m=e,n.c=t,n.p="",n(0)}([function(e,n){"use strict";function t(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}function r(){new i}var a=function(){function e(e,n){for(var t=0;t<n.length;t++){var r=n[t];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(n,t,r){return t&&e(n.prototype,t),r&&e(n,r),n}}(),i=function(){function e(){t(this,e),this.headings=document.getElementsByClassName("abt_PR_heading");for(var n=0;n<this.headings.length;n++){var r=this.headings[n],a=r.nextElementSibling;a.style.display="none",r.addEventListener("click",this._clickHandler)}}return a(e,[{key:"_clickHandler",value:function(e){var n=e.target.nextSibling;if("none"!==n.style.display)return void(n.style.display="none");for(var t=e.target.parentElement.children,r=0;r<t.length;r++){var a=t[r];"DIV"===a.tagName&&(a.previousSibling!==e.target?a.style.display="none":a.style.display="")}}}]),e}();"interactive"===document.readyState?r():document.addEventListener("DOMContentLoaded",r)}]);