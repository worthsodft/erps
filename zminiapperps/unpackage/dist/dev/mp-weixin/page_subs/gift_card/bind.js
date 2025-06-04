"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_v_line_title2 = common_vendor.resolveComponent("v-line-title");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  (_easycom_v_line_title2 + _easycom_uni_easyinput2)();
}
const _easycom_v_line_title = () => "../../components/v-line-title/v-line-title.js";
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
if (!Math) {
  (_easycom_v_line_title + _easycom_uni_easyinput)();
}
const captchaType = "GiftCardBindCaptcha";
const _sfc_main = {
  __name: "bind",
  setup(__props) {
    const captcha = common_vendor.ref({});
    common_vendor.ref();
    const banners = common_vendor.ref([]);
    const card = common_vendor.ref();
    const formData = common_vendor.ref({ sn: "", code: "", vcode: "", vuniqid: "" });
    const remarkList = common_vendor.ref([]);
    getCaptcha();
    getInitDataGiftCardBind();
    function getCaptcha() {
      common_vendor.index.$http.post("v1/captcha", { type: captchaType }).then((res) => {
        captcha.value = res.data;
        formData.value.vuniqid = res.data.uniqid;
      });
    }
    function getInitDataGiftCardBind() {
      common_vendor.index.$http.get("v1/getInitDataGiftCardBind").then((res) => {
        banners.value = res.data.banners;
        remarkList.value = res.data.remarks;
      });
    }
    function valid() {
      if (!formData.value.sn)
        return common_vendor.index.$tool.tip("请扫描或输入卡号");
      if (!formData.value.code)
        return common_vendor.index.$tool.tip("请输入密码");
      if (!formData.value.vcode)
        return common_vendor.index.$tool.tip("请输入验证码");
      if (!formData.value.vuniqid)
        return common_vendor.index.$tool.tip("请输入验证码标识");
      return true;
    }
    function search() {
      if (!valid())
        return;
      doSearch();
    }
    let doSearch = common_vendor.index.$tool.debounce(() => {
      formData.value.type = captchaType;
      common_vendor.index.$http.post("v1/searchGiftCardForMiniBind", formData.value).then((res) => {
        getCaptcha();
        formData.value.vcode = "";
        if (res.code == 1) {
          card.value = res.data.card;
          common_vendor.index.$tool.alert("卡有效，请核对信息无误后，点击“确认绑定”按钮，进行绑卡操作");
        }
      }).catch((err) => {
        getCaptcha();
      });
    });
    function bind() {
      if (!valid())
        return;
      doBind();
    }
    let doBind = common_vendor.index.$tool.debounce(() => {
      formData.value.type = captchaType;
      common_vendor.index.$http.post("v1/bindGiftCardForMiniBind", formData.value).then((res) => {
        getCaptcha();
        if (res.code == 1)
          common_vendor.index.$tool.tip(res.info || "操作成功");
        resetFormData();
      }).catch((err) => {
        getCaptcha();
      });
    });
    function resetFormData() {
      formData.value.sn = "";
      formData.value.code = "";
      formData.value.vcode = "";
      card.value = "";
    }
    function getSnFromScan() {
      common_vendor.index.scanCode({
        success({ result }) {
          if (result)
            formData.value.sn = result;
          else
            console.log("扫码失败");
        },
        fail(err) {
          console.log(err);
        }
      });
    }
    function getCodeFromScan() {
      common_vendor.index.scanCode({
        success({ result }) {
          if (result)
            formData.value.code = result;
          else
            console.log("扫码失败");
        },
        fail(err) {
          console.log(err);
        }
      });
    }
    function list() {
      common_vendor.index.$tool.navto("/page_subs/gift_card/list");
    }
    function logs() {
      common_vendor.index.$tool.navto("/page_subs/gift_card/logs");
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: card.value
      }, card.value ? common_vendor.e({
        b: common_vendor.t(card.value.sn),
        c: common_vendor.t(card.value.bind_expire_at),
        d: common_vendor.t(card.value.useful_days),
        e: card.value.use_type == 0
      }, card.value.use_type == 0 ? {
        f: common_vendor.t(card.value.has)
      } : {
        g: common_vendor.t(card.value.take_goods_sn_txt),
        h: common_vendor.t(card.value.has)
      }) : {
        i: common_vendor.f(banners.value, (item, index, i0) => {
          return {
            a: item,
            b: index
          };
        })
      }, {
        j: common_vendor.p({
          title: "卡号："
        }),
        k: common_vendor.o(getSnFromScan),
        l: common_vendor.o(($event) => formData.value.sn = $event),
        m: common_vendor.p({
          suffixIcon: "scan",
          trim: true,
          placeholder: "请扫描或输入卡号",
          modelValue: formData.value.sn
        }),
        n: common_vendor.p({
          title: "密码："
        }),
        o: common_vendor.o(getCodeFromScan),
        p: common_vendor.o(($event) => formData.value.code = $event),
        q: common_vendor.p({
          suffixIcon: "scan",
          trim: true,
          placeholder: "请输入密码",
          modelValue: formData.value.code
        }),
        r: common_vendor.p({
          title: "验证码："
        }),
        s: common_vendor.o(($event) => formData.value.vcode = $event),
        t: common_vendor.p({
          trim: true,
          placeholder: "请输入验证码",
          modelValue: formData.value.vcode
        }),
        v: captcha.value.image || "",
        w: common_vendor.o(getCaptcha),
        x: card.value
      }, card.value ? {
        y: common_vendor.o(bind)
      } : {
        z: common_vendor.o(search)
      }, {
        A: common_vendor.o(logs),
        B: common_vendor.o(list),
        C: remarkList.value.length > 0
      }, remarkList.value.length > 0 ? {
        D: common_vendor.p({
          title: "使用说明"
        }),
        E: common_vendor.f(remarkList.value, (item, idx, i0) => {
          return {
            a: common_vendor.t(idx + 1),
            b: common_vendor.t(item),
            c: idx
          };
        })
      } : {});
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-0902cdb9"]]);
wx.createPage(MiniProgramPage);
