(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["uni_modules-uni-feedback-pages-opendb-feedback-opendb-feedback"],{"0d85":function(t,e,n){var a=n("4190");a.__esModule&&(a=a.default),"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var i=n("4f06").default;i("1a4edbb6",a,!0,{sourceMap:!1,shadowMode:!1})},"31b7":function(t,e,n){"use strict";n.d(e,"b",(function(){return i})),n.d(e,"c",(function(){return o})),n.d(e,"a",(function(){return a}));var a={uniForms:n("ee6f").default,uniFormsItem:n("6817").default,uniFilePicker:n("e707").default,uniEasyinput:n("5d6b").default},i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("v-uni-view",{staticClass:"uni-container"},[n("uni-forms",{ref:"form",attrs:{value:t.formData,"validate-trigger":"submit","err-show-type":"toast"}},[n("uni-forms-item",{attrs:{name:"content",label:"留言内容/回复内容",required:!0}},[n("v-uni-textarea",{staticClass:"uni-textarea-border",attrs:{trim:"right"},on:{input:function(e){arguments[0]=e=t.$handleEvent(e),t.binddata("content",e.detail.value)}},model:{value:t.formData.content,callback:function(e){t.$set(t.formData,"content",e)},expression:"formData.content"}})],1),n("uni-forms-item",{attrs:{name:"imgs",label:"图片列表"}},[n("uni-file-picker",{attrs:{"file-mediatype":"image",limit:1,"return-type":"array"},model:{value:t.formData.imgs,callback:function(e){t.$set(t.formData,"imgs",e)},expression:"formData.imgs"}})],1),n("uni-forms-item",{attrs:{name:"phone",label:"联系电话"}},[n("uni-easyinput",{attrs:{trim:"both"},model:{value:t.formData.phone,callback:function(e){t.$set(t.formData,"phone",e)},expression:"formData.phone"}})],1),n("v-uni-view",{staticClass:"uni-button-group"},[n("v-uni-button",{staticClass:"uni-button",attrs:{type:"primary"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.submit.apply(void 0,arguments)}}},[t._v("提交")])],1)],1)],1)},o=[]},"3c9f":function(t,e,n){"use strict";n.r(e);var a=n("a830"),i=n.n(a);for(var o in a)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return a[t]}))}(o);e["default"]=i.a},"418f":function(t,e,n){"use strict";n.r(e);var a=n("31b7"),i=n("3c9f");for(var o in i)["default"].indexOf(o)<0&&function(t){n.d(e,t,(function(){return i[t]}))}(o);n("7bd2");var r=n("f0c5"),u=Object(r["a"])(i["default"],a["b"],a["c"],!1,null,"08354b84",null,!1,a["a"],void 0);e["default"]=u.exports},4190:function(t,e,n){var a=n("24fb");e=a(!1),e.push([t.i,".uni-container[data-v-08354b84]{padding:15px}.uni-input-border[data-v-08354b84],\n.uni-textarea-border[data-v-08354b84]{width:100%;font-size:14px;color:#666;border:1px #e5e5e5 solid;border-radius:5px;box-sizing:border-box}.uni-input-border[data-v-08354b84]{padding:0 10px;height:35px}.uni-textarea-border[data-v-08354b84]{padding:10px;height:80px}.uni-button-group[data-v-08354b84]{margin-top:50px;\ndisplay:flex;\njustify-content:center}.uni-button[data-v-08354b84]{width:184px;padding:12px 20px;font-size:14px;border-radius:4px;line-height:1;margin:0}",""]),t.exports=e},"7bd2":function(t,e,n){"use strict";var a=n("0d85"),i=n.n(a);i.a},a830:function(t,e,n){"use strict";n("7a82"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var a={data:function(){return{user:{},formData:{content:"",imgs:[],contact:"",phone:""}}},onLoad:function(){this.user=uni.getStorageSync("userinfo"),console.log(this.user),null!=this.user.id&&""!=this.user.id||uni.navigateTo({url:"/pages/login/login"})},methods:{submit:function(){uni.showLoading({mask:!0}),console.log(this.formData),this.formData.images=this.formData.imgs.length>0?this.formData.imgs[0].url:"",this.formData.remember_token=this.user.remember_token,uni.request({url:this.$url+"addSuggestion1",data:this.formData,method:"POST",success:function(t){console.log(t),t=t.data,0===t.code?uni.showToast({icon:"none",title:t.msg}):(uni.showToast({icon:"none",title:t.msg}),setTimeout((function(){uni.navigateBack()}),2e3))},complete:function(){uni.hideLoading()}}),console.log(this.formData)}}};e.default=a}}]);