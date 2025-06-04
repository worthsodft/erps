"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_v_station_item2 = common_vendor.resolveComponent("v-station-item");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  (_easycom_uni_icons2 + _easycom_v_station_item2 + _easycom_uni_popup2)();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_v_station_item = () => "../v-station-item/v-station-item.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
if (!Math) {
  (_easycom_uni_icons + _easycom_v_station_item + _easycom_uni_popup)();
}
const _sfc_main = {
  __name: "v-station-select",
  props: {
    list: Array
  },
  emits: ["select"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const itemPopup = common_vendor.ref();
    const emit = __emit;
    const map = () => {
      common_vendor.index.$tool.tip("水站地图功能开发中，敬请期待...", false, null, 3);
    };
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
        a: common_vendor.p({
          type: "location",
          color: "#5aa1d8"
        }),
        b: common_vendor.o(map),
        c: common_vendor.f(__props.list, (item, index, i0) => {
          return {
            a: item.id,
            b: common_vendor.o(($event) => selectItem(item), item.id),
            c: "9078bdc1-2-" + i0 + ",9078bdc1-0",
            d: common_vendor.p({
              item
            })
          };
        }),
        d: common_vendor.sr(itemPopup, "9078bdc1-0", {
          "k": "itemPopup"
        }),
        e: common_vendor.o(popupChange),
        f: common_vendor.p({
          ["background-color"]: "#fff"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-9078bdc1"]]);
wx.createComponent(Component);
