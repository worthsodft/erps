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
  __name: "v-order-item2",
  props: /* @__PURE__ */ common_vendor.mergeModels({
    item: Object,
    isInvoice: Boolean
  }, {
    "modelValue": {},
    "modelModifiers": {}
  }),
  emits: /* @__PURE__ */ common_vendor.mergeModels(["takeSuccess", "cancelSuccess", "refundApplySuccess", "showFinishOrderPopup", "showRefundReasonPopup", "selectInvoice"], ["update:modelValue"]),
  setup(__props, { emit: __emit }) {
    const $enum = common_vendor.ref(common_vendor.index.$enum);
    common_vendor.ref("");
    const props = __props;
    const number = common_vendor.useModel(__props, "modelValue");
    number.value = props.item.min_buy_number || 1;
    common_vendor.ref();
    common_vendor.ref();
    common_vendor.ref();
    const emit = __emit;
    function selectInvoice() {
      emit("selectInvoice", props.item.sn);
    }
    async function showQrcode() {
      let qrcode = await getQrcode();
      emit("showFinishOrderPopup", { qrcode, sn: props.item.sn });
    }
    async function getQrcode() {
      const res = await common_vendor.index.$http.post("v1/getFinishOrderQrcode/" + props.item.sn);
      return res.data.url;
    }
    function confirm() {
      common_vendor.index.$tool.navto("/pages/order/confirm?sn=" + props.item.sn);
    }
    function refund() {
      if (props.item.pay_type == "giftcard")
        return common_vendor.index.$tool.tip("实物卡支付方式暂不支持退款");
      if (props.item.goods_type == 2)
        return common_vendor.index.$tool.tip("实物卡商品订单暂不支持退款");
      emit("showRefundReasonPopup", { sn: props.item.sn });
    }
    function cancel() {
      common_vendor.index.$tool.confirm("确定要取消订单吗？", () => {
        doCancelOrder();
      });
    }
    function doCancelOrder() {
      common_vendor.index.$http.post("v1/cancelOrder/" + props.item.sn).then((res) => {
        if (res.code == 1)
          common_vendor.index.$tool.success(res.info, true, () => {
            emit("cancelSuccess", res.data.order);
          });
        else
          common_vendor.index.$tool.tip(res.info || "取消失败");
      });
    }
    function take() {
      common_vendor.index.$tool.confirm("确定要进行收货操作吗？", () => {
        doTakeOrder();
      });
    }
    function doTakeOrder() {
      common_vendor.index.$http.post("v1/takeOrder/" + props.item.sn).then((res) => {
        if (res.code == 1)
          common_vendor.index.$tool.success(res.info, true, () => {
            emit("takeSuccess", res.data.order);
          });
        else
          common_vendor.index.$tool.tip(res.info || "收货失败");
      });
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.t(__props.item.sn),
        b: __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF ? {} : {}, {
        c: __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER ? {} : {}, {
        d: common_vendor.o(confirm),
        e: common_vendor.f(__props.item.subs, (sub, index, i0) => {
          return {
            a: sub.goods_cover,
            b: sub.id
          };
        }),
        f: common_vendor.o(confirm),
        g: common_vendor.t(__props.item.pay_amount),
        h: common_vendor.t(__props.item.create_at),
        i: __props.item.pay_at
      }, __props.item.pay_at ? {
        j: common_vendor.t(__props.item.pay_at)
      } : {}, {
        k: __props.item.invoice_no
      }, __props.item.invoice_no ? {
        l: common_vendor.t(__props.item.invoice_no)
      } : {}, {
        m: __props.item.status == $enum.value.order.STATUS_UNPAY
      }, __props.item.status == $enum.value.order.STATUS_UNPAY ? {} : __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_NOT ? {} : __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_ING ? {} : __props.item.status == $enum.value.order.STATUS_FINISHED ? {} : __props.item.status == $enum.value.order.STATUS_REFUND ? common_vendor.e({
        r: common_vendor.t(__props.item.refund_status_txt),
        s: __props.item.refund_feedback_msg
      }, __props.item.refund_feedback_msg ? {
        t: common_vendor.t(__props.item.refund_feedback_msg)
      } : {}) : {}, {
        n: __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_NOT,
        o: __props.item.status == $enum.value.order.STATUS_DELIVERING && __props.item.deliver_status == $enum.value.order.DELIVER_STATUS_ING,
        p: __props.item.status == $enum.value.order.STATUS_FINISHED,
        q: __props.item.status == $enum.value.order.STATUS_REFUND,
        v: __props.item.status == $enum.value.order.STATUS_REFUND ? 1 : "",
        w: __props.item.status == $enum.value.order.STATUS_UNPAY
      }, __props.item.status == $enum.value.order.STATUS_UNPAY ? {
        x: common_vendor.o(cancel),
        y: common_vendor.o(confirm)
      } : __props.item.status == $enum.value.order.STATUS_DELIVERING ? common_vendor.e({
        A: __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_SELF ? {
        B: common_vendor.o(showQrcode)
      } : {}, {
        C: common_vendor.o(refund),
        D: __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER
      }, __props.item.take_type == $enum.value.order.TAKE_TYPE_DELIVER ? {
        E: common_vendor.o(take)
      } : {}) : __props.item.status == $enum.value.order.STATUS_FINISHED ? common_vendor.e({
        G: !__props.item.invoice_no && __props.item.invoice_apply_at
      }, !__props.item.invoice_no && __props.item.invoice_apply_at ? {} : !__props.item.invoice_no ? common_vendor.e({
        I: __props.isInvoice
      }, __props.isInvoice ? {
        J: common_vendor.p({
          type: "checkbox",
          size: "22",
          color: "#da4754"
        })
      } : {
        K: common_vendor.p({
          type: "checkbox",
          size: "22"
        })
      }, {
        L: common_vendor.o(selectInvoice)
      }) : {}, {
        H: !__props.item.invoice_no,
        M: common_vendor.o(confirm)
      }) : __props.item.status == $enum.value.order.STATUS_REFUND ? {
        O: common_vendor.o(confirm)
      } : {}, {
        z: __props.item.status == $enum.value.order.STATUS_DELIVERING,
        F: __props.item.status == $enum.value.order.STATUS_FINISHED,
        N: __props.item.status == $enum.value.order.STATUS_REFUND
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-45dc32da"]]);
wx.createComponent(Component);
