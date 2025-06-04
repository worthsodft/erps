"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_data_checkbox2 = common_vendor.resolveComponent("uni-data-checkbox");
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_v_order_sub_item22 = common_vendor.resolveComponent("v-order-sub-item2");
  const _easycom_v_order_info_item2 = common_vendor.resolveComponent("v-order-info-item");
  const _easycom_v_order_confirm_bar22 = common_vendor.resolveComponent("v-order-confirm-bar2");
  const _easycom_v_deliver_images2 = common_vendor.resolveComponent("v-deliver-images");
  (_easycom_uni_data_checkbox2 + _easycom_uni_icons2 + _easycom_v_order_sub_item22 + _easycom_v_order_info_item2 + _easycom_v_order_confirm_bar22 + _easycom_v_deliver_images2)();
}
const _easycom_uni_data_checkbox = () => "../../uni_modules/uni-data-checkbox/components/uni-data-checkbox/uni-data-checkbox.js";
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_v_order_sub_item2 = () => "../../components/v-order-sub-item2/v-order-sub-item2.js";
const _easycom_v_order_info_item = () => "../../components/v-order-info-item/v-order-info-item.js";
const _easycom_v_order_confirm_bar2 = () => "../../components/v-order-confirm-bar2/v-order-confirm-bar2.js";
const _easycom_v_deliver_images = () => "../../components/v-deliver-images/v-deliver-images.js";
if (!Math) {
  (_easycom_uni_data_checkbox + _easycom_uni_icons + _easycom_v_order_sub_item2 + _easycom_v_order_info_item + _easycom_v_order_confirm_bar2 + _easycom_v_deliver_images)();
}
const _sfc_main = {
  __name: "detail",
  props: {
    "modelValue": {},
    "modelModifiers": {}
  },
  emits: ["update:modelValue"],
  setup(__props) {
    common_vendor.ref(common_vendor.index.$enum);
    common_vendor.ref("去使用");
    const from = common_vendor.ref();
    const orderInfo = common_vendor.ref({});
    const freeDeliverTxt = common_vendor.ref("");
    let takeTypes = common_vendor.ref([]);
    let preParams = {};
    let take_type = -1;
    let remark = common_vendor.useModel(__props, "modelValue");
    const deliverImagesRef = common_vendor.ref();
    const images = common_vendor.ref([]);
    function showImage(index) {
      common_vendor.index.previewImage({
        current: index,
        urls: orderInfo.value.deliver_images
      });
    }
    function deliver() {
      deliverImagesRef.value.open();
    }
    function deliverSuccess() {
      getOrderDetail();
    }
    const coupon = common_vendor.ref({});
    let address = common_vendor.ref({});
    let station = common_vendor.ref({});
    let payTypes = common_vendor.ref({ weixin: "微信", yue: "余额" });
    let pay_type = common_vendor.ref("");
    let sn = "";
    common_vendor.onLoad((opts) => {
      getInitDataConfirm();
      if (opts.sn) {
        sn = opts.sn;
        doDetailInfo();
      }
    });
    const copySn = () => {
      const txt = "订单编号：" + orderInfo.value.sn;
      common_vendor.index.setClipboardData({
        data: txt,
        success(res) {
          common_vendor.index.$tool.tip("订单编号已复制");
        }
      });
    };
    function doDetailInfo() {
      common_vendor.index.$tool.setNavTitle("订单查看");
      common_vendor.index.$login.judgeLogin(() => {
        getOrderDetail();
      });
    }
    function getOrderDetail() {
      common_vendor.index.$tool.showLoading();
      common_vendor.index.$http.post("v1/getOrderDetail/" + sn).then((res) => {
        common_vendor.index.$tool.hideLoading();
        orderInfo.value = res.data.order;
        remark.value = orderInfo.value.remark;
        pay_type.value = orderInfo.value.pay_type || "";
        station.value = res.data.station;
        address.value = res.data.address;
        coupon.value = res.data.coupon;
        payTypes.value = res.data.payTypes;
      });
    }
    function getInitDataConfirm() {
      common_vendor.index.$http.post("v1/getInitDataConfirm", preParams).then((res) => {
        takeTypes.value = res.data.takeTypes;
      });
    }
    function back() {
      common_vendor.index.$tool.back();
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: from.value
      }, from.value ? common_vendor.e({
        b: common_vendor.o(_ctx.takeTypeChange),
        c: common_vendor.o(($event) => common_vendor.isRef(take_type) ? take_type.value = $event : take_type = $event),
        d: common_vendor.p({
          localdata: common_vendor.unref(takeTypes),
          modelValue: common_vendor.unref(take_type)
        }),
        e: common_vendor.unref(take_type) == 0
      }, common_vendor.unref(take_type) == 0 ? common_vendor.e({
        f: common_vendor.unref(station) && common_vendor.unref(station).gid
      }, common_vendor.unref(station) && common_vendor.unref(station).gid ? {
        g: common_vendor.t(common_vendor.unref(station).title),
        h: common_vendor.t("\n"),
        i: common_vendor.t(common_vendor.unref(station).province ? common_vendor.unref(station).province + ", " : ""),
        j: common_vendor.t(common_vendor.unref(station).city ? _ctx.item.city + ", " : ""),
        k: common_vendor.t(common_vendor.unref(station).district ? common_vendor.unref(station).district + ", " : ""),
        l: common_vendor.t(common_vendor.unref(station).street ? common_vendor.unref(station).street + ", " : ""),
        m: common_vendor.t(common_vendor.unref(station).detail),
        n: common_vendor.t("\n"),
        o: common_vendor.t(common_vendor.unref(station).link_name),
        p: common_vendor.t(common_vendor.unref(station).link_phone)
      } : {}, {
        q: common_vendor.p({
          type: "right",
          color: "#999999"
        }),
        r: common_vendor.o((...args) => _ctx.showStationPopup && _ctx.showStationPopup(...args))
      }) : common_vendor.unref(take_type) == 1 ? common_vendor.e({
        t: common_vendor.unref(address) && common_vendor.unref(address).gid
      }, common_vendor.unref(address) && common_vendor.unref(address).gid ? {
        v: common_vendor.t(common_vendor.unref(address).province ? common_vendor.unref(address).province + ", " : ""),
        w: common_vendor.t(common_vendor.unref(address).city ? common_vendor.unref(address).city + ", " : ""),
        x: common_vendor.t(common_vendor.unref(address).district ? common_vendor.unref(address).district + ", " : ""),
        y: common_vendor.t(common_vendor.unref(address).street ? common_vendor.unref(address).street + ", " : ""),
        z: common_vendor.t(common_vendor.unref(address).detail),
        A: common_vendor.t("\n"),
        B: common_vendor.t(common_vendor.unref(address).link_name),
        C: common_vendor.t(common_vendor.unref(address).link_phone)
      } : {}, {
        D: common_vendor.p({
          type: "right",
          color: "#999999"
        })
      }) : {}, {
        s: common_vendor.unref(take_type) == 1
      }) : orderInfo.value.sn ? common_vendor.e({
        F: common_vendor.t(orderInfo.value.take_type_txt),
        G: common_vendor.unref(station) && common_vendor.unref(station).gid
      }, common_vendor.unref(station) && common_vendor.unref(station).gid ? {
        H: common_vendor.t(common_vendor.unref(station).title),
        I: common_vendor.t("\n"),
        J: common_vendor.t(common_vendor.unref(station).province ? common_vendor.unref(station).province + ", " : ""),
        K: common_vendor.t(common_vendor.unref(station).city ? _ctx.item.city + ", " : ""),
        L: common_vendor.t(common_vendor.unref(station).district ? common_vendor.unref(station).district + ", " : ""),
        M: common_vendor.t(common_vendor.unref(station).street ? common_vendor.unref(station).street + ", " : ""),
        N: common_vendor.t(common_vendor.unref(station).detail),
        O: common_vendor.t("\n"),
        P: common_vendor.t(common_vendor.unref(station).link_name),
        Q: common_vendor.t(common_vendor.unref(station).link_phone)
      } : common_vendor.unref(address) && common_vendor.unref(address).gid ? {
        S: common_vendor.t(common_vendor.unref(address).province ? common_vendor.unref(address).province + ", " : ""),
        T: common_vendor.t(common_vendor.unref(address).city ? common_vendor.unref(address).city + ", " : ""),
        U: common_vendor.t(common_vendor.unref(address).district ? common_vendor.unref(address).district + ", " : ""),
        V: common_vendor.t(common_vendor.unref(address).street ? common_vendor.unref(address).street + ", " : ""),
        W: common_vendor.t(common_vendor.unref(address).detail),
        X: common_vendor.t("\n"),
        Y: common_vendor.t(common_vendor.unref(address).link_name),
        Z: common_vendor.t(common_vendor.unref(address).link_phone)
      } : {}, {
        R: common_vendor.unref(address) && common_vendor.unref(address).gid
      }) : {}, {
        E: orderInfo.value.sn,
        aa: orderInfo.value.sn
      }, orderInfo.value.sn ? {
        ab: common_vendor.t(orderInfo.value.sn),
        ac: common_vendor.o(copySn)
      } : {}, {
        ad: common_vendor.f(orderInfo.value.subs, (item, index, i0) => {
          return {
            a: item.gid,
            b: "6b23c96c-3-" + i0,
            c: common_vendor.p({
              item
            })
          };
        }),
        ae: common_vendor.p({
          title: "商品金额",
          value: "￥" + (orderInfo.value.goods_amount || "0.00")
        }),
        af: common_vendor.p({
          title: "优惠金额",
          value: "-￥" + (orderInfo.value.discount_amount || "0.00"),
          color: "#da4754",
          bold: true
        }),
        ag: common_vendor.p({
          title: "商品实付金额",
          value: "￥" + (orderInfo.value.real_amount || "0.00"),
          bold: true
        }),
        ah: common_vendor.p({
          title: "服务费",
          title2: freeDeliverTxt.value,
          title2color: "#da4754",
          value: "￥" + (orderInfo.value.deliver_amount || "0.00")
        }),
        ai: orderInfo.value.status == 0
      }, orderInfo.value.status == 0 ? {
        aj: common_vendor.o(_ctx.showPayTypePopup),
        ak: common_vendor.p({
          title: "支付方式",
          value: common_vendor.unref(payTypes)[common_vendor.unref(pay_type)],
          type: "pay_type",
          color: "#999"
        })
      } : {
        al: common_vendor.p({
          title: "支付方式",
          value: common_vendor.unref(payTypes)[common_vendor.unref(pay_type)],
          type: "pay_type",
          color: "#999",
          showRightIcon: false
        })
      }, {
        am: from.value
      }, from.value ? {
        an: common_vendor.o(_ctx.showCouponPopup),
        ao: common_vendor.p({
          title: "优惠券",
          value: coupon.value.title || "选择优惠券",
          type: "coupon",
          color: "#999"
        })
      } : orderInfo.value.sn ? {
        aq: common_vendor.p({
          title: "优惠券",
          value: coupon.value && coupon.value.title || "未使用",
          color: "#999"
        })
      } : {}, {
        ap: orderInfo.value.sn,
        ar: common_vendor.unref(remark)
      }, common_vendor.unref(remark) ? {
        as: common_vendor.t(common_vendor.unref(remark))
      } : {}, {
        at: orderInfo.value.deliver_remark
      }, orderInfo.value.deliver_remark ? {
        av: common_vendor.t(orderInfo.value.deliver_remark)
      } : {}, {
        aw: orderInfo.value.deliver_images
      }, orderInfo.value.deliver_images ? {
        ax: common_vendor.f(orderInfo.value.deliver_images, (item, index, i0) => {
          return {
            a: common_vendor.o(($event) => showImage(index), index),
            b: item,
            c: index
          };
        })
      } : {}, {
        ay: orderInfo.value.sn && orderInfo.value.take_type == 1 && orderInfo.value.status == 1 && orderInfo.value.deliver_status == 1
      }, orderInfo.value.sn && orderInfo.value.take_type == 1 && orderInfo.value.status == 1 && orderInfo.value.deliver_status == 1 ? {
        az: common_vendor.o(deliver),
        aA: common_vendor.p({
          money: orderInfo.value.pay_amount || "",
          number: orderInfo.value.goods_total,
          action: "deliver"
        })
      } : {
        aB: common_vendor.o(back),
        aC: common_vendor.p({
          money: orderInfo.value.pay_amount || "",
          number: orderInfo.value.goods_total,
          action: "navto"
        })
      }, {
        aD: common_vendor.sr(deliverImagesRef, "6b23c96c-14", {
          "k": "deliverImagesRef"
        }),
        aE: common_vendor.o(deliverSuccess),
        aF: common_vendor.p({
          sn: orderInfo.value.sn,
          images: images.value
        })
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-6b23c96c"]]);
wx.createPage(MiniProgramPage);
