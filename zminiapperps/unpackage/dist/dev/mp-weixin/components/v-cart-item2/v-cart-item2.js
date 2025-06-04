"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_v_number_box2 = common_vendor.resolveComponent("v-number-box");
  (_easycom_uni_icons2 + _easycom_v_number_box2)();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_v_number_box = () => "../v-number-box/v-number-box.js";
if (!Math) {
  (_easycom_uni_icons + _easycom_v_number_box)();
}
const _sfc_main = {
  __name: "v-cart-item2",
  props: {
    item: Object,
    index: [String, Number]
  },
  emits: ["delItem", "numbeChange", "check"],
  setup(__props, { emit: __emit }) {
    common_vendor.ref("");
    const props = __props;
    const emit = __emit;
    const check = () => {
      emit("check", { index: props.index, is_checked: !props.item.is_checked });
    };
    function numbeChange(number) {
      emit("numbeChange", { index: props.index, number });
    }
    function delItem() {
      emit("delItem", props.index);
    }
    function detail() {
      common_vendor.index.$tool.navto("/pages/goods/detail?sn=" + props.item.sn);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.item.is_checked
      }, __props.item.is_checked ? {
        b: common_vendor.p({
          type: "checkbox-filled",
          size: "20",
          color: "#5aa1d8"
        })
      } : {
        c: common_vendor.p({
          type: "checkbox",
          size: "20",
          color: "#ccc"
        })
      }, {
        d: common_vendor.o(check),
        e: common_vendor.o(detail),
        f: __props.item.cover,
        g: common_vendor.t(__props.item.name),
        h: common_vendor.o(detail),
        i: common_vendor.t(__props.item.min_buy_number),
        j: common_vendor.t(__props.item.unit),
        k: common_vendor.t(parseFloat(__props.item.deliver_fee)),
        l: common_vendor.t(__props.item.unit),
        m: common_vendor.t(__props.item.self_price),
        n: common_vendor.t(__props.item.unit),
        o: common_vendor.t(__props.item.amount),
        p: common_vendor.o(numbeChange),
        q: common_vendor.o(($event) => __props.item.number = $event),
        r: common_vendor.p({
          min: 1,
          max: __props.item.stock,
          width: 30,
          background: "#5aa1d830",
          color: "#8A8A8A",
          modelValue: __props.item.number
        }),
        s: common_vendor.o(() => {
        }),
        t: common_vendor.o(delItem)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-010683e5"]]);
wx.createComponent(Component);
