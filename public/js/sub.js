(window.webpackJsonp=window.webpackJsonp||[]).push([[16],{tk4I:function(t,s,a){"use strict";a.r(s);var e={data:function(){return{subItems:{data:[],page:1,perPage:15,total:0,lastPage:1},fields:[{key:"#",thStyle:{width:"40px"},class:"text-center"},{key:"username",sortable:!0,label:this.trans("common.username"),thClass:"text-center"},{key:"name",sortable:!0,label:this.trans("common.name"),thClass:"text-center"},{key:"status",label:this.trans("common.status"),class:"table-col-status"},{key:"remark",sortable:!0,label:this.trans("common.remark"),thClass:"text-center"},{key:"updated_at",sortable:!0,label:this.trans("common.updated_at"),class:"table-col-time"},{key:"created_at",sortable:!0,label:this.trans("common.created_at"),class:"table-col-time"}],searchData:{account:"",status:"all",page:1,perPage:25},isLoading:!1}},mounted:function(){this.search()},methods:{search:function(){var t=this,s=arguments.length>0&&void 0!==arguments[0]?arguments[0]:1,a=arguments.length>1&&void 0!==arguments[1]?arguments[1]:25;this.isLoading||(this.isLoading=!0,this.searchData.page=s,this.searchData.perPage=Number(a),this.$ajax("POST","/api/accounts/sub_account/list",this.searchData).then(function(s){t.subItems=s,t.isLoading=!1}).catch(function(t){console.error(t)}))},addSub:function(){location.href="/accounts/sub_account-edit/"},enabled:function(t,s){var a=this;this.isLoading=!0,this.$ajax("POST","/api/accounts/sub_account/toggle-enabled",{id:t,enabled:s}).then(function(e){a.subItems.data.filter(function(s){return s.id==t})[0].status=s,a.isLoading=!1}).catch(function(t){console.error(t)})}}},n=a("KHd+"),o=Object(n.a)(e,function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("section",[a("list-grid",{attrs:{data:t.subItems,search:t.search}},[a("template",{slot:"search-form"},[t.user.isAdmin?a("a",{staticClass:"btn btn-info mr-2 mb-1",attrs:{href:"/accounts/sub_account-edit/"}},[t._v(t._s(t.trans("common.add")))]):t._e(),t._v(" "),a("b-input-group",{staticClass:"mr-2 mb-1",attrs:{prepend:t.trans("common.status")}},[a("b-form-select",{model:{value:t.searchData.status,callback:function(s){t.$set(t.searchData,"status",s)},expression:"searchData.status"}},[a("option",{attrs:{value:"all"}},[t._v(t._s(t.trans("common.all")))]),t._v(" "),a("option",{attrs:{value:"1"}},[t._v(t._s(t.trans("common.active")))]),t._v(" "),a("option",{attrs:{value:"0"}},[t._v(t._s(t.trans("common.suspended")))])])],1)],1),t._v(" "),a("template",{slot:"table"},[a("b-table",{attrs:{responsive:"",bordered:"",striped:"",small:"",items:t.subItems.data,fields:t.fields,busy:t.isLoading,"show-empty":"","empty-text":t.trans("common.empty-data")},scopedSlots:t._u([{key:"#",fn:function(s){return[t._v("\n                        "+t._s(s.index+1+t.subItems.perPage*(t.subItems.page-1))+"\n                    ")]}},{key:"username",fn:function(s){return[a("a",{attrs:{href:"/accounts/sub_account-edit/"+s.item.id}},[t._v(t._s(s.item.username))])]}},{key:"status",fn:function(s){return[a("button",{class:"btn-pill btn-sm btn-"+(s.item.status?"success":"danger"),on:{click:function(a){return t.enabledDialog(s.item.id,s.item.username,s.item.status,t.enabled)}}},[t._v("\n\t\t\t\t\t\t\t\t"+t._s(t.trans("common."+(s.item.status?"active":"suspended")))+"\n\t\t\t\t\t\t")])]}}])},[a("loading",{attrs:{slot:"table-busy"},slot:"table-busy"})],1)],1)],2)],1)},[],!1,null,null,null);s.default=o.exports}}]);