"use strict";
const common_vendor = require("../../../common/vendor.js");
if (!Array) {
  const _easycom_uni_section2 = common_vendor.resolveComponent("uni-section");
  const _easycom_uni_datetime_picker2 = common_vendor.resolveComponent("uni-datetime-picker");
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  (_easycom_uni_section2 + _easycom_uni_datetime_picker2 + _easycom_uni_load_more2)();
}
const _easycom_uni_section = () => "../../../uni_modules/uni-section/components/uni-section/uni-section.js";
const _easycom_uni_datetime_picker = () => "../../../uni_modules/uni-datetime-picker/components/uni-datetime-picker/uni-datetime-picker.js";
const _easycom_uni_load_more = () => "../../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
if (!Math) {
  (_easycom_uni_section + _easycom_uni_datetime_picker + vOrderItem2Show + _easycom_uni_load_more)();
}
const vOrderItem2Show = () => "../components/v-order-item2-show/v-order-item2-show.js";
const _sfc_main = {
  __name: "order_finish_list",
  setup(__props) {
    let today = common_vendor.index.$tool.today();
    const takeAtRange = common_vendor.ref([today, today]);
    const loadStatus = common_vendor.ref("more");
    const list = common_vendor.ref([]);
    let total = common_vendor.ref(0);
    let page = 1;
    getList();
    function takeAtRangeChange(e) {
      if (e.length == 0) {
        takeAtRange.value = [today, today];
      } else {
        takeAtRange.value = e;
      }
      reset();
      getList("reset");
    }
    function reset() {
      loadStatus.value = "more";
      page = 1;
      total.value = 0;
    }
    function getList(type) {
      if (loadStatus.value != "more")
        return;
      loadStatus.value = "loading";
      common_vendor.index.$http.post("v1/getMyFinishOrderPageData", { page, takeAtRange: takeAtRange.value }).then((res) => {
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        let pageData = res.data.pageData;
        total.value = pageData.total;
        if (pageData.total == 0) {
          loadStatus.value = "nomore";
          list.value = [];
          return;
        }
        if (pageData.data.length == 0) {
          loadStatus.value = "nomore";
          return;
        }
        if (type == "reset")
          list.value = [...pageData.data];
        else
          list.value = [...list.value, ...pageData.data];
        total.value = pageData.total;
        if (list.value.length >= total.value) {
          loadStatus.value = "nomore";
        } else {
          loadStatus.value = "more";
          page++;
        }
      });
    }
    common_vendor.onReachBottom(() => {
      getList();
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.p({
          title: "核销时间：",
          type: "line"
        }),
        b: common_vendor.o(takeAtRangeChange),
        c: common_vendor.o(($event) => takeAtRange.value = $event),
        d: common_vendor.p({
          type: "daterange",
          rangeSeparator: "至",
          modelValue: takeAtRange.value
        }),
        e: common_vendor.p({
          title: "总订单数：" + common_vendor.unref(total),
          type: "line"
        }),
        f: common_vendor.f(list.value, (vo, index, i0) => {
          return {
            a: "0e6e7389-3-" + i0,
            b: common_vendor.p({
              item: vo
            }),
            c: vo.id
          };
        }),
        g: common_vendor.o(getList),
        h: common_vendor.p({
          status: loadStatus.value
        })
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-0e6e7389"]]);
wx.createPage(MiniProgramPage);
