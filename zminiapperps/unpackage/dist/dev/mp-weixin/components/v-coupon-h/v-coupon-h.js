"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_uni_section2 = common_vendor.resolveComponent("uni-section");
  const _easycom_v_coupon_item2 = common_vendor.resolveComponent("v-coupon-item");
  (_easycom_uni_icons2 + _easycom_uni_section2 + _easycom_v_coupon_item2)();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_uni_section = () => "../../uni_modules/uni-section/components/uni-section/uni-section.js";
const _easycom_v_coupon_item = () => "../v-coupon-item/v-coupon-item.js";
if (!Math) {
  (_easycom_uni_icons + _easycom_uni_section + _easycom_v_coupon_item)();
}
const _sfc_main = {
  __name: "v-coupon-h",
  props: {
    list: Array
  },
  setup(__props) {
    function more() {
      common_vendor.index.$tool.navto("/pages/my/coupon");
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.p({
          type: "right",
          color: "#333"
        }),
        b: common_vendor.o(more),
        c: common_vendor.p({
          title: "优惠券",
          type: "line"
        }),
        d: common_vendor.f(__props.list, (item, k0, i0) => {
          return {
            a: "e8434bad-2-" + i0,
            b: common_vendor.p({
              item
            }),
            c: item.gid
          };
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-e8434bad"]]);
wx.createComponent(Component);
