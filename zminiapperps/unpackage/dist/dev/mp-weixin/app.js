"use strict";
Object.defineProperty(exports, Symbol.toStringTag, { value: "Module" });
const common_vendor = require("./common/vendor.js");
const extend_enum = require("./extend/enum.js");
const extend_tool = require("./extend/tool.js");
const extend_http = require("./extend/http.js");
const extend_login = require("./extend/login.js");
const extend_api = require("./extend/api.js");
if (!Math) {
  "./pages/index/index.js";
  "./pages/goods/list.js";
  "./pages/goods/gift_card_list.js";
  "./pages/cart/cart.js";
  "./pages/my/my.js";
  "./pages/goods/detail.js";
  "./pages/my/address.js";
  "./pages/my/address-edit.js";
  "./pages/my/coupon.js";
  "./pages/my/form.js";
  "./pages/order/list.js";
  "./pages/order/confirm.js";
  "./pages/order/detail.js";
  "./pages/pay/recharge.js";
  "./pages/pay/money-log.js";
  "./pages/pay/success.js";
  "./pages/pay/refund.js";
  "./pages/demo/list.js";
  "./page_subs/manager/order_finish/order_finish_welcome.js";
  "./page_subs/manager/order_finish/order_finish_index.js";
  "./page_subs/manager/order_finish/order_finish_list.js";
  "./page_subs/manager/order_finish/order_pick_list.js";
  "./page_subs/manager/order_deliver/order_deliver_list.js";
  "./page_subs/gift_card/bind.js";
  "./page_subs/gift_card/list.js";
  "./page_subs/gift_card/logs.js";
  "./page_subs/my_poster/my_poster.js";
}
const _sfc_main = {
  onLaunch: async function(opts) {
    var _a;
    const res = await common_vendor.index.$api.getAppConfig();
    const sysConfig = res.data.config;
    this.globalData.shareData = sysConfig.shareData;
    this.globalData.servicePhone = sysConfig.servicePhone;
    let pid = (_a = opts == null ? void 0 : opts.query) == null ? void 0 : _a.scene;
    common_vendor.index.$login.do(pid, (res2) => {
    });
  },
  onShow(opts) {
    if (opts.referrerInfo.appId == "wx1183b055aeec94d1" && opts.referrerInfo.extraData.req_extradata) {
      console.log("App Show", opts.referrerInfo.extraData.req_extradata);
    }
  },
  onHide() {
  },
  globalData: {
    shareData: {},
    servicePhone: ""
  }
};
common_vendor.index.$enum = extend_enum.enum_;
common_vendor.index.$tool = extend_tool.tool;
common_vendor.index.$http = extend_http.http;
common_vendor.index.$login = extend_login.login;
common_vendor.index.$api = extend_api.api;
function createApp() {
  const app = common_vendor.createSSRApp(_sfc_main);
  return {
    app
  };
}
createApp().app.mount("#app");
exports.createApp = createApp;
