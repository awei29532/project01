(window.webpackJsonp=window.webpackJsonp||[]).push([[11],{Hxag:function(t,a,s){"use strict";s.r(a);var n={data:function(){return{username:"",password:"",captcha:"",errors:"",lang:null,langs:window.langs,captchaImg:document.getElementById("captcha").textContent}},methods:{submit:function(t){var a=this;t.preventDefault(),this.errors="",this.$ajax("POST","/api/login",{username:this.username,password:this.password,captcha:this.captcha}).then(function(t){location.href=t.redirect}).catch(function(t){422==t.response.status?a.errors=t.response.data.errors:console.warn(t)})},getCaptchaImg:function(){var t=this;axios.get("/login-captcha-img").then(function(a){t.captchaImg=a}).catch(function(t){console.error(t)})},changeLang:function(){axios.get("lang",{params:{lang:this.lang}}).then(function(t){location.reload()}).catch(function(t){console.error(t)})}}},e=s("KHd+"),r=Object(e.a)(n,function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"container"},[s("div",{staticClass:"py-5"}),t._v(" "),s("div",{staticClass:"card mx-auto p-3",staticStyle:{"max-width":"500px"}},[s("div",{staticClass:"card-body"},[s("h1",[t._v("Login")]),t._v(" "),s("p",{staticClass:"text-muted"},[t._v("Sign In to your account")]),t._v(" "),t.errors?s("ul",{staticClass:"alert alert-danger"},t._l(t.errors,function(a,n,e){return s("li",{key:e},[t._v(t._s(a))])}),0):t._e(),t._v(" "),s("form",{on:{submit:t.submit}},[s("b-input-group",{staticClass:"mb-3"},[s("b-input-group-text",{staticStyle:{width:"40px"},attrs:{slot:"prepend"},slot:"prepend"},[s("i",{staticClass:"fas fa-user"})]),t._v(" "),s("b-form-input",{attrs:{type:"text",autofocus:"",placeholder:t.trans("common.username"),required:""},model:{value:t.username,callback:function(a){t.username=a},expression:"username"}})],1),t._v(" "),s("b-input-group",{staticClass:"mb-3"},[s("b-input-group-text",{staticStyle:{width:"40px"},attrs:{slot:"prepend"},slot:"prepend"},[s("i",{staticClass:"fas fa-key"})]),t._v(" "),s("b-form-input",{attrs:{type:"password",placeholder:t.trans("common.password"),required:""},model:{value:t.password,callback:function(a){t.password=a},expression:"password"}})],1),t._v(" "),s("b-input-group",{staticClass:"mb-3"},[s("b-input-group-text",{staticStyle:{width:"40px"},attrs:{slot:"prepend"},slot:"prepend"},[s("i",{staticClass:"fas fa-lock"})]),t._v(" "),s("b-form-input",{attrs:{type:"text",placeholder:t.trans("login.captcha"),required:""},model:{value:t.captcha,callback:function(a){t.captcha=a},expression:"captcha"}}),t._v(" "),s("img",{attrs:{src:t.captchaImg},on:{click:function(a){return t.getCaptchaImg()}}})],1),t._v(" "),s("div",{staticClass:"row mb-2"},[s("div",{staticClass:"col-6"},[s("button",{staticClass:"btn btn-primary px-4",attrs:{type:"submit"}},[s("i",{staticClass:"fas fa-sign-in-alt"}),t._v(" "+t._s(t.trans("login.login"))+"\n                        ")])]),t._v(" "),s("div",{staticClass:"col-6"},[s("b-form-select",{on:{change:t.changeLang},model:{value:t.lang,callback:function(a){t.lang=a},expression:"lang"}},[s("option",{attrs:{disabled:""},domProps:{value:null}},[t._v(t._s(t.trans("login.lang")))]),t._v(" "),t._l(t.langs,function(a,n){return s("option",{key:n,domProps:{value:a}},[t._v(t._s(t.trans("common.lang."+a)))])})],2)],1)])],1)])])])},[],!1,null,null,null);a.default=r.exports}}]);