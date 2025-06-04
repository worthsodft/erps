"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_user_service_item2 = common_vendor.resolveComponent("v-user-service-item");
  _easycom_v_user_service_item2();
}
const _easycom_v_user_service_item = () => "../v-user-service-item/v-user-service-item.js";
if (!Math) {
  _easycom_v_user_service_item();
}
const _sfc_main = {
  __name: "v-user-service",
  setup(__props) {
    const servicePhone = common_vendor.ref(getApp().globalData.servicePhone);
    const list = common_vendor.ref([]);
    let currItem = null;
    getServiceMenu();
    function getServiceMenu() {
      common_vendor.index.$http.get("v1/getServiceMenu").then((res) => {
        list.value = res.data.list;
      });
    }
    function doService(item) {
      currItem = item;
      let type = currItem.type;
      if (type == "path")
        path();
      else if (type == "phonecall")
        phonecall();
      else if (type == "set")
        ;
      else if (type == "logout")
        logout();
      else if (type == "order_finish")
        orderFinish();
      else if (type == "order_deliver")
        orderDeliver();
      else if (type == "update")
        common_vendor.index.$tool.checkUpdateApp(false);
      else
        path();
    }
    function orderFinish() {
      common_vendor.index.$login.judgePhone(async () => {
        const res = await common_vendor.index.$http.post("v1/isAuthFinishOrder");
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        let url = "/page_subs/manager/order_finish/order_finish_welcome";
        common_vendor.index.$tool.navto(url);
      });
    }
    async function orderDeliver() {
      common_vendor.index.$login.judgePhone(async () => {
        const res = await common_vendor.index.$http.post("v1/isAuthDeliverOrder");
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        let url = "/page_subs/manager/order_deliver/order_deliver_list";
        common_vendor.index.$tool.navto(url);
      });
    }
    function path() {
      if (!currItem.url)
        return common_vendor.index.$tool.tip("暂无跳转链接");
      common_vendor.index.$tool.navto(currItem.url, (err) => {
        common_vendor.index.$tool.tip("暂缓开放");
      });
    }
    function phonecall() {
      common_vendor.index.makePhoneCall({
        phoneNumber: servicePhone.value,
        fail(err) {
          console.log(err);
        }
      });
    }
    function logout() {
      common_vendor.index.$tool.confirm("确定要清除用户缓存吗？", () => {
        common_vendor.index.$login.logout();
        common_vendor.index.$tool.tip("已清除用户缓存", true, () => {
          common_vendor.index.$tool.index();
        });
      });
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(list.value, (item, index, i0) => {
          return {
            a: item.id,
            b: common_vendor.o(doService, item.id),
            c: "5490a0ca-0-" + i0,
            d: common_vendor.p({
              index,
              item
            })
          };
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-5490a0ca"]]);
wx.createComponent(Component);
