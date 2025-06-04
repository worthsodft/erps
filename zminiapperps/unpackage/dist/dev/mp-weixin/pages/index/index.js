"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  data() {
    return {
      swiperList: [],
      noticeList: [],
      couponList: [],
      hotGoodsList: [],
      hotGiftCardList: [],
      aboutContent: ""
    };
  },
  onLoad(opts) {
    common_vendor.index.$tool.checkUpdateApp();
  },
  onShow() {
    common_vendor.index.$api.getCartTotal();
    this.getInitDataIndex();
  },
  methods: {
    getInitDataIndex() {
      common_vendor.index.$http.get("v1/getInitDataIndex").then((res) => {
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        this.swiperList = res.data.swiperList;
        this.noticeList = res.data.noticeList;
        this.couponList = res.data.couponList;
        this.hotGoodsList = res.data.hotGoodsList;
        this.hotGiftCardList = res.data.hotGiftCardList;
        this.aboutContent = res.data.aboutContent;
      });
    }
  },
  onPullDownRefresh() {
    this.getInitDataIndex();
    setTimeout(() => {
      common_vendor.index.stopPullDownRefresh();
    }, 200);
  },
  onShareAppMessage() {
    return getApp().globalData.shareData;
  }
};
if (!Array) {
  const _easycom_v_swiper2 = common_vendor.resolveComponent("v-swiper");
  const _easycom_v_notice2 = common_vendor.resolveComponent("v-notice");
  const _easycom_v_coupon_h2 = common_vendor.resolveComponent("v-coupon-h");
  const _easycom_v_hot22 = common_vendor.resolveComponent("v-hot2");
  const _easycom_v_about2 = common_vendor.resolveComponent("v-about");
  (_easycom_v_swiper2 + _easycom_v_notice2 + _easycom_v_coupon_h2 + _easycom_v_hot22 + _easycom_v_about2)();
}
const _easycom_v_swiper = () => "../../components/v-swiper/v-swiper.js";
const _easycom_v_notice = () => "../../components/v-notice/v-notice.js";
const _easycom_v_coupon_h = () => "../../components/v-coupon-h/v-coupon-h.js";
const _easycom_v_hot2 = () => "../../components/v-hot2/v-hot2.js";
const _easycom_v_about = () => "../../components/v-about/v-about.js";
if (!Math) {
  (_easycom_v_swiper + _easycom_v_notice + _easycom_v_coupon_h + _easycom_v_hot2 + _easycom_v_about)();
}
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return common_vendor.e({
    a: $data.swiperList.length
  }, $data.swiperList.length ? {
    b: common_vendor.p({
      list: $data.swiperList
    })
  } : {}, {
    c: $data.noticeList.length
  }, $data.noticeList.length ? {
    d: common_vendor.p({
      list: $data.noticeList
    })
  } : {}, {
    e: $data.couponList.length
  }, $data.couponList.length ? {
    f: common_vendor.p({
      list: $data.couponList
    })
  } : {}, {
    g: $data.hotGoodsList.length
  }, $data.hotGoodsList.length ? {
    h: common_vendor.p({
      list: $data.hotGoodsList
    })
  } : {}, {
    i: $data.hotGiftCardList.length
  }, $data.hotGiftCardList.length ? {
    j: common_vendor.p({
      title: "热销实物卡",
      path: "/pages/goods/gift_card_list",
      list: $data.hotGiftCardList
    })
  } : {}, {
    k: $data.aboutContent
  }, $data.aboutContent ? {
    l: common_vendor.p({
      content: $data.aboutContent
    })
  } : {});
}
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["render", _sfc_render]]);
_sfc_main.__runtimeHooks = 2;
wx.createPage(MiniProgramPage);
