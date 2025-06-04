"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_address_edit2 = common_vendor.resolveComponent("v-address-edit");
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  (_easycom_v_address_edit2 + _easycom_uni_load_more2 + _easycom_v_button_w802)();
}
const _easycom_v_address_edit = () => "../../components/v-address-edit/v-address-edit.js";
const _easycom_uni_load_more = () => "../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
const _easycom_v_button_w80 = () => "../../components/v-button-w80/v-button-w80.js";
if (!Math) {
  (_easycom_v_address_edit + _easycom_uni_load_more + _easycom_v_button_w80)();
}
const _sfc_main = {
  __name: "address",
  setup(__props) {
    const list = common_vendor.ref([]);
    common_vendor.ref("more");
    const source = common_vendor.ref("");
    common_vendor.onLoad((opts) => {
      source.value = opts.source || "";
      getList();
    });
    async function getList() {
      const res = await common_vendor.index.$http.get("v1/getAddressList");
      list.value = res.data.list;
    }
    function back() {
      common_vendor.index.$tool.navto("/pages/my/my");
    }
    function add() {
      common_vendor.index.$tool.navto("/pages/my/address-edit");
    }
    function delItem(index) {
      common_vendor.index.$tool.confirm("确认要删除此地址吗？", async () => {
        let gid = list.value[index].gid;
        const res = await common_vendor.index.$http.post("v1/delAddress/" + gid);
        if (res.code == 1)
          list.value.splice(index, 1);
      });
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.f(list.value, (item, index, i0) => {
          return {
            a: item.id,
            b: common_vendor.o(delItem, item.id),
            c: "ea533e64-0-" + i0,
            d: common_vendor.p({
              item,
              index,
              source: source.value
            })
          };
        }),
        b: list.value.length < 1
      }, list.value.length < 1 ? {
        c: common_vendor.p({
          status: "nomore"
        })
      } : {}, {
        d: common_vendor.o(add),
        e: common_vendor.p({
          title: "添加地址"
        }),
        f: common_vendor.o(back),
        g: common_vendor.p({
          type: "normal",
          title: "返回我的"
        })
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-ea533e64"]]);
wx.createPage(MiniProgramPage);
