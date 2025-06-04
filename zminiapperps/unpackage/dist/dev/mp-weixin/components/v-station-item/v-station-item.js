"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  _easycom_uni_icons2();
}
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  _easycom_uni_icons();
}
const _sfc_main = {
  __name: "v-station-item",
  props: {
    item: Object
  },
  emits: ["action"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    function action() {
      if (props.item.is_open != 1)
        return common_vendor.index.$tool.tip("当前水站已暂停营业", false, null, 3);
      emit("action", props.item);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.item.is_open != 1
      }, __props.item.is_open != 1 ? {} : {}, {
        b: common_vendor.t(__props.item.title),
        c: __props.item.detail
      }, __props.item.detail ? {
        d: common_vendor.t(__props.item.province ? __props.item.province + ", " : ""),
        e: common_vendor.t(__props.item.city ? __props.item.city + ", " : ""),
        f: common_vendor.t(__props.item.district ? __props.item.district + ", " : ""),
        g: common_vendor.t(__props.item.street ? __props.item.street + ", " : ""),
        h: common_vendor.t(__props.item.detail)
      } : {}, {
        i: __props.item.link_name
      }, __props.item.link_name ? {
        j: common_vendor.t(__props.item.link_name)
      } : {}, {
        k: __props.item.link_phone
      }, __props.item.link_phone ? {
        l: common_vendor.t(__props.item.link_phone)
      } : {}, {
        m: __props.item.open_time
      }, __props.item.open_time ? {
        n: common_vendor.t(__props.item.open_time)
      } : {}, {
        o: __props.item.remark
      }, __props.item.remark ? {
        p: common_vendor.t(__props.item.remark)
      } : {}, {
        q: __props.item.distance
      }, __props.item.distance ? {
        r: common_vendor.t(__props.item.distance)
      } : {}, {
        s: common_vendor.p({
          type: "right",
          color: "#999999"
        }),
        t: common_vendor.o(action)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-c229d901"]]);
wx.createComponent(Component);
