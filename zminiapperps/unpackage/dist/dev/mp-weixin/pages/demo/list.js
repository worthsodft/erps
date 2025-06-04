"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "list",
  setup(__props) {
    const openBusinessView = () => {
      common_vendor.index.openBusinessView({
        businessType: "weappOrderConfirm",
        extraData: {
          transaction_id: "4200002420202408234193346519"
        }
      });
    };
    const temp = () => {
      common_vendor.index.$http.get("v1/hello").then((res) => {
        console.log(res);
      });
    };
    const noAuthApi = common_vendor.index.$tool.throttle(async () => {
      const res = await common_vendor.index.$http.get("v1/getInitDataIndex");
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      console.log(res);
    });
    const authApi = common_vendor.index.$tool.debounce(() => {
      common_vendor.index.$login.judgeLogin(async () => {
        const res = await common_vendor.index.$http.get("v1/getUserInfo");
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        console.log(res);
      });
    });
    const logout = common_vendor.index.$tool.debounce(() => {
      common_vendor.index.$login.logout();
      common_vendor.index.$tool.tip("退出登录成功");
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o((...args) => common_vendor.unref(noAuthApi) && common_vendor.unref(noAuthApi)(...args)),
        b: common_vendor.o((...args) => common_vendor.unref(authApi) && common_vendor.unref(authApi)(...args)),
        c: common_vendor.o((...args) => common_vendor.unref(logout) && common_vendor.unref(logout)(...args)),
        d: common_vendor.o(temp),
        e: common_vendor.o(openBusinessView)
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-0c743fbf"]]);
wx.createPage(MiniProgramPage);
