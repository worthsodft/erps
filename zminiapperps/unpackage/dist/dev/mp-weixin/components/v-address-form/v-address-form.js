"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_address_picker2 = common_vendor.resolveComponent("v-address-picker");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  (_easycom_v_address_picker2 + _easycom_uni_easyinput2 + _easycom_v_button_w802 + _easycom_uni_popup2)();
}
const _easycom_v_address_picker = () => "../v-address-picker/v-address-picker.js";
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
const _easycom_v_button_w80 = () => "../v-button-w80/v-button-w80.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
if (!Math) {
  (_easycom_v_address_picker + _easycom_uni_easyinput + _easycom_v_button_w80 + _easycom_uni_popup)();
}
const _sfc_main = {
  __name: "v-address-form",
  emits: ["saveSuccess"],
  setup(__props, { expose: __expose, emit: __emit }) {
    let gid = "";
    const blankData = {
      gid: "",
      district: "",
      detail: "",
      link_name: "",
      link_phone: "",
      is_default: 0
    };
    const item = common_vendor.ref({ ...blankData });
    const cities = common_vendor.ref([]);
    common_vendor.onLoad((opts) => {
      if (opts.gid) {
        gid = opts.gid;
        getDetail();
      }
    });
    const addressFormPopup = common_vendor.ref();
    const emit = __emit;
    const getCities = async () => {
      const res = await common_vendor.index.$http.get("v1/getCities");
      cities.value = res.data.cities;
    };
    async function getDetail() {
      const res = await common_vendor.index.$http.get("v1/getAddressDetail/" + gid);
      item.value = res.data.address;
    }
    function addressChange(e) {
      item.value.district = e[0].text;
    }
    const save = common_vendor.index.$tool.throttle(async () => {
      if (!valid())
        return;
      const res = await common_vendor.index.$http.post("v1/saveAddress", item.value);
      common_vendor.index.$tool.tip(res.info, true, () => {
        emit("saveSuccess", res.data.item);
      });
    });
    function reset() {
      item.value = { ...blankData };
    }
    function valid() {
      if (!item.value.district)
        return common_vendor.index.$tool.tip("请选择区域");
      if (!item.value.detail)
        return common_vendor.index.$tool.tip("请输入详细地址");
      if (!item.value.link_name)
        return common_vendor.index.$tool.tip("请输入联系人");
      if (!item.value.link_phone)
        return common_vendor.index.$tool.tip("请输入联系电话");
      return true;
    }
    function changeDefault({ detail }) {
      item.value.is_default = detail.value ? 1 : 0;
    }
    function open() {
      addressFormPopup.value.open();
    }
    function close() {
      addressFormPopup.value.close();
    }
    __expose({
      open,
      close,
      reset,
      getCities
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o(addressChange),
        b: common_vendor.o(($event) => item.value.district = $event),
        c: common_vendor.p({
          cities: cities.value,
          modelValue: item.value.district
        }),
        d: common_vendor.o(($event) => item.value.detail = $event),
        e: common_vendor.p({
          type: "textarea",
          maxlength: "200",
          autoHeight: true,
          placeholder: "请输入详细地址",
          modelValue: item.value.detail
        }),
        f: common_vendor.o(($event) => item.value.link_name = $event),
        g: common_vendor.p({
          autoHeight: true,
          maxlength: "20",
          placeholder: "请输入联系人姓名",
          modelValue: item.value.link_name
        }),
        h: common_vendor.o(($event) => item.value.link_phone = $event),
        i: common_vendor.p({
          autoHeight: true,
          type: "number",
          maxlength: "20",
          placeholder: "请输入联系电话",
          modelValue: item.value.link_phone
        }),
        j: item.value.is_default == 1,
        k: common_vendor.o(changeDefault),
        l: common_vendor.o(common_vendor.unref(save)),
        m: common_vendor.p({
          title: "提交保存"
        }),
        n: common_vendor.sr(addressFormPopup, "52f66845-0", {
          "k": "addressFormPopup"
        }),
        o: common_vendor.p({
          ["background-color"]: "#fff",
          ["mask-background-color"]: "#000000dd"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-52f66845"]]);
wx.createComponent(Component);
