"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-order-confirm-bar2",
  props: {
    action: String,
    money: [String, Number],
    number: [String, Number],
    sn: String
  },
  emits: ["createOrder", "payOrder", "navto", "deliver"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function createOrder() {
      emit("createOrder");
    }
    function payOrder() {
      emit("payOrder");
    }
    function navto() {
      emit("navto");
    }
    function deliver() {
      emit("deliver");
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.money),
        b: __props.action == "pay"
      }, __props.action == "pay" ? {
        c: common_vendor.o(payOrder)
      } : __props.action == "create" ? {
        e: common_vendor.o(createOrder)
      } : __props.action == "navto" ? {
        g: common_vendor.o(navto)
      } : __props.action == "deliver" ? {
        i: common_vendor.o(deliver)
      } : {}, {
        d: __props.action == "create",
        f: __props.action == "navto",
        h: __props.action == "deliver"
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-2b8ae5a3"]]);
wx.createComponent(Component);
