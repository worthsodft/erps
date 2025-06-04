"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_pay_type_gift_card2 = common_vendor.resolveComponent("v-pay-type-gift-card");
  const _easycom_uni_icons2 = common_vendor.resolveComponent("uni-icons");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  (_easycom_v_pay_type_gift_card2 + _easycom_uni_icons2 + _easycom_uni_popup2)();
}
const _easycom_v_pay_type_gift_card = () => "../v-pay-type-gift-card/v-pay-type-gift-card.js";
const _easycom_uni_icons = () => "../../uni_modules/uni-icons/components/uni-icons/uni-icons.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
if (!Math) {
  (_easycom_v_pay_type_gift_card + _easycom_uni_icons + _easycom_uni_popup)();
}
const _sfc_main = {
  __name: "v-pay-type-select",
  props: {
    list: Object
  },
  emits: ["select", "reset", "useGiftCard"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const money = common_vendor.ref(0);
    const popup = common_vendor.ref();
    const giftCardList = common_vendor.ref([]);
    const emit = __emit;
    const isGiftCardChecked = common_vendor.ref(false);
    common_vendor.index.$api.getUserInfo((res) => {
      money.value = res.data.userInfo.money;
    });
    getGiftCardList();
    function getGiftCardList() {
      common_vendor.index.$http.get("v1/getMyUsableGiftCardList").then((res) => {
        giftCardList.value = res.data.giftCardList;
      });
    }
    function giftCardChange(i) {
      giftCardList.value[i].checked = !giftCardList.value[i].checked;
      isGiftCardChecked.value = hasGiftCardChecked();
    }
    function useGiftCard(payType) {
      let selectedSn = [];
      let cardHasMoney = 0;
      for (let i = 0, len = giftCardList.value.length; i < len; i++) {
        let item = giftCardList.value[i];
        if (item.checked) {
          cardHasMoney += +item.has;
          selectedSn.push(item.sn);
        }
      }
      emit("select", { payType, selectedSn, cardHasMoney });
    }
    function hasGiftCardChecked() {
      let isChecked = false;
      for (let i = 0, len = giftCardList.value.length; i < len; i++) {
        if (giftCardList.value[i].checked) {
          isChecked = true;
          break;
        }
      }
      return isChecked;
    }
    function open() {
      popup.value.open("bottom");
    }
    function close() {
      popup.value.close();
    }
    function select(payType) {
      for (let i = 0, len = giftCardList.value.length; i < len; i++) {
        if (giftCardList.value[i].checked) {
          giftCardList.value[i].checked = false;
        }
      }
      isGiftCardChecked.value = false;
      emit("select", { payType });
    }
    __expose({
      open,
      close
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.f(__props.list, (item, index, i0) => {
          return common_vendor.e({
            a: index == "giftcard"
          }, index == "giftcard" ? common_vendor.e({
            b: common_vendor.t(item),
            c: giftCardList.value.length > 0
          }, giftCardList.value.length > 0 ? common_vendor.e({
            d: common_vendor.f(giftCardList.value, (card, i, i1) => {
              return {
                a: card.sn,
                b: common_vendor.o(giftCardChange, card.sn),
                c: "0f29aa57-1-" + i0 + "-" + i1 + ",0f29aa57-0",
                d: common_vendor.p({
                  card,
                  index: i
                })
              };
            }),
            e: isGiftCardChecked.value
          }, isGiftCardChecked.value ? {
            f: common_vendor.o(($event) => useGiftCard(index), item.id)
          } : {}) : {}) : common_vendor.e({
            g: common_vendor.t(item),
            h: index == "yue"
          }, index == "yue" ? {
            i: common_vendor.t(money.value)
          } : {}, {
            j: "0f29aa57-2-" + i0 + ",0f29aa57-0",
            k: common_vendor.p({
              type: "right",
              color: "#999"
            }),
            l: common_vendor.o(($event) => select(index), item.id)
          }), {
            m: item.id
          });
        }),
        b: common_vendor.sr(popup, "0f29aa57-0", {
          "k": "popup"
        }),
        c: common_vendor.p({
          ["background-color"]: "#fff"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-0f29aa57"]]);
wx.createComponent(Component);
