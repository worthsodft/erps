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
  __name: "v-user-order-tabs",
  setup(__props) {
    const tabs = common_vendor.ref();
    common_vendor.onShow(() => {
      common_vendor.index.$login.judgeLogin().then(() => {
        getUserOrderTabsData();
      });
    });
    function getUserOrderTabsData() {
      common_vendor.index.$http.get("v1/getUserOrderTabsData").then((res) => {
        tabs.value = res.data.tabsData;
      });
    }
    function orderList(status) {
      common_vendor.index.$tool.navto("/pages/order/list?status=" + status);
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(tabs.value, (item, index, i0) => {
          return common_vendor.e({
            a: "372a7ef8-0-" + i0,
            b: common_vendor.p({
              type: item.icon,
              size: "30",
              color: "#6abfff"
            }),
            c: common_vendor.t(item.title),
            d: item.count > 0
          }, item.count > 0 ? {
            e: common_vendor.t(item.count)
          } : {}, {
            f: index,
            g: common_vendor.o(($event) => orderList(item.status), index)
          });
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-372a7ef8"]]);
wx.createComponent(Component);
