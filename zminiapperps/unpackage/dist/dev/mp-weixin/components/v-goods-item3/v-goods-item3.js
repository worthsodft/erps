"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_number_box2 = common_vendor.resolveComponent("v-number-box");
  _easycom_v_number_box2();
}
const _easycom_v_number_box = () => "../v-number-box/v-number-box.js";
if (!Math) {
  _easycom_v_number_box();
}
const _sfc_main = {
  __name: "v-goods-item3",
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
    number.value = props.item.number || 0;
    common_vendor.watchEffect(() => {
      number.value = props.item.number || 0;
    });
    function detail() {
      common_vendor.index.$tool.navto("/pages/goods/detail?sn=" + props.item.sn);
    }
    function update2cart(e) {
      number.value = e;
      let params = {
        sn: props.item.sn,
        number: number.value
      };
      common_vendor.index.$api.update2cart(params);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.item.cover,
        b: common_vendor.t(__props.item.name),
        c: common_vendor.t(__props.item.min_buy_number),
        d: common_vendor.t(__props.item.unit),
        e: __props.item.deliver_fee > 0
      }, __props.item.deliver_fee > 0 ? {
        f: common_vendor.t(parseInt(__props.item.deliver_fee)),
        g: common_vendor.t(__props.item.unit)
      } : {}, {
        h: common_vendor.t(__props.item.self_price),
        i: common_vendor.t(__props.item.unit),
        j: parseFloat(__props.item.market_price) != 0
      }, parseFloat(__props.item.market_price) != 0 ? {
        k: common_vendor.t(__props.item.market_price)
      } : {}, {
        l: common_vendor.o(update2cart),
        m: common_vendor.p({
          value: number.value,
          min: 0,
          width: 20,
          background: "#5aa1d830",
          color: "#5aa1d8"
        }),
        n: common_vendor.o(() => {
        }),
        o: common_vendor.o(detail)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-1d5562b6"]]);
wx.createComponent(Component);
