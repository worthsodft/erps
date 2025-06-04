"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-order-sub-item2",
  props: /* @__PURE__ */ common_vendor.mergeModels({
    item: Object
  }, {
    "modelValue": {},
    "modelModifiers": {}
  }),
  emits: ["update:modelValue"],
  setup(__props) {
    common_vendor.ref("");
    const props = __props;
    const number = common_vendor.useModel(__props, "modelValue");
    number.value = props.item.min_buy_number || 1;
    function detail() {
      common_vendor.index.$tool.navto("/pages/goods/detail?sn=" + props.item.goods_sn);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.item.goods_cover,
        b: common_vendor.t(__props.item.goods_name),
        c: common_vendor.t(__props.item.goods_min_buy_number),
        d: common_vendor.t(__props.item.goods_unit),
        e: __props.item.goods_deliver_fee
      }, __props.item.goods_deliver_fee ? {
        f: common_vendor.t(parseFloat(__props.item.goods_deliver_fee)),
        g: common_vendor.t(__props.item.goods_unit)
      } : {}, {
        h: common_vendor.t(__props.item.goods_self_price),
        i: common_vendor.t(__props.item.goods_number),
        j: common_vendor.t(__props.item.goods_unit),
        k: common_vendor.o(detail)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-0d42b7b0"]]);
wx.createComponent(Component);
