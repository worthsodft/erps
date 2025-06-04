"use strict";
const common_vendor = require("../common/vendor.js");
const login = {
  // 获取登录用的code
  getCode(cb) {
    common_vendor.index.login({
      success(res) {
        cb && cb(res.code);
      }
    });
  },
  // 登录
  do(pid, cb) {
    return new Promise((resolve, reject) => {
      this.getCode(async (code) => {
        const res = await common_vendor.index.$http.post("v1/login", { code, pid });
        cb && cb(res);
        if (res.code == 1) {
          this.updateTokenCache(res.data);
          resolve(res.data.token);
        } else {
          reject();
        }
      });
    });
  },
  updateTokenCache(data) {
    common_vendor.index.$tool.cache("token", data.token);
    common_vendor.index.$tool.cache("expire_at", data.expire_at);
    common_vendor.index.$tool.cache("expire_at_txt", data.expire_at_txt);
    common_vendor.index.$tool.cache("is_phone", data.is_phone);
  },
  // 判断是否已登录
  async judgeLogin(cb) {
    const token = common_vendor.index.$tool.cache("token");
    const expire_at = common_vendor.index.$tool.cache("expire_at");
    if (!token || Date.now() / 1e3 > expire_at) {
      await this.do();
      console.log("login.do(登录)");
      cb && cb();
      return Promise.resolve();
    } else {
      cb && cb();
      return Promise.resolve();
    }
  },
  judgePhone(cb) {
    this.judgeLogin(() => {
      common_vendor.index.$tool.cache("token");
      const is_phone = common_vendor.index.$tool.cache("is_phone");
      if (is_phone) {
        cb && cb();
      } else {
        common_vendor.index.$tool.alert("请先完善用户信息", () => {
          common_vendor.index.$tool.navto("/pages/my/form");
        });
      }
    });
  },
  logout() {
    common_vendor.index.$tool.removeCache("token");
    common_vendor.index.$tool.removeCache("expire_at");
    common_vendor.index.$tool.removeCache("expire_at_txt");
    common_vendor.index.$tool.removeCache("is_phone");
  }
  // 获取绑定手机号码
  // getPhone(params, code=null){
  // 	return new Promise((resolve,reject)=>{
  // 		if(!code){
  // 			this.getCode(code=>{
  // 				params.code = code
  // 				this.postPhone(params).then(res=>{ resolve(res) })
  // 			})
  // 		}else{
  // 			params.code = code
  // 			this.postPhone(params).then(res=>{ resolve(res) })
  // 		}
  // 	})
  // },
  // 请求后台api
  // postPhone(params){
  // 	return new Promise((resolve,reject)=>{
  // 		uni.$u.http.post(pv + '/savePhone', params, {custom:{auth:false}}).then(res=>{
  // 			if(res.code == 1){
  // 				store.commit('updateToken', res.data)
  // 				resolve(res.data.userInfo)
  // 			}
  // 		})
  // 	})
  // }
};
exports.login = login;
