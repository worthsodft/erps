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
  __name: "v-notice",
  props: {
    list: Array,
    interval: {
      type: Number,
      default: 3e3
    }
  },
  setup(__props) {
    return (_ctx, _cache) => {
      return {
        a: common_vendor.p({
          type: "sound-filled",
          size: "30",
          color: "#5aa1d8"
        }),
        b: common_vendor.f(__props.list, (item, k0, i0) => {
          return {
            a: common_vendor.t(item.title),
            b: item.gid
          };
        }),
        c: __props.interval
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-fd2c8c0b"]]);
wx.createComponent(Component);
