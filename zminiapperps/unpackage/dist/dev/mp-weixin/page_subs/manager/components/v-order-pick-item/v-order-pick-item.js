"use strict";
const common_vendor = require("../../../../common/vendor.js");
const _sfc_main = {
  __name: "v-order-pick-item",
  props: {
    item: Object,
    index: Number
  },
  emits: ["pick"],
  setup(__props, { emit: __emit }) {
    const $enum = common_vendor.ref(common_vendor.index.$enum);
    const emit = __emit;
    const props = __props;
    function pick() {
      emit("pick", props.item);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.item.station_title),
        b: common_vendor.t(__props.item.gid_),
        c: common_vendor.t(__props.item.username),
        d: common_vendor.t(__props.item.create_at),
        e: common_vendor.t(__props.item.goods_total || ""),
        f: common_vendor.f(__props.item.goods_list, (sub, index, i0) => {
          return {
            a: common_vendor.t(sub.goods_name),
            b: common_vendor.t(sub.goods_total),
            c: common_vendor.t(sub.goods_unit),
            d: sub.id
          };
        }),
        g: __props.item.remark
      }, __props.item.remark ? {
        h: common_vendor.t(__props.item.remark)
      } : {}, {
        i: __props.item.confirm_by
      }, __props.item.confirm_by ? {
        j: common_vendor.t(__props.item.confirm_by_username)
      } : {}, {
        k: __props.item.confirm_at
      }, __props.item.confirm_at ? {
        l: common_vendor.t(__props.item.confirm_at)
      } : {}, {
        m: __props.item.status == $enum.value.orderPick.STATUS_PICK_NOT
      }, __props.item.status == $enum.value.orderPick.STATUS_PICK_NOT ? {} : __props.item.status == $enum.value.orderPick.STATUS_PICK_YES ? {} : {}, {
        n: __props.item.status == $enum.value.orderPick.STATUS_PICK_YES,
        o: __props.item.status == $enum.value.orderPick.STATUS_PICK_NOT
      }, __props.item.status == $enum.value.orderPick.STATUS_PICK_NOT ? {
        p: common_vendor.o(pick)
      } : {});
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-a1f5f4f0"]]);
wx.createComponent(Component);
