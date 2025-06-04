"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_address_picker2 = common_vendor.resolveComponent("v-address-picker");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  (_easycom_v_address_picker2 + _easycom_uni_easyinput2 + _easycom_v_button_w802)();
}
const _easycom_v_address_picker = () => "../../components/v-address-picker/v-address-picker.js";
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
const _easycom_v_button_w80 = () => "../../components/v-button-w80/v-button-w80.js";
if (!Math) {
  (_easycom_v_address_picker + _easycom_uni_easyinput + _easycom_v_button_w80)();
}
const _sfc_main = {
  __name: "address-edit",
  setup(__props) {
    let gid = "";
    const item = common_vendor.ref({
      gid: "",
      district: "",
      detail: "",
      link_name: "",
      link_phone: "",
      is_default: 0
    });
    const cities = common_vendor.ref([]);
    common_vendor.onLoad((opts) => {
      if (opts.gid) {
        gid = opts.gid;
        getDetail();
      }
      getCities();
    });
    const getCities = async () => {
      const res = await common_vendor.index.$http.get("v1/getCities");
      cities.value = res.data.cities;
    };
    async function getDetail() {
      const res = await common_vendor.index.$http.get("v1/getAddressDetail/" + gid);
      item.value = res.data.address;
      item.value.district = res.data.address.district;
    }
    function addressChange(e) {
      item.value.district = e[0].value;
    }
    const save = common_vendor.index.$tool.throttle(async () => {
      if (!valid())
        return;
      const res = await common_vendor.index.$http.post("v1/saveAddress", item.value);
      common_vendor.index.$tool.tip(res.info, true, () => {
        common_vendor.index.$tool.navto("/pages/my/address");
      });
      console.log(res);
    });
    function valid() {
      if (!item.value.district)
        return common_vendor.index.$tool.tip("请选择区域");
      if (!item.value.detail)
        return common_vendor.index.$tool.tip("请输入详细地址");
      if (!item.value.link_name)
        return common_vendor.index.$tool.tip("请输入联系人");
      if (!item.value.link_phone)
        return common_vendor.index.$tool.tip("请输入联系电话");
      if (!common_vendor.index.$tool.isPhone(item.value.link_phone))
        return common_vendor.index.$tool.tip("电话号码格式错误");
      return true;
    }
    function changeDefault({ detail }) {
      item.value.is_default = detail.value ? 1 : 0;
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o(addressChange),
        b: common_vendor.p({
          cities: cities.value,
          value: item.value.district
        }),
        c: common_vendor.o(($event) => item.value.detail = $event),
        d: common_vendor.p({
          type: "textarea",
          maxlength: "200",
          autoHeight: true,
          placeholder: "请输入详细地址",
          modelValue: item.value.detail
        }),
        e: common_vendor.o(($event) => item.value.link_name = $event),
        f: common_vendor.p({
          autoHeight: true,
          maxlength: "20",
          placeholder: "请输入联系人姓名",
          modelValue: item.value.link_name
        }),
        g: common_vendor.o(($event) => item.value.link_phone = $event),
        h: common_vendor.p({
          autoHeight: true,
          type: "number",
          maxlength: "20",
          placeholder: "请输入联系电话",
          modelValue: item.value.link_phone
        }),
        i: item.value.is_default == 1,
        j: common_vendor.o(changeDefault),
        k: common_vendor.o(common_vendor.unref(save)),
        l: common_vendor.p({
          title: "提交保存"
        })
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-7272ca84"]]);
wx.createPage(MiniProgramPage);
