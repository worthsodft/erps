"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  _easycom_uni_icons2();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  _easycom_uni_icons();
}
const _sfc_main = {
  __name: "success",
  setup(__props) {
    const type = common_vendor.ref(""), money = common_vendor.ref("0.00"), sn = common_vendor.ref("");
    common_vendor.onLoad((opts) => {
      type.value = opts.type || "order";
      sn.value = opts.sn || "";
      common_vendor.index.$login.judgeLogin(() => {
        getInitDataPaySuccess();
      });
    });
    async function getInitDataPaySuccess() {
      const res = await common_vendor.index.$http.get(`v1/getInitDataPaySuccess/${type.value}/${sn.value}`);
      money.value = res.data.money;
    }
    function back() {
      common_vendor.index.$tool.index();
    }
    function show() {
      let url = "/pages/order/list?status=1";
      if (type.value == "recharge")
        url = "/pages/pay/money-log";
      common_vendor.index.$tool.navto(url);
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.p({
          type: "checkbox-filled",
          size: "160",
          color: "#5aa1d8"
        }),
        b: common_vendor.t(sn.value),
        c: common_vendor.t(money.value),
        d: common_vendor.o(back),
        e: common_vendor.t(type.value == "order" ? "查看订单列表" : "查看余额记录"),
        f: common_vendor.o(show)
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-2b0b9865"]]);
wx.createPage(MiniProgramPage);
