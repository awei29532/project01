(window.webpackJsonp=window.webpackJsonp||[]).push([[14],{BfrK:function(e,t,a){"use strict";a.r(t);function r(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),a.push.apply(a,r)}return a}function n(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}var o={props:{providerid:{required:!0}},data:function(){return{data:{id:this.providerid,name:"",code:"",maintenance_start:"",maintenance_end:"",has_fun:""},errors:{}}},mounted:function(){if(0!=this.providerid){var e=this.getJsonData("provider-data");this.data=function(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?r(a,!0).forEach(function(t){n(e,t,a[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):r(a).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))})}return e}({id:this.providerid},e)}},methods:{send:function(e){var t=this;e.preventDefault(),this.errors=[];var a="/api/products/provider/"+(0==this.providerid?"add":"edit");this.$ajax("POST",a,this.data).then(function(e){location.href="/products/provider"}).catch(function(e){if(422==e.response.status){var a=e.response.data.errors;for(var r in a)a[r]=a[r].join(", ");t.errors=a}else console.warn(e)})},handleErrors:function(e){return void 0===this.errors[e]&&null},cancel:function(){history.back()}}},s=a("KHd+"),i=Object(s.a)(o,function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("section",{staticClass:"container-fluid"},[a("div",{staticClass:"card"},[a("div",{staticClass:"card-body"},[a("form",{on:{submit:e.send}},[a("b-form-group",{attrs:{"label-cols-md":"1",label:e.trans("common.name"),"label-for":"name"}},[a("b-form-input",{attrs:{id:"name",type:"text",state:e.handleErrors("name"),required:""},model:{value:e.data.name,callback:function(t){e.$set(e.data,"name",t)},expression:"data.name"}}),e._v(" "),a("b-form-invalid-feedback",[e._v("\n                        "+e._s(e.errors.name)+"\n                    ")])],1),e._v(" "),a("b-form-group",{attrs:{"label-cols-md":"1",label:e.trans("providers.code"),"label-for":"code"}},[a("b-form-input",{attrs:{id:"code",type:"text",state:e.handleErrors("code"),required:"",disabled:0!=e.providerid},model:{value:e.data.code,callback:function(t){e.$set(e.data,"code",t)},expression:"data.code"}}),e._v(" "),a("b-form-invalid-feedback",[e._v("\n                        "+e._s(e.errors.code)+"\n                    ")])],1),e._v(" "),a("b-form-group",{attrs:{"label-cols-md":"1",label:e.trans("providers.maintenance_start"),"label-for":"maintenance_start"}},[a("datetime",{attrs:{format:"YYYY-MM-DD H:i:s"},model:{value:e.data.maintenance_start,callback:function(t){e.$set(e.data,"maintenance_start",t)},expression:"data.maintenance_start"}}),e._v(" "),a("b-form-invalid-feedback",[e._v("\n                        "+e._s(e.errors.maintenance_start)+"\n                    ")])],1),e._v(" "),a("b-form-group",{attrs:{"label-cols-md":"1",label:e.trans("providers.maintenance_end"),"label-for":"maintenance_end"}},[a("datetime",{attrs:{format:"YYYY-MM-DD H:i:s"},model:{value:e.data.maintenance_end,callback:function(t){e.$set(e.data,"maintenance_end",t)},expression:"data.maintenance_end"}}),e._v(" "),a("b-form-invalid-feedback",[e._v("\n                        "+e._s(e.errors.maintenance_end)+"\n                    ")])],1),e._v(" "),a("button",{staticClass:"btn btn-primary",attrs:{type:"submit"}},[a("i",{staticClass:"fa fa-search"}),e._v(" "+e._s(e.trans("common.submit")))]),e._v(" "),a("button",{staticClass:"btn btn-danger",on:{click:function(t){return e.cancel()}}},[a("i",{staticClass:"fa fa-ban"}),e._v(" "+e._s(e.trans("common.cancel")))])],1)])])])},[],!1,null,null,null);t.default=i.exports}}]);