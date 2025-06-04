"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_tag2 = common_vendor.resolveComponent("uni-tag");
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  (_easycom_uni_tag2 + _easycom_uni_icons2)();
}
const _easycom_uni_tag = () => "../../uni_modules/uni-tag/components/uni-tag/uni-tag.js";
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  (_easycom_uni_tag + _easycom_uni_icons)();
}
const _sfc_main = {
  __name: "v-address-item",
  props: {
    item: Object
  },
  emits: ["action"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function action() {
      emit("action", props.item.value);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.item.province ? __props.item.province + ", " : ""),
        b: common_vendor.t(__props.item.city ? __props.item.city + ", " : ""),
        c: common_vendor.t(__props.item.district ? __props.item.district + ", " : ""),
        d: common_vendor.t(__props.item.street ? __props.item.street + ", " : ""),
        e: common_vendor.t(__props.item.detail),
        f: common_vendor.t(__props.item.link_name),
        g: common_vendor.t(__props.item.link_phone),
        h: __props.item.is_default
      }, __props.item.is_default ? {
        i: common_vendor.p({
          text: "默 认",
          type: "primary",
          size: "mini"
        })
      } : {}, {
        j: common_vendor.p({
          type: "right",
          color: "#999999"
        }),
        k: common_vendor.o(action)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-a82cc4c5"]]);
wx.createComponent(Component);
