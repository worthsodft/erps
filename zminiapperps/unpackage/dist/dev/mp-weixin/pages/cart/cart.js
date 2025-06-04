"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_cart_item22 = common_vendor.resolveComponent("v-cart-item2");
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  (_easycom_v_cart_item22 + _easycom_uni_load_more2 + _easycom_uni_icons2)();
}
const _easycom_v_cart_item2 = () => "../../components/v-cart-item2/v-cart-item2.js";
const _easycom_uni_load_more = () => "../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
if (!Math) {
  (_easycom_v_cart_item2 + _easycom_uni_load_more + _easycom_uni_icons)();
}
const _sfc_main = {
  __name: "cart",
  setup(__props) {
    const cart = common_vendor.ref({});
    const tabBarHeight = common_vendor.ref(0);
    common_vendor.index.$login.judgeLogin(() => {
      common_vendor.index.$api.getCartTotal();
    });
    common_vendor.onShow(() => {
      getInitDataCart();
    });
    const check = (item) => {
      doCheck(item);
    };
    const checkAll = () => {
      let item = {
        index: -1,
        is_checked: !cart.value.is_checked
      };
      doCheck(item);
    };
    const doCheck = (e) => {
      let params = {
        sn: e.index == -1 ? -1 : cart.value.list[e.index].sn,
        is_checked: e.is_checked
      };
      common_vendor.index.$api.check2cart(params, (res) => {
        cart.value = res.data.cart;
      });
    };
    function getInitDataCart() {
      common_vendor.index.$http.get("v1/getInitDataCart").then((res) => {
        cart.value = res.data.cart;
        common_vendor.index.$tool.setCartBadge(cart.value.total);
      });
    }
    function numbeChange(e) {
      let list = cart.value.list;
      list[e.index];
      let params = {
        sn: list[e.index].sn,
        number: e.number
      };
      common_vendor.index.$api.update2cart(params, (res) => {
        cart.value = res.data.cart;
      });
    }
    function delItem(index) {
      common_vendor.index.$tool.confirm("确认要从购物车删除此项吗？", () => {
        let list = cart.value.list;
        list[index];
        let params = {
          sn: list[index].sn
        };
        del2cart(params);
      });
    }
    function del2cart(params) {
      common_vendor.index.$api.del2cart(params, (res) => {
        cart.value = res.data.cart;
      });
    }
    function buyNow() {
      if (cart.value.total == 0)
        return common_vendor.index.$tool.tip("商品列表不能为空");
      common_vendor.index.$login.judgePhone(() => {
        common_vendor.index.$tool.navto("/pages/order/confirm?from=cart");
      });
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.f(cart.value.list, (item, index, i0) => {
          return {
            a: item.id,
            b: common_vendor.o(delItem, item.id),
            c: common_vendor.o(numbeChange, item.id),
            d: common_vendor.o(check, item.id),
            e: "c91e7611-0-" + i0,
            f: common_vendor.p({
              item,
              index
            })
          };
        }),
        b: cart.value.list && cart.value.list.length < 1
      }, cart.value.list && cart.value.list.length < 1 ? {
        c: common_vendor.p({
          status: "nomore"
        })
      } : {}, {
        d: cart.value.is_checked
      }, cart.value.is_checked ? {
        e: common_vendor.p({
          type: "checkbox-filled",
          size: "20",
          color: "#5aa1d8"
        })
      } : {
        f: common_vendor.p({
          type: "checkbox",
          size: "20",
          color: "#ccc"
        })
      }, {
        g: common_vendor.o(checkAll),
        h: common_vendor.t(cart.value.amount || "0.00"),
        i: common_vendor.t(cart.value.total || 0),
        j: common_vendor.o(buyNow),
        k: tabBarHeight.value
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-c91e7611"]]);
wx.createPage(MiniProgramPage);
