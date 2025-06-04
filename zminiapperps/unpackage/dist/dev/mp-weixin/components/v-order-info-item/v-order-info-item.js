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
  __name: "v-order-info-item",
  props: {
    type: String,
    title: String,
    title2: String,
    title2color: String,
    value: [String, Number],
    color: "color",
    bold: Boolean,
    showRightIcon: {
      type: Boolean,
      default: true
    }
  },
  emits: ["action"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const triggers = common_vendor.ref({ coupon: 1, pay_type: 1 });
    const emit = __emit;
    function action() {
      if (triggers.value[props.type]) {
        emit("action");
      }
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.title),
        b: common_vendor.t(__props.title2 || ""),
        c: __props.title2color || "#999",
        d: triggers.value[__props.type]
      }, triggers.value[__props.type] ? common_vendor.e({
        e: common_vendor.t(__props.value),
        f: __props.showRightIcon
      }, __props.showRightIcon ? {
        g: common_vendor.p({
          type: "right",
          color: __props.color
        })
      } : {}, {
        h: __props.bold ? 1 : "",
        i: __props.color
      }) : {
        j: common_vendor.t(__props.value),
        k: __props.bold ? 1 : "",
        l: __props.color
      }, {
        m: common_vendor.o(action)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-748876cb"]]);
wx.createComponent(Component);
