"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_line_title2 = common_vendor.resolveComponent("v-line-title");
  _easycom_v_line_title2();
}
const _easycom_v_line_title = () => "../../components/v-line-title/v-line-title.js";
if (!Math) {
  _easycom_v_line_title();
}
const _sfc_main = {
  __name: "recharge",
  setup(__props) {
    const list = common_vendor.ref([]);
    const remarkList = common_vendor.ref([]);
    const money = common_vendor.ref(0);
    const current = common_vendor.ref(0);
    getList();
    getUserInfo();
    function getList() {
      common_vendor.index.$http.get("v1/rechargeWayList").then((res) => {
        list.value = res.data.list;
        remarkList.value = res.data.remarkList;
      });
    }
    function getUserInfo() {
      common_vendor.index.$api.getUserInfo((res) => {
        money.value = res.data.userInfo.money;
        res.data.userInfo.money_expire_at;
        common_vendor.index.$login.updateTokenCache(res.data);
      });
    }
    function show() {
      common_vendor.index.$tool.navto("/pages/pay/money-log");
    }
    function submit() {
      common_vendor.index.$login.judgePhone(() => {
        doSubmit();
      });
    }
    async function doSubmit() {
      let item = list.value[current.value];
      if (!item.gid)
        return common_vendor.index.$tool.tip("充值项目不存在");
      const res = await common_vendor.index.$api.getPayInfo("recharge", item.gid);
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.payment(res.data.payInfo, () => {
        getUserInfo();
      });
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.p({
          title: "当前余额："
        }),
        b: common_vendor.t(money.value),
        c: common_vendor.p({
          title: "充值金额"
        }),
        d: common_vendor.f(list.value, (item, index, i0) => {
          return {
            a: common_vendor.t(item.money),
            b: common_vendor.t(item.give_money),
            c: common_vendor.o(($event) => current.value = index, item.id),
            d: current.value == index ? 1 : "",
            e: item.id
          };
        }),
        e: remarkList.value.length > 0
      }, remarkList.value.length > 0 ? {
        f: common_vendor.p({
          title: "充值说明"
        }),
        g: common_vendor.f(remarkList.value, (item, idx, i0) => {
          return {
            a: common_vendor.t(idx + 1),
            b: common_vendor.t(item),
            c: idx
          };
        })
      } : {}, {
        h: common_vendor.o(submit),
        i: common_vendor.o(show)
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-b0187d83"]]);
wx.createPage(MiniProgramPage);
