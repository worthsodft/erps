"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Math) {
  posterSwiper();
}
const posterSwiper = () => "./components/poster-swiper.js";
const _sfc_main = {
  __name: "my_poster",
  setup(__props) {
    const list = common_vendor.ref([]);
    const currIndex = common_vendor.ref(0);
    const getMyPosterList = (res) => {
      common_vendor.index.$tool.showLoading("海报生成中...");
      common_vendor.index.$http.addon.get("userspread/v1/getMyPosterList").then((res2) => {
        common_vendor.index.$tool.hideLoading();
        list.value = res2.data.list;
      });
    };
    getMyPosterList();
    const change = (index) => {
      currIndex.value = index;
    };
    const save = () => {
    };
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.o(change),
        b: common_vendor.p({
          imgList: list.value
        }),
        c: !list.value.length
      }, !list.value.length ? {} : {}, {
        d: common_vendor.o(save)
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-6c09a971"]]);
wx.createPage(MiniProgramPage);
