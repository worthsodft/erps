"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_data_picker2 = common_vendor.resolveComponent("uni-data-picker");
  _easycom_uni_data_picker2();
}
const _easycom_uni_data_picker = () => "../../uni_modules/uni-data-picker/components/uni-data-picker/uni-data-picker.js";
if (!Math) {
  _easycom_uni_data_picker();
}
const _sfc_main = {
  __name: "v-address-picker",
  props: {
    value: String,
    cities: Array
  },
  emits: ["change"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    function change({ detail }) {
      emit("change", detail.value);
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o(change),
        b: common_vendor.p({
          localdata: __props.cities,
          map: {
            value: "name",
            text: "name"
          },
          value: __props.value,
          placeholder: "请选择区域",
          ["popup-title"]: "请选择地址"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-fd66be24"]]);
wx.createComponent(Component);
