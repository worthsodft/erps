"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_address_item2 = common_vendor.resolveComponent("v-address-item");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  const _easycom_v_address_form2 = common_vendor.resolveComponent("v-address-form");
  (_easycom_v_address_item2 + _easycom_v_button_w802 + _easycom_uni_popup2 + _easycom_v_address_form2)();
}
const _easycom_v_address_item = () => "../v-address-item/v-address-item.js";
const _easycom_v_button_w80 = () => "../v-button-w80/v-button-w80.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
const _easycom_v_address_form = () => "../v-address-form/v-address-form.js";
if (!Math) {
  (_easycom_v_address_item + _easycom_v_button_w80 + _easycom_uni_popup + _easycom_v_address_form)();
}
const _sfc_main = {
  __name: "v-address-select",
  props: {
    list: Array
  },
  emits: ["select", "reload"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const itemPopup = common_vendor.ref();
    const addressFormRef = common_vendor.ref();
    const emit = __emit;
    function add() {
      addressFormRef.value.getCities();
      addressFormRef.value.reset();
      addressFormRef.value.open();
    }
    function saveSuccess(item) {
      selectItem(item);
      emit("reload");
      addressFormRef.value.close();
    }
    function open() {
      itemPopup.value.open("bottom");
    }
    function close() {
      itemPopup.value.close();
    }
    function selectItem(item) {
      emit("select", item);
    }
    function popupChange(e) {
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
            b: common_vendor.o(($event) => selectItem(item), item.id),
            c: "ffc194d3-1-" + i0 + ",ffc194d3-0",
            d: common_vendor.p({
              item
            })
          };
        }),
        b: common_vendor.o(add),
        c: common_vendor.p({
          title: "添加地址"
        }),
        d: common_vendor.sr(itemPopup, "ffc194d3-0", {
          "k": "itemPopup"
        }),
        e: common_vendor.o(popupChange),
        f: common_vendor.p({
          ["background-color"]: "#fff"
        }),
        g: common_vendor.sr(addressFormRef, "ffc194d3-3", {
          "k": "addressFormRef"
        }),
        h: common_vendor.o(saveSuccess)
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-ffc194d3"]]);
wx.createComponent(Component);
