"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-button-w80",
  props: {
    type: String,
    disabled: Boolean,
    title: {
      type: String,
      default: "确 定"
    },
    style: {
      type: Object,
      default: {}
    }
  },
  emits: ["action"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function action() {
      emit("action");
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.disabled
      }, __props.disabled ? {
        b: common_vendor.t(__props.title)
      } : __props.type == "normal" ? {
        d: common_vendor.t(__props.title),
        e: common_vendor.o(action)
      } : {
        f: common_vendor.t(__props.title),
        g: common_vendor.o(action)
      }, {
        c: __props.type == "normal",
        h: common_vendor.s(__props.style)
      });
    };
  }
};
wx.createComponent(_sfc_main);
