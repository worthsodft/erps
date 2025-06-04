"use strict";
const common_vendor = require("../../common/vendor.js");
const extend_config = require("../../extend/config.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  _easycom_uni_icons2();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  _easycom_uni_icons();
}
const _sfc_main = {
  __name: "v-user-banner",
  setup(__props, { expose: __expose }) {
    const defaultAvatar = common_vendor.ref(extend_config.config.defaultAvatar);
    const userInfo = common_vendor.ref({});
    const isTest = common_vendor.ref();
    isTest.value = extend_config.config.domain.indexOf("erp.") == -1;
    common_vendor.onShow(() => {
      getUserInfo();
    });
    function getUserInfo() {
      common_vendor.index.$api.getUserInfo((res) => {
        userInfo.value = res.data.userInfo;
      });
    }
    function reload() {
      getUserInfo();
    }
    function form() {
      common_vendor.index.$tool.navto("/pages/my/form");
    }
    __expose({
      reload
    });
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: userInfo.value.avatar || defaultAvatar.value,
        b: common_vendor.t(userInfo.value.nickname || "点击登录"),
        c: userInfo.value.realname
      }, userInfo.value.realname ? {
        d: common_vendor.t(userInfo.value.realname)
      } : {}, {
        e: isTest.value
      }, isTest.value ? {} : {}, {
        f: common_vendor.o(form),
        g: common_vendor.p({
          type: "gear",
          size: "30",
          color: "#fff"
        })
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-f33a6c72"]]);
wx.createComponent(Component);
