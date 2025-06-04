"use strict";
const common_vendor = require("../../../common/vendor.js");
if (!Array) {
  const _easycom_uni_section2 = common_vendor.resolveComponent("uni-section");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  (_easycom_uni_section2 + _easycom_v_button_w802)();
}
const _easycom_uni_section = () => "../../../uni_modules/uni-section/components/uni-section/uni-section.js";
const _easycom_v_button_w80 = () => "../../../components/v-button-w80/v-button-w80.js";
if (!Math) {
  (_easycom_uni_section + _easycom_v_button_w80)();
}
const _sfc_main = {
  __name: "order_finish_index",
  setup(__props) {
    const $enum = common_vendor.ref(common_vendor.index.$enum);
    const orderInfo = common_vendor.ref({});
    let sn = "";
    common_vendor.onLoad((opts) => {
      sn = opts.sn || "";
      if (!sn)
        return common_vendor.index.$tool.tip("订单编号不存在", true, () => common_vendor.index.$tool.navto("/page_subs/manager/order_finish/order_finish_welcome"));
      getDetail();
    });
    function getDetail() {
      common_vendor.index.$http.post(`v1/getOrderDetailForFinish/${sn}`).then((res) => {
        orderInfo.value = res.data.order;
      });
    }
    async function doFinish() {
      common_vendor.index.$tool.showLoading();
      const res = await common_vendor.index.$http.post("v1/finishOrder/" + sn);
      common_vendor.index.$tool.hideLoading();
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.alert(res.info || "操作成功", () => {
        orderInfo.value = res.data.order;
      });
    }
    const finish = common_vendor.index.$tool.throttle(() => {
      common_vendor.index.$tool.confirm("确定要核销吗？", () => {
        doFinish();
      });
    });
    return (_ctx, _cache) => {
      var _a;
      return common_vendor.e({
        a: orderInfo.value.station_title
      }, orderInfo.value.station_title ? {
        b: common_vendor.p({
          type: "line",
          titleFontSize: "32rpx",
          title: "水站信息"
        }),
        c: common_vendor.t(orderInfo.value.station_title),
        d: common_vendor.t(orderInfo.value.station_link_name),
        e: common_vendor.t(orderInfo.value.station_link_phone),
        f: common_vendor.t(orderInfo.value.station_address)
      } : {}, {
        g: orderInfo.value.take_name
      }, orderInfo.value.take_name ? {
        h: common_vendor.p({
          type: "line",
          titleFontSize: "32rpx",
          title: "收货信息"
        }),
        i: common_vendor.t(orderInfo.value.take_name),
        j: common_vendor.t(orderInfo.value.take_phone),
        k: common_vendor.t(orderInfo.value.take_address)
      } : {}, {
        l: common_vendor.p({
          type: "line",
          titleFontSize: "32rpx",
          title: "订单信息"
        }),
        m: orderInfo.value.id
      }, orderInfo.value.id ? common_vendor.e({
        n: common_vendor.t(orderInfo.value.sn),
        o: common_vendor.t(orderInfo.value.goods_amount),
        p: common_vendor.t(orderInfo.value.discount_amount || "0.00"),
        q: common_vendor.t(orderInfo.value.real_amount || "0.00"),
        r: common_vendor.t(orderInfo.value.deliver_fee || "0.00"),
        s: common_vendor.t(orderInfo.value.pay_type_txt),
        t: common_vendor.t(((_a = orderInfo.value.coupon) == null ? void 0 : _a.title) || ""),
        v: common_vendor.t(orderInfo.value.take_type_txt),
        w: orderInfo.value.status == 1
      }, orderInfo.value.status == 1 ? {
        x: common_vendor.t(orderInfo.value.status_txt)
      } : {
        y: common_vendor.t(orderInfo.value.status_txt)
      }) : {}, {
        z: common_vendor.p({
          type: "line",
          titleFontSize: "32rpx",
          title: "订单明细"
        }),
        A: common_vendor.f(orderInfo.value.subs, (sub, index, i0) => {
          return {
            a: common_vendor.t(sub.goods_name),
            b: common_vendor.t(sub.goods_number),
            c: common_vendor.t(sub.goods_unit),
            d: common_vendor.t(sub.goods_self_price),
            e: common_vendor.t(sub.goods_amount),
            f: index
          };
        }),
        B: orderInfo.value.remark
      }, orderInfo.value.remark ? {
        C: common_vendor.p({
          type: "line",
          titleFontSize: "32rpx",
          title: "订单备注"
        }),
        D: common_vendor.t(orderInfo.value.remark)
      } : {}, {
        E: orderInfo.value.status == $enum.value.order.STATUS_DELIVERING
      }, orderInfo.value.status == $enum.value.order.STATUS_DELIVERING ? {
        F: common_vendor.o(common_vendor.unref(finish)),
        G: common_vendor.p({
          title: "确认核销"
        })
      } : {
        H: common_vendor.p({
          disabled: true,
          title: "确认核销"
        })
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-92498e1b"]]);
wx.createPage(MiniProgramPage);
