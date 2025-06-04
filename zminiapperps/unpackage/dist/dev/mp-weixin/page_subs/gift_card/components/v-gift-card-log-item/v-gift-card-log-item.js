"use strict";
const common_vendor = require("../../../../common/vendor.js");
if (!Array) {
  const _easycom_uni_tag2 = common_vendor.resolveComponent("uni-tag");
  _easycom_uni_tag2();
}
const _easycom_uni_tag = () => "../../../../uni_modules/uni-tag/components/uni-tag/uni-tag.js";
if (!Math) {
  _easycom_uni_tag();
}
const _sfc_main = {
  __name: "v-gift-card-log-item",
  props: {
    item: Object
  },
  setup(__props) {
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.item.card_sn),
        b: __props.item.use_type == 0
      }, __props.item.use_type == 0 ? {
        c: common_vendor.t(__props.item.delta),
        d: common_vendor.t(__props.item.has)
      } : __props.item.use_type == 1 ? {
        f: common_vendor.t(__props.item.delta),
        g: common_vendor.t(__props.item.has),
        h: common_vendor.t(__props.item.take_goods_sn_txt)
      } : {}, {
        e: __props.item.use_type == 1,
        i: __props.item.order_sn
      }, __props.item.order_sn ? {
        j: common_vendor.t(__props.item.order_sn)
      } : {}, {
        k: common_vendor.t(__props.item.create_at),
        l: common_vendor.t(__props.item.consume_openid_txt),
        m: __props.item.use_type == 0
      }, __props.item.use_type == 0 ? {
        n: common_vendor.p({
          text: __props.item.use_type_txt,
          type: "primary"
        })
      } : __props.item.use_type == 1 ? {
        p: common_vendor.p({
          text: __props.item.use_type_txt,
          type: "success"
        })
      } : {
        q: common_vendor.p({
          text: "未知",
          type: "disabled"
        })
      }, {
        o: __props.item.use_type == 1
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-702bb178"]]);
wx.createComponent(Component);
