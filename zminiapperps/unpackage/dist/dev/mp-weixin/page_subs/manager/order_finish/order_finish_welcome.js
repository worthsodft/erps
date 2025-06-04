"use strict";
const common_vendor = require("../../../common/vendor.js");
if (!Array) {
  const _easycom_v_line_title2 = common_vendor.resolveComponent("v-line-title");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  (_easycom_v_line_title2 + _easycom_uni_easyinput2)();
}
const _easycom_v_line_title = () => "../../../components/v-line-title/v-line-title.js";
const _easycom_uni_easyinput = () => "../../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
if (!Math) {
  (_easycom_v_line_title + _easycom_uni_easyinput)();
}
const _sfc_main = {
  __name: "order_finish_welcome",
  setup(__props) {
    const sn = common_vendor.ref("");
    async function toFinish() {
      let sn_ = sn.value || "";
      sn.value = "";
      if (!sn_)
        return common_vendor.index.$tool.tip("请扫描或输入订单编号");
      const res = await common_vendor.index.$http.post("v1/isAuthFinishOrder");
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.navto("/page_subs/manager/order_finish/order_finish_index?sn=" + sn_);
    }
    function getSnFromScan() {
      common_vendor.index.scanCode({
        success({ result }) {
          if (result) {
            sn.value = result;
            toFinish();
          } else
            console.log("扫码失败");
        },
        fail(err) {
          console.log(err);
        }
      });
    }
    function showPick() {
      common_vendor.index.$tool.navto("/page_subs/manager/order_finish/order_pick_list");
    }
    function show() {
      common_vendor.index.$tool.navto("/page_subs/manager/order_finish/order_finish_list");
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.p({
          title: "订单编号："
        }),
        b: common_vendor.o(getSnFromScan),
        c: common_vendor.o(($event) => sn.value = $event),
        d: common_vendor.p({
          suffixIcon: "scan",
          placeholder: "请扫描或输入订单编号",
          modelValue: sn.value
        }),
        e: common_vendor.o(toFinish),
        f: common_vendor.o(showPick),
        g: common_vendor.o(show)
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-ee1ab9df"]]);
wx.createPage(MiniProgramPage);
