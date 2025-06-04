"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_coupon_item2 = common_vendor.resolveComponent("v-coupon-item");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  (_easycom_v_coupon_item2 + _easycom_uni_popup2)();
}
const _easycom_v_coupon_item = () => "../v-coupon-item/v-coupon-item.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
if (!Math) {
  (_easycom_v_coupon_item + _easycom_uni_popup)();
}
const _sfc_main = {
  __name: "v-coupon-select",
  props: {
    list: Array
  },
  emits: ["select", "reset"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const couponPopup = common_vendor.ref();
    const emit = __emit;
    function open() {
      couponPopup.value.open("bottom");
    }
    function close() {
      couponPopup.value.close();
    }
    function select(e) {
      emit("select", e);
    }
    function reset(e) {
      emit("reset");
    }
    function couponPopupChange(e) {
    }
    __expose({
      open,
      close
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(__props.list, (item, index, i0) => {
          return {
            a: item.id,
            b: common_vendor.o(select, item.id),
            c: "d620671b-1-" + i0 + ",d620671b-0",
            d: common_vendor.p({
              item,
              type: "select",
              mb: true,
              fullWidth: true
            })
          };
        }),
        b: common_vendor.o(reset),
        c: common_vendor.sr(couponPopup, "d620671b-0", {
          "k": "couponPopup"
        }),
        d: common_vendor.o(couponPopupChange),
        e: common_vendor.p({
          ["background-color"]: "#fff"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-d620671b"]]);
wx.createComponent(Component);
