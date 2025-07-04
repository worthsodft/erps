"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  _easycom_uni_load_more2();
}
const _easycom_uni_load_more = () => "../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
if (!Math) {
  (vGiftCardItem + _easycom_uni_load_more)();
}
const vGiftCardItem = () => "./components/v-gift-card-item/v-gift-card-item.js";
const _sfc_main = {
  __name: "list",
  setup(__props) {
    const list = common_vendor.ref([]);
    const loadStatus = common_vendor.ref("more");
    let page = 1;
    let total = 0;
    getList();
    async function getList() {
      if (loadStatus.value != "more")
        return;
      loadStatus.value = "loading";
      const res = await common_vendor.index.$http.get("v1/getMyGiftCardPageData?page=" + page);
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      let pageData = res.data.pageData;
      if (pageData.data.length == 0) {
        loadStatus.value = "nomore";
        return;
      }
      list.value = [...list.value, ...pageData.data];
      total = pageData.total;
      if (list.value.length >= total) {
        loadStatus.value = "nomore";
      } else {
        loadStatus.value = "more";
        page++;
      }
    }
    common_vendor.onReachBottom(() => {
      getList();
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(list.value, (item, index, i0) => {
          return {
            a: item.id,
            b: "eae06b1e-0-" + i0,
            c: common_vendor.p({
              item
            })
          };
        }),
        b: common_vendor.o(getList),
        c: common_vendor.p({
          status: loadStatus.value
        })
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-eae06b1e"]]);
wx.createPage(MiniProgramPage);
