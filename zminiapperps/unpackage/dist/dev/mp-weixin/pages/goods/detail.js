"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uv_parse2 = common_vendor.resolveComponent("uv-parse");
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  (_easycom_uv_parse2 + _easycom_uni_icons2)();
}
const _easycom_uv_parse = () => "../../uni_modules/uv-parse/components/uv-parse/uv-parse.js";
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  (_easycom_uv_parse + _easycom_uni_icons)();
}
const _sfc_main = {
  __name: "detail",
  props: {
    "modelValue": {},
    "modelModifiers": {}
  },
  emits: ["update:modelValue"],
  setup(__props) {
    const cartTrans = common_vendor.ref("");
    const item = common_vendor.ref({});
    const number = common_vendor.useModel(__props, "modelValue");
    const amount = common_vendor.ref(0);
    const cartTotal = common_vendor.ref(0);
    common_vendor.onLoad((opts) => {
      let sn = opts.sn || "";
      if (!sn)
        return common_vendor.index.$tool.tip("商品编号不存在");
      getDetail(sn);
      getCartTotal();
    });
    function getCartTotal() {
      common_vendor.index.$api.getCartTotal((res) => {
        cartTotal.value = res.data.total;
      });
    }
    function cartIconAnimate() {
      cartTrans.value = "scale-down";
      setTimeout(() => {
        cartTrans.value = "scale-up";
      }, 100);
    }
    function getDetail(sn) {
      common_vendor.index.$http.get("v1/goodsDetail/" + sn).then((res) => {
        item.value = res.data.goods;
        number.value = 1;
        amount.value = number.value * item.value.self_price;
      });
    }
    function toCart() {
      common_vendor.index.$tool.navto(`/pages/cart/cart`);
    }
    function add2cart() {
      cartIconAnimate();
      let params = {
        sn: item.value.sn,
        number: number.value
      };
      common_vendor.index.$login.judgeLogin(() => {
        common_vendor.index.$api.add2cart(params, (res) => {
          cartTotal.value = res.data.total;
        });
      });
    }
    common_vendor.onShareAppMessage(() => {
      let shareData = getApp().globalData.shareData;
      console.log(shareData);
      return shareData;
    });
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.f(item.value.slider, (pic, index, i0) => {
          return {
            a: pic || "",
            b: index
          };
        }),
        b: common_vendor.t(item.value.self_price),
        c: common_vendor.t(item.value.unit),
        d: item.value.market_price > 0
      }, item.value.market_price > 0 ? {
        e: common_vendor.t(item.value.market_price)
      } : {}, {
        f: common_vendor.t(item.value.name || ""),
        g: common_vendor.t(item.value.min_buy_number),
        h: common_vendor.t(item.value.unit),
        i: item.value.deliver_fee > 0
      }, item.value.deliver_fee > 0 ? {
        j: common_vendor.t(parseInt(item.value.deliver_fee)),
        k: common_vendor.t(item.value.unit)
      } : {}, {
        l: common_vendor.p({
          content: item.value.desc
        }),
        m: common_vendor.p({
          type: "cart",
          size: "30",
          color: "#000"
        }),
        n: cartTotal.value > 0
      }, cartTotal.value > 0 ? {
        o: common_vendor.t(cartTotal.value)
      } : {}, {
        p: common_vendor.n(cartTrans.value),
        q: common_vendor.o(toCart),
        r: common_vendor.o(add2cart),
        s: common_vendor.o(toCart)
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-adbe0a1d"]]);
_sfc_main.__runtimeHooks = 2;
wx.createPage(MiniProgramPage);
