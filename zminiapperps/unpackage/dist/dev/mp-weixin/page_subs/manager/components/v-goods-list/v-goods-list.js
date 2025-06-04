"use strict";
const common_vendor = require("../../../../common/vendor.js");
if (!Array) {
  const _easycom_uni_data_select2 = common_vendor.resolveComponent("uni-data-select");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  (_easycom_uni_data_select2 + _easycom_uni_popup2)();
}
const _easycom_uni_data_select = () => "../../../../uni_modules/uni-data-select/components/uni-data-select/uni-data-select.js";
const _easycom_uni_popup = () => "../../../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
if (!Math) {
  (_easycom_uni_data_select + _easycom_uni_popup)();
}
const _sfc_main = {
  __name: "v-goods-list",
  props: {
    list: Object,
    stationList: Array
  },
  emits: ["confirm"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const itemPopup = common_vendor.ref();
    const station_gid = common_vendor.ref("");
    const emit = __emit;
    function stationChange(gid) {
    }
    function preView(img) {
      common_vendor.index.previewImage({
        urls: [img]
      });
    }
    function confirm(item) {
      if (!station_gid.value)
        return common_vendor.index.$tool.tip("请选择水站信息");
      common_vendor.index.$tool.confirm("确认需要配送的商品无误吗？", () => {
        emit("confirm", station_gid.value);
      });
    }
    function open() {
      itemPopup.value.open("bottom");
    }
    function close() {
      itemPopup.value.close();
    }
    __expose({
      open,
      close
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o(stationChange),
        b: common_vendor.o(($event) => station_gid.value = $event),
        c: common_vendor.p({
          localdata: __props.stationList,
          modelValue: station_gid.value
        }),
        d: common_vendor.f(__props.list, (item, index, i0) => {
          return {
            a: item.goods_cover || "",
            b: common_vendor.t(item.goods_name),
            c: common_vendor.t(item.goods_number),
            d: common_vendor.t(item.goods_unit),
            e: item.goods_sn,
            f: common_vendor.o(($event) => preView(item.goods_cover), item.goods_sn)
          };
        }),
        e: common_vendor.o(confirm),
        f: common_vendor.sr(itemPopup, "49f597c8-0", {
          "k": "itemPopup"
        }),
        g: common_vendor.p({
          ["background-color"]: "#fff"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-49f597c8"]]);
wx.createComponent(Component);
