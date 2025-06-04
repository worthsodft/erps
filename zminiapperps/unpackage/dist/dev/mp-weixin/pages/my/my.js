"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_user_banner2 = common_vendor.resolveComponent("v-user-banner");
  const _easycom_v_user_bar2 = common_vendor.resolveComponent("v-user-bar");
  const _easycom_v_user_order_tabs2 = common_vendor.resolveComponent("v-user-order-tabs");
  const _easycom_v_user_service2 = common_vendor.resolveComponent("v-user-service");
  const _easycom_v_version2 = common_vendor.resolveComponent("v-version");
  (_easycom_v_user_banner2 + _easycom_v_user_bar2 + _easycom_v_user_order_tabs2 + _easycom_v_user_service2 + _easycom_v_version2)();
}
const _easycom_v_user_banner = () => "../../components/v-user-banner/v-user-banner.js";
const _easycom_v_user_bar = () => "../../components/v-user-bar/v-user-bar.js";
const _easycom_v_user_order_tabs = () => "../../components/v-user-order-tabs/v-user-order-tabs.js";
const _easycom_v_user_service = () => "../../components/v-user-service/v-user-service.js";
const _easycom_v_version = () => "../../components/v-version/v-version.js";
if (!Math) {
  (_easycom_v_user_banner + _easycom_v_user_bar + _easycom_v_user_order_tabs + _easycom_v_user_service + _easycom_v_version)();
}
const _sfc_main = {
  __name: "my",
  setup(__props) {
    const userBannerRef = common_vendor.ref();
    const userBarRef = common_vendor.ref();
    common_vendor.onShow(() => {
      common_vendor.index.$api.getCartTotal();
    });
    function reload() {
      userBannerRef.value.reload();
      userBarRef.value.reload();
    }
    common_vendor.onPullDownRefresh(() => {
      reload();
      setTimeout(() => {
        common_vendor.index.stopPullDownRefresh();
      }, 200);
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.sr(userBannerRef, "2f1ef635-0", {
          "k": "userBannerRef"
        }),
        b: common_vendor.sr(userBarRef, "2f1ef635-1", {
          "k": "userBarRef"
        })
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-2f1ef635"]]);
wx.createPage(MiniProgramPage);
