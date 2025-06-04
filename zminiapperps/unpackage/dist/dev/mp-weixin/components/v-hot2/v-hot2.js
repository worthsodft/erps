"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_uni_section2 = common_vendor.resolveComponent("uni-section");
  const _easycom_v_goods_item32 = common_vendor.resolveComponent("v-goods-item3");
  (_easycom_uni_icons2 + _easycom_uni_section2 + _easycom_v_goods_item32)();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_uni_section = () => "../../uni_modules/uni-section/components/uni-section/uni-section.js";
const _easycom_v_goods_item3 = () => "../v-goods-item3/v-goods-item3.js";
if (!Math) {
  (_easycom_uni_icons + _easycom_uni_section + _easycom_v_goods_item3)();
}
const _sfc_main = {
  __name: "v-hot2",
  props: {
    list: Array,
    title: {
      type: String,
      default: "热销商品"
    },
    path: {
      type: String,
      default: "/pages/goods/list"
    }
  },
  setup(__props) {
    const props = __props;
    function more() {
      common_vendor.index.$tool.navto(props.path);
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.p({
          type: "right",
          color: "#333"
        }),
        b: common_vendor.o(more),
        c: common_vendor.p({
          title: __props.title,
          type: "line"
        }),
        d: common_vendor.f(__props.list, (item, k0, i0) => {
          return {
            a: "ed49ccd5-2-" + i0,
            b: common_vendor.p({
              item
            }),
            c: item.id
          };
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-ed49ccd5"]]);
wx.createComponent(Component);
