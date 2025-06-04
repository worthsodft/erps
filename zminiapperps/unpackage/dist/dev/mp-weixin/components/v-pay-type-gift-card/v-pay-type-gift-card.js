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
  __name: "v-pay-type-gift-card",
  props: {
    index: Number,
    card: Object
  },
  emits: ["action"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function select(index) {
      emit("action", index);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.card.checked
      }, __props.card.checked ? {
        b: common_vendor.p({
          type: "checkbox-filled",
          color: "#5aa1d8",
          size: "24"
        })
      } : {
        c: common_vendor.p({
          type: "checkbox",
          color: "#999",
          size: "24"
        })
      }, {
        d: common_vendor.t(__props.card.has || ""),
        e: common_vendor.n(__props.card.checked ? "color-green" : "color-desc"),
        f: common_vendor.t(__props.card.useful_expire_at || ""),
        g: common_vendor.n(__props.card.checked ? "color-green" : "color-desc"),
        h: common_vendor.p({
          type: "right",
          color: "#fff"
        }),
        i: common_vendor.o(($event) => select(__props.index)),
        j: __props.card.checked ? 1 : ""
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-e4b40490"]]);
wx.createComponent(Component);
