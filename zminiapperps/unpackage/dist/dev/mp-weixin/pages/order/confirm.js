"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_data_checkbox2 = common_vendor.resolveComponent("uni-data-checkbox");
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_v_order_sub_item22 = common_vendor.resolveComponent("v-order-sub-item2");
  const _easycom_v_order_info_item2 = common_vendor.resolveComponent("v-order-info-item");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  const _easycom_v_order_confirm_bar22 = common_vendor.resolveComponent("v-order-confirm-bar2");
  const _easycom_v_pay_type_select2 = common_vendor.resolveComponent("v-pay-type-select");
  const _easycom_v_coupon_select2 = common_vendor.resolveComponent("v-coupon-select");
  const _easycom_v_station_select2 = common_vendor.resolveComponent("v-station-select");
  const _easycom_v_address_select2 = common_vendor.resolveComponent("v-address-select");
  (_easycom_uni_data_checkbox2 + _easycom_uni_icons2 + _easycom_v_order_sub_item22 + _easycom_v_order_info_item2 + _easycom_uni_easyinput2 + _easycom_v_order_confirm_bar22 + _easycom_v_pay_type_select2 + _easycom_v_coupon_select2 + _easycom_v_station_select2 + _easycom_v_address_select2)();
}
const _easycom_uni_data_checkbox = () => "../../uni_modules/uni-data-checkbox/components/uni-data-checkbox/uni-data-checkbox.js";
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_v_order_sub_item2 = () => "../../components/v-order-sub-item2/v-order-sub-item2.js";
const _easycom_v_order_info_item = () => "../../components/v-order-info-item/v-order-info-item.js";
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
const _easycom_v_order_confirm_bar2 = () => "../../components/v-order-confirm-bar2/v-order-confirm-bar2.js";
const _easycom_v_pay_type_select = () => "../../components/v-pay-type-select/v-pay-type-select.js";
const _easycom_v_coupon_select = () => "../../components/v-coupon-select/v-coupon-select.js";
const _easycom_v_station_select = () => "../../components/v-station-select/v-station-select.js";
const _easycom_v_address_select = () => "../../components/v-address-select/v-address-select.js";
if (!Math) {
  (_easycom_uni_data_checkbox + _easycom_uni_icons + _easycom_v_order_sub_item2 + _easycom_v_order_info_item + _easycom_uni_easyinput + _easycom_v_order_confirm_bar2 + _easycom_v_pay_type_select + _easycom_v_coupon_select + _easycom_v_station_select + _easycom_v_address_select)();
}
const _sfc_main = {
  __name: "confirm",
  props: {
    "modelValue": {},
    "modelModifiers": {}
  },
  emits: ["update:modelValue"],
  setup(__props) {
    const $enum = common_vendor.ref(common_vendor.index.$enum);
    common_vendor.ref("去使用");
    const from = common_vendor.ref();
    let isCreated = false;
    const orderInfo = common_vendor.ref({});
    const freeDeliverTxt = common_vendor.ref("");
    let takeTypes = common_vendor.ref([]);
    let preParams = {};
    let take_type = -1;
    let remark = common_vendor.useModel(__props, "modelValue");
    const couponSelectRef = common_vendor.ref();
    const couponList = common_vendor.ref([]);
    const coupon = common_vendor.ref({});
    const addressSelectRef = common_vendor.ref();
    const addressList = common_vendor.ref([]);
    let address = common_vendor.ref({});
    const stationSelectRef = common_vendor.ref();
    const stationList = common_vendor.ref([]);
    let station = common_vendor.ref({});
    let payTypes = common_vendor.ref({});
    let pay_type = common_vendor.ref("");
    const payTypeSelect = common_vendor.ref();
    let sn = "";
    common_vendor.onLoad((opts) => {
      getInitDataConfirm();
      if (opts.sn) {
        sn = opts.sn;
        doDetailInfo();
      } else {
        doConfirmInfo(opts);
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
    function doConfirmInfo(opts) {
      from.value = opts.from || "";
      preParams = {
        from: opts.from || "",
        goods_sn: opts.goods_sn || "",
        goods_number: opts.goods_number || "",
        address_gid: opts.agid || "",
        station_gid: opts.sgid || "",
        take_type: -1,
        pay_type: "",
        gift_card_sns: []
      };
      if (preParams.from == "goods") {
        if (!preParams.goods_sn || !preParams.goods_number)
          return common_vendor.index.$tool.tip("请输入商品编号和数量");
      } else if (preParams.from != "cart")
        return common_vendor.index.$tool.tip("路径参数错误，购物车不存在");
      common_vendor.index.$login.judgeLogin(() => {
        getOrderConfirmInfo();
      });
    }
    function getOrderConfirmInfo() {
      common_vendor.index.$tool.showLoading();
      common_vendor.index.$http.post("v1/getOrderConfirmInfoV2", preParams).then((res) => {
        common_vendor.index.$tool.hideLoading();
        isCreated = false;
        orderInfo.value = res.data.orderInfo;
        freeDeliverTxt.value = res.data.freeDeliverTxt;
        if (res.data.address) {
          address.value = res.data.address;
          preParams.address_gid = address.value.gid;
        }
        if (res.data.station) {
          station.value = res.data.station;
          preParams.station_gid = station.value.gid;
        }
        couponList.value = res.data.couponList;
        if (res.data.coupon) {
          coupon.value = res.data.coupon;
          preParams.coupon_gid = coupon.value.gid;
        } else {
          coupon.value = {};
        }
        payTypes.value = res.data.payTypes;
      }).catch((err) => {
        common_vendor.index.$tool.alert(err.info, () => {
          if (preParams.from == "cart") {
            common_vendor.index.$tool.navto("/pages/cart/cart");
          } else {
            common_vendor.index.$tool.back();
          }
        });
      });
    }
    function getInitDataConfirm() {
      common_vendor.index.$http.post("v1/getInitDataConfirm", preParams).then((res) => {
        takeTypes.value = res.data.takeTypes;
      });
    }
    function takeTypeChange({ detail }) {
      take_type = detail.value;
      preParams.take_type = take_type;
      if (take_type == $enum.value.order.TAKE_TYPE_SELF)
        preParams.address_gid = "";
      else if (take_type == $enum.value.order.TAKE_TYPE_DELIVER)
        preParams.station_gid = "";
      reload();
    }
    function reload() {
      common_vendor.index.$login.judgeLogin(() => {
        getOrderConfirmInfo();
      });
    }
    function showAddressPopup() {
      if (addressList.value.length == 0)
        getAddressList(() => addressSelectRef.value.open());
      else
        addressSelectRef.value.open();
    }
    async function getAddressList(cb) {
      const res = await common_vendor.index.$http.get("v1/getAddressList");
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      addressList.value = res.data.list;
      cb && cb();
    }
    function selectAddress(item) {
      address.value = item;
      preParams.address_gid = item.gid;
      addressSelectRef.value.close();
    }
    function showStationPopup() {
      if (stationList.value.length == 0)
        getStationList(() => stationSelectRef.value.open());
      else
        stationSelectRef.value.open();
    }
    async function getStationList(cb) {
      let params = {
        lat: 39.136465,
        lng: 117.209932
      };
      const res = await common_vendor.index.$http.get("v1/getStationListWithDistance", params);
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      stationList.value = res.data.list;
      cb && cb();
    }
    function selectStation(item) {
      station.value = item;
      preParams.station_gid = item.gid;
      stationSelectRef.value.close();
    }
    function showCouponPopup() {
      if (couponList.value.length == 0)
        getCouponList(() => couponSelectRef.value.open());
      else
        couponSelectRef.value.open();
    }
    async function getCouponList(cb) {
      cb && cb();
    }
    function selectCoupon(e) {
      couponSelectRef.value.close();
      preParams.coupon_gid = e.gid;
      reload();
    }
    function resetCoupon() {
      coupon.value = {};
      couponSelectRef.value.close();
      preParams.coupon_gid = "";
      reload();
    }
    async function showPayTypePopup() {
      payTypeSelect.value.open();
    }
    function selectPayType(e) {
      if (e.payType == "giftcard") {
        if (e.cardHasMoney < orderInfo.value.pay_amount)
          return common_vendor.index.$tool.tip("所选实物卡余额不足");
        preParams.gift_card_sns = e.selectedSn;
      } else {
        preParams.gift_card_sns = [];
      }
      pay_type.value = e.payType;
      payTypeSelect.value.close();
      preParams.pay_type = pay_type.value;
    }
    const payOrder = common_vendor.index.$tool.debounce(() => {
      doPayOrder(orderInfo.value.sn, pay_type.value);
    });
    const createOrder = common_vendor.index.$tool.debounce(() => {
      if (orderInfo.value.subs.length == 0 || orderInfo.value.goods_total == 0)
        return common_vendor.index.$tool.tip("订单中没有商品");
      if (take_type == -1)
        return common_vendor.index.$tool.tip("请选择收货方式");
      if (take_type == $enum.value.order.TAKE_TYPE_SELF && !preParams.station_gid)
        return common_vendor.index.$tool.tip("请选择水站");
      if (take_type == $enum.value.order.TAKE_TYPE_DELIVER && !preParams.address_gid)
        return common_vendor.index.$tool.tip("请选择收货地址");
      if (pay_type.value == "")
        return common_vendor.index.$tool.tip("请选择有效的支付方式");
      if (pay_type.value == "giftcard" && preParams.gift_card_sns.length == 0)
        return common_vendor.index.$tool.tip("请选择有效的实物卡", true, null, 2);
      if (isCreated)
        return common_vendor.index.$tool.tip("订单已创建，请到我的订单列表继续支付");
      preParams.remark = remark.value;
      common_vendor.index.$http.post("v1/createOrderFromConfirmV2", preParams).then((res) => {
        if (!res.data.sn)
          return common_vendor.index.$tool.tip("订单创建失败");
        isCreated = true;
        doPayOrder(res.data.sn, pay_type.value, true);
      });
    });
    async function doPayOrder(sn2, pay_type2, isFirst = false) {
      if (orderInfo.value.pay_amount < 0)
        return common_vendor.index.$tool.tip("支付金额不能小于0");
      else if (orderInfo.value.pay_amount == 0)
        pay_type2 = "yue";
      switch (pay_type2) {
        case "yue":
          common_vendor.index.$http.post(`v1/payOrder/${sn2}`, { pay_type: pay_type2 }).then((res2) => {
            common_vendor.index.$tool.navto("/pages/pay/success?type=order&sn=" + sn2);
          });
          break;
        case "giftcard":
          let gift_card_sns = preParams.gift_card_sns;
          common_vendor.index.$http.post(`v1/payOrder/${sn2}`, { pay_type: pay_type2, gift_card_sns }).then((res2) => {
            common_vendor.index.$tool.navto("/pages/pay/success?type=order&sn=" + sn2);
          });
          break;
        case "weixin":
          const res = await common_vendor.index.$api.getPayInfo("order", sn2);
          if (res.code != 1)
            return common_vendor.index.$tool.tip(res.info || "系统错误");
          common_vendor.index.$tool.payment(res.data.payInfo, () => {
            common_vendor.index.$tool.navto("/pages/pay/success?type=order&sn=" + sn2);
          }, () => {
            if (isFirst) {
              common_vendor.index.$tool.tip("订单已创建", true, () => {
                common_vendor.index.$tool.redirect("/pages/order/list");
              });
            }
          });
          break;
        default:
          return common_vendor.index.$tool.tip("请选择有效的支付方式");
      }
    }
    function showImage(index) {
      common_vendor.index.previewImage({
        current: index,
        urls: orderInfo.value.deliver_images
      });
    }
    function back() {
      common_vendor.index.$tool.back();
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: from.value
      }, from.value ? common_vendor.e({
        b: common_vendor.o(takeTypeChange),
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
        j: common_vendor.t(common_vendor.unref(station).city ? common_vendor.unref(station).city + ", " : ""),
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
        r: common_vendor.o(showStationPopup)
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
        }),
        E: common_vendor.o(showAddressPopup)
      }) : {}, {
        s: common_vendor.unref(take_type) == 1
      }) : orderInfo.value.sn ? common_vendor.e({
        G: common_vendor.t(orderInfo.value.take_type_txt),
        H: common_vendor.unref(station) && common_vendor.unref(station).gid
      }, common_vendor.unref(station) && common_vendor.unref(station).gid ? {
        I: common_vendor.t(common_vendor.unref(station).title),
        J: common_vendor.t("\n"),
        K: common_vendor.t(common_vendor.unref(station).province ? common_vendor.unref(station).province + ", " : ""),
        L: common_vendor.t(common_vendor.unref(station).city ? common_vendor.unref(station).city + ", " : ""),
        M: common_vendor.t(common_vendor.unref(station).district ? common_vendor.unref(station).district + ", " : ""),
        N: common_vendor.t(common_vendor.unref(station).street ? common_vendor.unref(station).street + ", " : ""),
        O: common_vendor.t(common_vendor.unref(station).detail),
        P: common_vendor.t("\n"),
        Q: common_vendor.t(common_vendor.unref(station).link_name),
        R: common_vendor.t(common_vendor.unref(station).link_phone)
      } : common_vendor.unref(address) && common_vendor.unref(address).gid ? {
        T: common_vendor.t(common_vendor.unref(address).province ? common_vendor.unref(address).province + ", " : ""),
        U: common_vendor.t(common_vendor.unref(address).city ? common_vendor.unref(address).city + ", " : ""),
        V: common_vendor.t(common_vendor.unref(address).district ? common_vendor.unref(address).district + ", " : ""),
        W: common_vendor.t(common_vendor.unref(address).street ? common_vendor.unref(address).street + ", " : ""),
        X: common_vendor.t(common_vendor.unref(address).detail),
        Y: common_vendor.t("\n"),
        Z: common_vendor.t(common_vendor.unref(address).link_name),
        aa: common_vendor.t(common_vendor.unref(address).link_phone)
      } : {}, {
        S: common_vendor.unref(address) && common_vendor.unref(address).gid
      }) : {}, {
        F: orderInfo.value.sn,
        ab: orderInfo.value.sn
      }, orderInfo.value.sn ? {
        ac: common_vendor.t(orderInfo.value.sn),
        ad: common_vendor.o(copySn)
      } : {}, {
        ae: common_vendor.f(orderInfo.value.subs, (item, index, i0) => {
          return {
            a: item.gid,
            b: "324e7894-3-" + i0,
            c: common_vendor.p({
              item
            })
          };
        }),
        af: common_vendor.p({
          title: "商品金额",
          value: "￥" + (orderInfo.value.goods_amount || "0.00")
        }),
        ag: common_vendor.p({
          title: "优惠金额",
          value: "-￥" + (orderInfo.value.discount_amount || "0.00"),
          color: "#da4754",
          bold: true
        }),
        ah: common_vendor.p({
          title: "商品实付金额",
          value: "￥" + (orderInfo.value.real_amount || "0.00"),
          bold: true
        }),
        ai: common_vendor.p({
          title: "服务费",
          title2: freeDeliverTxt.value,
          title2color: "#da4754",
          value: "￥" + (orderInfo.value.deliver_amount || "0.00")
        }),
        aj: orderInfo.value.status == 0
      }, orderInfo.value.status == 0 ? {
        ak: common_vendor.o(showPayTypePopup),
        al: common_vendor.p({
          title: "支付方式",
          value: common_vendor.unref(payTypes)[common_vendor.unref(pay_type)],
          type: "pay_type",
          color: "#999"
        })
      } : {
        am: common_vendor.p({
          title: "支付方式",
          value: common_vendor.unref(payTypes)[common_vendor.unref(pay_type)],
          type: "pay_type",
          color: "#999",
          showRightIcon: false
        })
      }, {
        an: from.value
      }, from.value ? {
        ao: common_vendor.o(showCouponPopup),
        ap: common_vendor.p({
          title: "优惠券",
          value: coupon.value.title || "选择优惠券",
          type: "coupon",
          color: "#999"
        })
      } : orderInfo.value.sn ? {
        ar: common_vendor.p({
          title: "优惠券",
          value: coupon.value && coupon.value.title || "未使用",
          color: "#999"
        })
      } : {}, {
        aq: orderInfo.value.sn,
        as: from.value
      }, from.value ? {
        at: common_vendor.o(($event) => common_vendor.isRef(remark) ? remark.value = $event : remark = $event),
        av: common_vendor.p({
          type: "textarea",
          trim: true,
          maxlength: "100",
          placeholder: "请输入您的备注",
          autoHeight: true,
          modelValue: common_vendor.unref(remark)
        })
      } : orderInfo.value.sn ? {
        ax: common_vendor.t(common_vendor.unref(remark))
      } : {}, {
        aw: orderInfo.value.sn,
        ay: orderInfo.value.deliver_remark
      }, orderInfo.value.deliver_remark ? {
        az: common_vendor.t(orderInfo.value.deliver_remark)
      } : {}, {
        aA: orderInfo.value.deliver_images
      }, orderInfo.value.deliver_images ? {
        aB: common_vendor.f(orderInfo.value.deliver_images, (item, index, i0) => {
          return {
            a: common_vendor.o(($event) => showImage(index), index),
            b: item,
            c: index
          };
        })
      } : {}, {
        aC: from.value
      }, from.value ? {
        aD: common_vendor.o(common_vendor.unref(createOrder)),
        aE: common_vendor.p({
          money: orderInfo.value.pay_amount || "",
          number: orderInfo.value.goods_total,
          action: "create"
        })
      } : orderInfo.value.sn && orderInfo.value.status == 0 ? {
        aG: common_vendor.o(common_vendor.unref(payOrder)),
        aH: common_vendor.p({
          money: orderInfo.value.pay_amount || "",
          number: orderInfo.value.goods_total,
          action: "pay"
        })
      } : orderInfo.value.sn && orderInfo.value.status > 0 ? {
        aJ: common_vendor.o(back),
        aK: common_vendor.p({
          money: orderInfo.value.pay_amount || "",
          number: orderInfo.value.goods_total,
          action: "navto"
        })
      } : {}, {
        aF: orderInfo.value.sn && orderInfo.value.status == 0,
        aI: orderInfo.value.sn && orderInfo.value.status > 0,
        aL: common_vendor.sr(payTypeSelect, "324e7894-16", {
          "k": "payTypeSelect"
        }),
        aM: common_vendor.o(selectPayType),
        aN: common_vendor.p({
          list: common_vendor.unref(payTypes)
        }),
        aO: common_vendor.sr(couponSelectRef, "324e7894-17", {
          "k": "couponSelectRef"
        }),
        aP: common_vendor.o(selectCoupon),
        aQ: common_vendor.o(resetCoupon),
        aR: common_vendor.p({
          list: couponList.value
        }),
        aS: common_vendor.sr(stationSelectRef, "324e7894-18", {
          "k": "stationSelectRef"
        }),
        aT: common_vendor.o(selectStation),
        aU: common_vendor.p({
          list: stationList.value
        }),
        aV: common_vendor.sr(addressSelectRef, "324e7894-19", {
          "k": "addressSelectRef"
        }),
        aW: common_vendor.o(selectAddress),
        aX: common_vendor.o(getAddressList),
        aY: common_vendor.p({
          list: addressList.value
        })
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-324e7894"]]);
wx.createPage(MiniProgramPage);
