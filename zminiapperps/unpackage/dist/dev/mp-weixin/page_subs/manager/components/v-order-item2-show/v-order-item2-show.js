"use strict";
const common_vendor = require("../../../../common/vendor.js");
if (!Array) {
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  _easycom_uni_icons2();
}
const _easycom_uni_icons = () => "../../../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  _easycom_uni_icons();
}
const _sfc_main = {
  __name: "v-order-item2-show",
  props: {
    item: Object,
    index: Number
  },
  emits: ["select"],
  setup(__props, { emit: __emit }) {
    const $enum = common_vendor.ref(common_vendor.index.$enum);
    const emit = __emit;
    const props = __props;
    function showOrderPickPopup() {
      if (!props.item.pick_gid)
        return common_vendor.index.$tool.tip("配货单号不能为空");
      console.log(props.item.pick_gid);
    }
    function select() {
      emit("select", props.index);
    }
    function callPhone(phone) {
      if (!phone)
        return;
      common_vendor.index.makePhoneCall({
        phoneNumber: phone
      });
    }
    function confirm() {
      common_vendor.index.$tool.navto("/pages/order/detail?sn=" + props.item.sn);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.item.sn),
        b: __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF ? {} : __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER ? {} : {}, {
        c: __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER,
        d: common_vendor.o(confirm),
        e: __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF ? {
        f: common_vendor.t(__props.item.station_title),
        g: common_vendor.t(__props.item.station_address),
        h: common_vendor.t("\n"),
        i: common_vendor.t(__props.item.station_link_name),
        j: common_vendor.t(__props.item.station_link_phone),
        k: common_vendor.o(($event) => callPhone(__props.item.station_link_phone))
      } : {}, {
        l: __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER ? {
        m: common_vendor.t(__props.item.take_address),
        n: common_vendor.t("\n"),
        o: common_vendor.t(__props.item.take_name),
        p: common_vendor.t(__props.item.take_phone),
        q: common_vendor.o(($event) => callPhone(__props.item.take_phone))
      } : {}, {
        r: common_vendor.t(__props.item.pay_at),
        s: common_vendor.f(__props.item.subs, (sub, index, i0) => {
          return {
            a: common_vendor.t(sub.goods_name),
            b: common_vendor.t(sub.goods_number),
            c: common_vendor.t(sub.goods_unit),
            d: sub.id
          };
        }),
        t: common_vendor.o(confirm),
        v: common_vendor.t(__props.item.remark || "无"),
        w: common_vendor.o(confirm),
        x: __props.item.refund_status != 0
      }, __props.item.refund_status != 0 ? {
        y: common_vendor.t(__props.item.refund_status_txt || "无"),
        z: common_vendor.o(confirm)
      } : {}, {
        A: __props.item.pick_gid_
      }, __props.item.pick_gid_ ? {
        B: common_vendor.t(__props.item.pick_gid_),
        C: common_vendor.o(showOrderPickPopup)
      } : {}, {
        D: __props.item.pick_by_txt
      }, __props.item.pick_by_txt ? {
        E: common_vendor.t(__props.item.pick_by_txt)
      } : {}, {
        F: __props.item.pick_at
      }, __props.item.pick_at ? {
        G: common_vendor.t(__props.item.pick_at)
      } : {}, {
        H: __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER && __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_NOT
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER && __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_NOT ? common_vendor.e({
        I: __props.item.checked
      }, __props.item.checked ? {
        J: common_vendor.p({
          type: "checkbox-filled",
          size: "20",
          color: "#e43d33"
        })
      } : {
        K: common_vendor.p({
          type: "checkbox",
          size: "20"
        })
      }, {
        L: common_vendor.o(select)
      }) : common_vendor.e({
        M: __props.item.status == $enum.value.order.STATUS_UNPAY
      }, __props.item.status == $enum.value.order.STATUS_UNPAY ? {} : __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_NOT ? {} : __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_ING ? {} : __props.item.status == $enum.value.order.STATUS_FINISHED ? {} : __props.item.status == $enum.value.order.STATUS_REFUND ? common_vendor.e({
        R: common_vendor.t(__props.item.refund_status_txt),
        S: __props.item.refund_feedback_msg
      }, __props.item.refund_feedback_msg ? {
        T: common_vendor.t(__props.item.refund_feedback_msg)
      } : {}) : {}, {
        N: __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_NOT,
        O: __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_ING,
        P: __props.item.status == $enum.value.order.STATUS_FINISHED,
        Q: __props.item.status == $enum.value.order.STATUS_REFUND,
        U: __props.item.status == $enum.value.order.STATUS_REFUND ? 1 : ""
      }), {
        V: common_vendor.o(confirm)
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-b34b089e"]]);
wx.createComponent(Component);
