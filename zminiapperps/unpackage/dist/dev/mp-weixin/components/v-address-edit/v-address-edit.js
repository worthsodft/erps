"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_tag2 = common_vendor.resolveComponent("uni-tag");
  _easycom_uni_tag2();
}
const _easycom_uni_tag = () => "../../uni_modules/uni-tag/components/uni-tag/uni-tag.js";
if (!Math) {
  _easycom_uni_tag();
}
const _sfc_main = {
  __name: "v-address-edit",
  props: {
    item: Object,
    index: [String, Number],
    source: String
  },
  emits: ["delItem"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const info = props.item.province + ", " + props.item.city + ", " + props.item.district + ", " + props.item.street;
    const infoStr = common_vendor.ref(info);
    const emit = __emit;
    function delItem() {
      emit("delItem", props.index);
    }
    function select() {
      common_vendor.index.$tool.navto("/pages/order/confirm?agid=" + props.item.gid);
    }
    function edit() {
      common_vendor.index.$tool.navto("/pages/my/address-edit?gid=" + props.item.gid);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(infoStr.value),
        b: common_vendor.t(__props.item.detail),
        c: __props.item.is_default == 1
      }, __props.item.is_default == 1 ? {
        d: common_vendor.p({
          type: "warning",
          text: "默认"
        })
      } : {}, {
        e: __props.source == "order"
      }, __props.source == "order" ? {
        f: common_vendor.o(select)
      } : {}, {
        g: common_vendor.o(edit),
        h: common_vendor.o(delItem)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-e0d41c80"]]);
wx.createComponent(Component);
