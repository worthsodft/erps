"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-user-bar",
  setup(__props, { expose: __expose }) {
    const couponCount = common_vendor.ref(0);
    const balance = common_vendor.ref(0);
    const expire_at = common_vendor.ref();
    common_vendor.onShow(() => {
      common_vendor.index.$login.judgeLogin().then(() => {
        getUserBarData();
      });
    });
    async function getUserBarData() {
      const res = await common_vendor.index.$http.get("v1/getUserBarData");
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      couponCount.value = res.data.couponCount;
      balance.value = res.data.balance;
      expire_at.value = res.data.expire_at;
    }
    function reload() {
      getUserBarData();
    }
    function coupon() {
      common_vendor.index.$tool.navto("/pages/my/coupon");
    }
    function moneyLog() {
      common_vendor.index.$tool.navto("/pages/pay/money-log");
    }
    __expose({
      reload
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.t(couponCount.value),
        b: common_vendor.o(coupon),
        c: common_vendor.t(balance.value),
        d: common_vendor.o(moneyLog)
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-1f85769f"]]);
wx.createComponent(Component);
