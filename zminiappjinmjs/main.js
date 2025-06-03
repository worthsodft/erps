
import enum_ from '@/extend/enum.js';
uni.$enum = enum_;

import tool from '@/extend/tool.js';
uni.$tool = tool;
import http from '@/extend/http.js';
uni.$http = http;
import login from '@/extend/login.js';
uni.$login = login;
import api from '@/extend/api.js';
uni.$api = api;

// #ifdef H5
// android点击手机物理返回键退出app bug解决
document.addEventListener("plusready", function() {
	plus.key.addEventListener('backbutton', function() {
		window.history.go(-1);
	}, false);
}) 
// #endif


// #ifndef VUE3
import Vue from 'vue'
import App from './App'
Vue.config.productionTip = false

App.mpType = 'app'

const app = new Vue({
    ...App
})
app.$mount()
// #endif


// #ifdef VUE3
import { createSSRApp } from 'vue'
import App from './App.vue'

export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif