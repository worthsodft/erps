"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-swiper",
  props: {
    list: Array
  },
  setup(__props) {
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(__props.list, (item, k0, i0) => {
          return {
            a: item.cover || "",
            b: item.gid
          };
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-803b8d0e"]]);
wx.createComponent(Component);
