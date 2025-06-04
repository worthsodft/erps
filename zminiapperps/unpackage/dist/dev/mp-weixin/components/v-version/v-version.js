"use strict";
const common_vendor = require("../../common/vendor.js");
const extend_config = require("../../extend/config.js");
const _sfc_main = {
  __name: "v-version",
  setup(__props) {
    const version = common_vendor.ref(extend_config.config.version);
    return (_ctx, _cache) => {
      return {
        a: common_vendor.t(version.value)
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-0c2720f3"]]);
wx.createComponent(Component);
