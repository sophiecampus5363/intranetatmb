!function(t){function e(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,e),o.l=!0,o.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="",e(e.s=0)}([function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});n(1)},function(t,e,n){"use strict";function r(t){return function(){var e=t.apply(this,arguments);return new Promise(function(t,n){function r(o,i){try{var a=e[o](i),s=a.value}catch(t){return void n(t)}if(!a.done)return Promise.resolve(s).then(function(t){r("next",t)},function(t){r("throw",t)});t(s)}return r("next")})}}var o=n(2),i=n.n(o),a=n(5),s=(n.n(a),n(6)),l=(n.n(s),wp.i18n.__),c=wp.blocks.registerBlockType,u=wp.components,p=(u.SelectControl,u.TextControl,wp.editor.RichText,!1);c("opinion-stage/block-os-poll",{title:l("Poll"),icon:"chart-bar",category:"opinion-stage",keywords:[l("Opinion Stage Poll"),l("Opinion Stage Poll")],attributes:{embedUrl:{source:"attribute",attribute:"data-test-url",selector:"div[data-test-url]"},lockEmbed:{source:"attribute",attribute:"data-lock-embed",selector:"div[data-lock-embed]"},buttonText:{source:"attribute",attribute:"data-button-text",selector:"div[data-button-text]"},insertItemImage:{source:"attribute",attribute:"data-image-url",selector:"div[data-image-url]"},insertItemOsTitle:{source:"attribute",attribute:"data-title-url",selector:"div[data-title-url]"},insertItemOsView:{source:"attribute",attribute:"data-view-url",selector:"div[data-view-url]"},insertItemOsEdit:{source:"attribute",attribute:"data-edit-url",selector:"div[data-edit-url]"},insertItemOsStatistics:{source:"attribute",attribute:"data-statistics-url",selector:"div[data-statistics-url]"}},edit:function(t){var e=t.attributes,n=e.embedUrl,o=(e.lockEmbed,e.buttonText),a=e.insertItemImage,s=e.insertItemOsTitle,l=e.insertItemOsView,c=e.insertItemOsEdit,u=e.insertItemOsStatistics,m=(t.setAttributes,osGutenData.callbackUrlOs),d=m,f=(osGutenData.getActionUrlOS,osGutenData.getLogoImageLink),h=function(e){window.verifyOSInsert=function(e){var n=this;t.setAttributes({embedUrl:e,buttonText:"Change"});var a=osGutenData.OswpPluginVersion,s=osGutenData.OswpClientToken,l=osGutenData.OswpFetchDataUrl+"?type=poll&page=1&per_page=99";fetch(l,{method:"GET",headers:{Accept:"application/vnd.api+json","Content-Type":"application/vnd.api+json","OSWP-Plugin-Version":a,"OSWP-Client-Token":s}}).then(function(){var e=r(i.a.mark(function e(r){var a;return i.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,r.json();case 2:a=e.sent,a=a.data,p=a,t.setAttributes({buttonText:o});case 6:case"end":return e.stop()}},e,n)}));return function(t){return e.apply(this,arguments)}}()).catch(function(t){console.log("ERROR: "+t.message)})}},g=function(e){t.setAttributes({embedUrl:"",buttonText:"Embed",lockEmbed:!1,insertItemImage:!1,insertItemOsTitle:!1,insertItemOsView:!1,insertItemOsEdit:!1,insertItemOsStatistics:!1})},v=function(t){window.location.replace(d)},b=osGutenData.onCreateButtonClickOs+"?w_type=poll&amp;utm_source=wordpress&amp;utm_campaign=WPMainPI&amp;utm_medium=link&amp;o=wp35e8",w=function(t){window.open(b,"_blank").focus()};if(""==osGutenData.isOsConnected)return wp.element.createElement("div",{className:t.className},wp.element.createElement("div",{className:"os-poll-wrapper components-placeholder"},wp.element.createElement("p",{className:"components-heading"},wp.element.createElement("img",{src:f,alt:""})),wp.element.createElement("p",{className:"components-heading"},"Please connect WordPress to Opinion Stage to start adding polls"),wp.element.createElement("button",{className:"components-button is-button is-default is-block is-primary",onClick:v},"Connect")),wp.element.createElement("div",null));if(jQuery(document).ready(function(){jQuery("span#oswpLauncherContentPopuppoll").live("click",function(t){t.preventDefault(),setTimeout(function(){jQuery(".editor-post-save-draft").trigger("click")},500);var e=jQuery(this).attr("data-os-block");jQuery("button#dropbtn span").text(e);for(var n=jQuery(".filter__itm"),r=0;r<n.length;r++){if(jQuery(n[r]).text()==e){setTimeout(function(){jQuery(n[r]).trigger("click")},1e3),setTimeout(function(){jQuery(".progress_message").css("display","none"),jQuery(".content__list").css("display","block")},2500),jQuery("button.content__links-itm").live("click",function(t){jQuery(".tingle-modal.opinionstage-content-popup").hide(),jQuery(".tingle-modal.opinionstage-content-popup.tingle-modal--visible").hide()});break}jQuery(".progress_message").css("display","block"),jQuery(".content__list").css("display","none")}})}),0!=p)for(var y=0;y<p.length;y++){var E=function(t){var e=document.createElement("a");return e.href=t,e}(p[y].attributes["landing-page-url"]),x=E.pathname;if(n==x){t.setAttributes({lockEmbed:!0,buttonText:"Change"}),t.setAttributes({insertItemImage:p[y].attributes["image-url"]}),t.setAttributes({insertItemOsTitle:p[y].attributes.title}),t.setAttributes({insertItemOsView:p[y].attributes["landing-page-url"]}),t.setAttributes({insertItemOsEdit:p[y].attributes["edit-url"]}),t.setAttributes({insertItemOsStatistics:p[y].attributes["stats-url"]});break}}var k=wp.element.createElement("div",{className:"os-poll-wrapper components-placeholder"},wp.element.createElement("p",{className:"components-heading"},wp.element.createElement("img",{src:f,alt:""})),wp.element.createElement("span",{id:"oswpLauncherContentPopuppoll",className:"components-button is-button is-default is-block is-primary","data-opinionstage-content-launch":!0,"data-os-block":"poll",onClick:h},"Select a Poll"),wp.element.createElement("input",{type:"button",value:"Create a New Poll",className:"components-button is-button is-default is-block is-primary",onClick:w}),wp.element.createElement("span",null));return""!=n&&n?"Embed"==o||"Change"==o&&(k=wp.element.createElement("div",{className:"os-poll-wrapper components-placeholder"},wp.element.createElement("p",{className:"components-heading"},wp.element.createElement("img",{src:f,alt:""})),wp.element.createElement("div",{className:"components-preview__block"},wp.element.createElement("div",{className:"components-preview__leftBlockImage"},wp.element.createElement("img",{src:a,alt:s,className:"image"}),wp.element.createElement("div",{className:"overlay"},wp.element.createElement("div",{className:"text"},wp.element.createElement("a",{href:l,target:"_blank"}," View "),wp.element.createElement("a",{href:c,target:"_blank"}," Edit "),wp.element.createElement("a",{href:u,target:"_blank"}," Statistics "),wp.element.createElement("input",{type:"button",value:o,className:"components-button is-button is-default is-large left-align",onClick:g})))),wp.element.createElement("div",{className:"components-preview__rightBlockContent"},wp.element.createElement("div",{className:"components-placeholder__label"},"Poll: ",s))),wp.element.createElement("span",null))):""==n||"undefined"===jQuery.type(n)||t.setAttributes({buttonText:"Embed"}),wp.element.createElement("div",{className:t.className},k,wp.element.createElement("span",null))},save:function(t){var e=t.attributes,n=e.embedUrl,r=e.lockEmbed,o=e.buttonText,i=e.insertItemImage,a=e.insertItemOsTitle,s=e.insertItemOsView,l=e.insertItemOsEdit,c=e.insertItemOsStatistics;return wp.element.createElement("div",{class:"os-poll-wrapper","data-type":"poll","data-image-url":i,"data-title-url":a,"data-view-url":s,"data-statistics-url":c,"data-edit-url":l,"data-test-url":n,"data-lock-embed":r,"data-button-text":o},'[os-widget path="',n,'"]',wp.element.createElement("span",null))}})},function(t,e,n){t.exports=n(3)},function(t,e,n){var r=function(){return this}()||Function("return this")(),o=r.regeneratorRuntime&&Object.getOwnPropertyNames(r).indexOf("regeneratorRuntime")>=0,i=o&&r.regeneratorRuntime;if(r.regeneratorRuntime=void 0,t.exports=n(4),o)r.regeneratorRuntime=i;else try{delete r.regeneratorRuntime}catch(t){r.regeneratorRuntime=void 0}},function(t,e){!function(e){"use strict";function n(t,e,n,r){var i=e&&e.prototype instanceof o?e:o,a=Object.create(i.prototype),s=new d(r||[]);return a._invoke=c(t,n,s),a}function r(t,e,n){try{return{type:"normal",arg:t.call(e,n)}}catch(t){return{type:"throw",arg:t}}}function o(){}function i(){}function a(){}function s(t){["next","throw","return"].forEach(function(e){t[e]=function(t){return this._invoke(e,t)}})}function l(t){function e(n,o,i,a){var s=r(t[n],t,o);if("throw"!==s.type){var l=s.arg,c=l.value;return c&&"object"===typeof c&&b.call(c,"__await")?Promise.resolve(c.__await).then(function(t){e("next",t,i,a)},function(t){e("throw",t,i,a)}):Promise.resolve(c).then(function(t){l.value=t,i(l)},a)}a(s.arg)}function n(t,n){function r(){return new Promise(function(r,o){e(t,n,r,o)})}return o=o?o.then(r,r):r()}var o;this._invoke=n}function c(t,e,n){var o=_;return function(i,a){if(o===L)throw new Error("Generator is already running");if(o===N){if("throw"===i)throw a;return h()}for(n.method=i,n.arg=a;;){var s=n.delegate;if(s){var l=u(s,n);if(l){if(l===P)continue;return l}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(o===_)throw o=N,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);o=L;var c=r(t,e,n);if("normal"===c.type){if(o=n.done?N:I,c.arg===P)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(o=N,n.method="throw",n.arg=c.arg)}}}function u(t,e){var n=t.iterator[e.method];if(n===g){if(e.delegate=null,"throw"===e.method){if(t.iterator.return&&(e.method="return",e.arg=g,u(t,e),"throw"===e.method))return P;e.method="throw",e.arg=new TypeError("The iterator does not provide a 'throw' method")}return P}var o=r(n,t.iterator,e.arg);if("throw"===o.type)return e.method="throw",e.arg=o.arg,e.delegate=null,P;var i=o.arg;return i?i.done?(e[t.resultName]=i.value,e.next=t.nextLoc,"return"!==e.method&&(e.method="next",e.arg=g),e.delegate=null,P):i:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,P)}function p(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function m(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function d(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(p,this),this.reset(!0)}function f(t){if(t){var e=t[y];if(e)return e.call(t);if("function"===typeof t.next)return t;if(!isNaN(t.length)){var n=-1,r=function e(){for(;++n<t.length;)if(b.call(t,n))return e.value=t[n],e.done=!1,e;return e.value=g,e.done=!0,e};return r.next=r}}return{next:h}}function h(){return{value:g,done:!0}}var g,v=Object.prototype,b=v.hasOwnProperty,w="function"===typeof Symbol?Symbol:{},y=w.iterator||"@@iterator",E=w.asyncIterator||"@@asyncIterator",x=w.toStringTag||"@@toStringTag",k="object"===typeof t,O=e.regeneratorRuntime;if(O)return void(k&&(t.exports=O));O=e.regeneratorRuntime=k?t.exports:{},O.wrap=n;var _="suspendedStart",I="suspendedYield",L="executing",N="completed",P={},T={};T[y]=function(){return this};var S=Object.getPrototypeOf,C=S&&S(S(f([])));C&&C!==v&&b.call(C,y)&&(T=C);var j=a.prototype=o.prototype=Object.create(T);i.prototype=j.constructor=a,a.constructor=i,a[x]=i.displayName="GeneratorFunction",O.isGeneratorFunction=function(t){var e="function"===typeof t&&t.constructor;return!!e&&(e===i||"GeneratorFunction"===(e.displayName||e.name))},O.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,a):(t.__proto__=a,x in t||(t[x]="GeneratorFunction")),t.prototype=Object.create(j),t},O.awrap=function(t){return{__await:t}},s(l.prototype),l.prototype[E]=function(){return this},O.AsyncIterator=l,O.async=function(t,e,r,o){var i=new l(n(t,e,r,o));return O.isGeneratorFunction(e)?i:i.next().then(function(t){return t.done?t.value:i.next()})},s(j),j[x]="Generator",j[y]=function(){return this},j.toString=function(){return"[object Generator]"},O.keys=function(t){var e=[];for(var n in t)e.push(n);return e.reverse(),function n(){for(;e.length;){var r=e.pop();if(r in t)return n.value=r,n.done=!1,n}return n.done=!0,n}},O.values=f,d.prototype={constructor:d,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=g,this.done=!1,this.delegate=null,this.method="next",this.arg=g,this.tryEntries.forEach(m),!t)for(var e in this)"t"===e.charAt(0)&&b.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=g)},stop:function(){this.done=!0;var t=this.tryEntries[0],e=t.completion;if("throw"===e.type)throw e.arg;return this.rval},dispatchException:function(t){function e(e,r){return i.type="throw",i.arg=t,n.next=e,r&&(n.method="next",n.arg=g),!!r}if(this.done)throw t;for(var n=this,r=this.tryEntries.length-1;r>=0;--r){var o=this.tryEntries[r],i=o.completion;if("root"===o.tryLoc)return e("end");if(o.tryLoc<=this.prev){var a=b.call(o,"catchLoc"),s=b.call(o,"finallyLoc");if(a&&s){if(this.prev<o.catchLoc)return e(o.catchLoc,!0);if(this.prev<o.finallyLoc)return e(o.finallyLoc)}else if(a){if(this.prev<o.catchLoc)return e(o.catchLoc,!0)}else{if(!s)throw new Error("try statement without catch or finally");if(this.prev<o.finallyLoc)return e(o.finallyLoc)}}}},abrupt:function(t,e){for(var n=this.tryEntries.length-1;n>=0;--n){var r=this.tryEntries[n];if(r.tryLoc<=this.prev&&b.call(r,"finallyLoc")&&this.prev<r.finallyLoc){var o=r;break}}o&&("break"===t||"continue"===t)&&o.tryLoc<=e&&e<=o.finallyLoc&&(o=null);var i=o?o.completion:{};return i.type=t,i.arg=e,o?(this.method="next",this.next=o.finallyLoc,P):this.complete(i)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),P},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.finallyLoc===t)return this.complete(n.completion,n.afterLoc),m(n),P}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.tryLoc===t){var r=n.completion;if("throw"===r.type){var o=r.arg;m(n)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,e,n){return this.delegate={iterator:f(t),resultName:e,nextLoc:n},"next"===this.method&&(this.arg=g),P}}}(function(){return this}()||Function("return this")())},function(t,e){},function(t,e){}]);