"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_card2 = common_vendor.resolveComponent("v-card");
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  (_easycom_v_card2 + _easycom_uni_load_more2)();
}
const _easycom_v_card = () => "../../components/v-card/v-card.js";
const _easycom_uni_load_more = () => "../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
if (!Math) {
  (_easycom_v_card + _easycom_uni_load_more)();
}
const _sfc_main = {
  __name: "refund",
  setup(__props) {
    const list = common_vendor.ref();
    common_vendor.index.$login.judgePhone(() => {
      getList();
    });
    function getList() {
      common_vendor.index.$http.post("v1/getMyMoneyCardList").then((res) => {
        list.value = res.data.list;
      });
    }
    function refund(item) {
      common_vendor.index.$tool.confirm("确定要发起退款申请吗？", () => {
        common_vendor.index.$tool.showLoading("受理中...");
        common_vendor.index.$http.post("v1/moneyCardRefund/" + item.gid).then((res) => {
          common_vendor.index.$tool.hideLoading();
          if (res.code != 1)
            return common_vendor.index.$tool.tip(res.info || "操作失败");
          common_vendor.index.$tool.tip(res.info || "受理成功");
          getList();
        });
      });
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(list.value, (item, index, i0) => {
          return common_vendor.e({
            a: common_vendor.t(item.real_has),
            b: common_vendor.t(item.real_init),
            c: common_vendor.t(item.give_has),
            d: common_vendor.t(item.give_init),
            e: common_vendor.t(item.create_at),
            f: item.refund_at,
            g: common_vendor.t(item.refund_at),
            h: item.status == 0
          }, item.status == 0 ? {} : item.status == 1 && item.real_has > 0 ? {} : item.status == 1 && item.real_has <= 0 ? {} : {}, {
            i: item.status == 1 && item.real_has > 0,
            j: item.status == 1 && item.real_has <= 0,
            k: item.status == 1 && item.real_has > 0
          }, item.status == 1 && item.real_has > 0 ? {
            l: common_vendor.o(($event) => refund(item), item.gid)
          } : {}, {
            m: item.gid,
            n: "4eb569b4-0-" + i0
          });
        }),
        b: common_vendor.p({
          status: "nomore"
        })
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-4eb569b4"]]);
wx.createPage(MiniProgramPage);
