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
  __name: "v-money-log-item",
  props: {
    item: Object
  },
  setup(__props) {
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.item.before),
        b: __props.item.delta < 0
      }, __props.item.delta < 0 ? {
        c: common_vendor.t(0 - __props.item.delta)
      } : {
        d: common_vendor.t(__props.item.delta)
      }, {
        e: __props.item.target_gid
      }, __props.item.target_gid ? {
        f: common_vendor.t(__props.item.target_gid)
      } : {}, {
        g: common_vendor.t(__props.item.create_at),
        h: __props.item.log_type == "order"
      }, __props.item.log_type == "order" ? {
        i: common_vendor.p({
          text: __props.item.log_type_txt,
          type: "primary"
        })
      } : __props.item.log_type == "recharge" ? {
        k: common_vendor.p({
          text: __props.item.log_type_txt,
          type: "success"
        })
      } : __props.item.log_type == "give" ? {
        m: common_vendor.p({
          text: __props.item.log_type_txt,
          type: "warning"
        })
      } : __props.item.log_type == "refund" ? {
        o: common_vendor.p({
          text: __props.item.log_type_txt,
          type: "error"
        })
      } : __props.item.log_type == "import" ? {
        q: common_vendor.p({
          text: __props.item.log_type_txt,
          type: "success"
        })
      } : {
        r: common_vendor.p({
          text: "未知",
          type: "disabled"
        })
      }, {
        j: __props.item.log_type == "recharge",
        l: __props.item.log_type == "give",
        n: __props.item.log_type == "refund",
        p: __props.item.log_type == "import"
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-751cce28"]]);
wx.createComponent(Component);
