"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-line-title",
  props: {
    title: String,
    inline: Boolean
  },
  setup(__props) {
    return (_ctx, _cache) => {
      return {
        a: common_vendor.t(__props.title)
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-e9673c5b"]]);
wx.createComponent(Component);
