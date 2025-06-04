"use strict";
const common_vendor = require("../../common/vendor.js");
const extend_config = require("../../extend/config.js");
if (!Array) {
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  (_easycom_uni_easyinput2 + _easycom_v_button_w802)();
}
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
const _easycom_v_button_w80 = () => "../../components/v-button-w80/v-button-w80.js";
if (!Math) {
  (_easycom_uni_easyinput + _easycom_v_button_w80)();
}
const _sfc_main = {
  __name: "form",
  setup(__props) {
    const defaultAvatar = common_vendor.ref(extend_config.config.defaultAvatar);
    const userInfo = common_vendor.ref({
      avatar: "",
      nickname: "",
      realname: "",
      phone: ""
    });
    common_vendor.index.$api.getUserInfo((res) => {
      userInfo.value = res.data.userInfo;
    });
    function getphonenumber({ detail }) {
      if (!detail.code)
        return;
      common_vendor.index.$http.post("v1/getPhoneNumberByCode", { code: detail.code }).then((res) => {
        userInfo.value.phone = res.data.phone;
      });
    }
    function chooseavatar({ detail }) {
      let tmp = detail.avatarUrl;
      userInfo.value.avatar = tmp;
    }
    function choosenickname({ detail }) {
      if (!detail.value)
        return;
      userInfo.value.nickname = detail.value;
    }
    function submit() {
      if (!valid())
        return;
      if (!common_vendor.index.$tool.isRemoteUrl(userInfo.value.avatar)) {
        upload(() => {
          updateUserInfo();
        });
      } else {
        updateUserInfo();
      }
    }
    const updateUserInfo = common_vendor.index.$tool.throttle(() => {
      let params = {
        avatar: userInfo.value.avatar,
        nickname: userInfo.value.nickname,
        realname: userInfo.value.realname,
        phone: userInfo.value.phone
      };
      common_vendor.index.$http.post("v1/updateUserInfo", params).then((res) => {
        if (res.code == 1) {
          common_vendor.index.$login.updateTokenCache(res.data);
          common_vendor.index.$tool.tip(res.info || "操作成功", true, () => {
            common_vendor.index.$tool.back();
          });
        } else {
          common_vendor.index.$tool.tip(res.info || "操作失败");
        }
      });
    });
    async function upload(cb) {
      let params = {
        filePath: userInfo.value.avatar,
        name: "file"
      };
      const res = await common_vendor.index.$http.upload("v1/upload", params);
      userInfo.value.avatar = res.data.url;
      cb && cb();
    }
    function valid() {
      if (!userInfo.value.avatar)
        return common_vendor.index.$tool.tip("请选择头像");
      if (!userInfo.value.nickname)
        return common_vendor.index.$tool.tip("请输入昵称");
      if (!userInfo.value.phone)
        return common_vendor.index.$tool.tip("请选择电话号码");
      return true;
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: userInfo.value.avatar || defaultAvatar.value,
        b: !userInfo.value.avatar
      }, !userInfo.value.avatar ? {} : {}, {
        c: common_vendor.o(chooseavatar),
        d: common_vendor.o(choosenickname),
        e: common_vendor.o(($event) => userInfo.value.nickname = $event),
        f: common_vendor.p({
          type: "nickname",
          placeholder: "请输入昵称",
          modelValue: userInfo.value.nickname
        }),
        g: common_vendor.o(($event) => userInfo.value.realname = $event),
        h: common_vendor.p({
          placeholder: "请输入真实姓名",
          modelValue: userInfo.value.realname
        }),
        i: common_vendor.p({
          disabled: true,
          value: userInfo.value.phone,
          placeholder: "点击获取电话号码"
        }),
        j: common_vendor.o(getphonenumber),
        k: common_vendor.o(submit),
        l: common_vendor.p({
          title: "保存提交"
        })
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-f4e28404"]]);
wx.createPage(MiniProgramPage);
