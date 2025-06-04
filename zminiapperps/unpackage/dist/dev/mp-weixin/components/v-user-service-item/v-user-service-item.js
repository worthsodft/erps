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
  __name: "v-user-service-item",
  props: {
    item: Object,
    index: [String, Number]
  },
  emits: ["doService"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function doService() {
      emit("doService", props.item);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.item.type == "set"
      }, __props.item.type == "set" ? {
        b: common_vendor.p({
          type: __props.item.icon,
          color: "#6a6a6a",
          size: "20"
        }),
        c: common_vendor.t(__props.item.title || ""),
        d: common_vendor.p({
          type: "right",
          color: "#6a6a6a",
          size: "20"
        })
      } : common_vendor.e({
        e: common_vendor.p({
          type: __props.item.icon,
          color: "#6a6a6a",
          size: "20"
        }),
        f: common_vendor.t(__props.item.title || ""),
        g: __props.item.desc
      }, __props.item.desc ? {
        h: common_vendor.t(__props.item.desc)
      } : {}, {
        i: common_vendor.p({
          type: "right",
          color: "#6a6a6a",
          size: "20"
        }),
        j: common_vendor.o(doService)
      }));
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-6834d9ef"]]);
wx.createComponent(Component);
