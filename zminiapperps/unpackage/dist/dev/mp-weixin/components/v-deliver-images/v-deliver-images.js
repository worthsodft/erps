"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_file_picker2 = common_vendor.resolveComponent("uni-file-picker");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  (_easycom_uni_file_picker2 + _easycom_uni_easyinput2 + _easycom_v_button_w802 + _easycom_uni_popup2)();
}
const _easycom_uni_file_picker = () => "../../uni_modules/uni-file-picker/components/uni-file-picker/uni-file-picker.js";
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
const _easycom_v_button_w80 = () => "../v-button-w80/v-button-w80.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
if (!Math) {
  (_easycom_uni_file_picker + _easycom_uni_easyinput + _easycom_v_button_w80 + _easycom_uni_popup)();
}
const _sfc_main = {
  __name: "v-deliver-images",
  props: {
    images: Array,
    sn: String
  },
  emits: ["success"],
  setup(__props, { expose: __expose, emit: __emit }) {
    const props = __props;
    const itemPopup = common_vendor.ref();
    const deliverRemark = common_vendor.ref("");
    let urls = [];
    let temps = [];
    const emit = __emit;
    let submit = common_vendor.index.$tool.throttle(() => {
      if (temps.length == 0)
        return common_vendor.index.$tool.tip("请上传配送照片");
      common_vendor.index.$tool.confirm("是否确认已送达？", () => {
        doSubmit();
      });
    }, 1e3);
    function doSubmit() {
      common_vendor.index.$tool.showLoading("上传中...");
      uploads(() => {
        common_vendor.index.$tool.hideLoading();
        let params = {
          sn: props.sn,
          urls,
          remark: deliverRemark.value
        };
        common_vendor.index.$http.post("v1/deliverOrder", params).then((res) => {
          if (res.code == 1) {
            common_vendor.index.$tool.tip(res.info || "操作成功");
            close();
            emit("success");
          } else
            common_vendor.index.$tool.tip(res.info || "系统错误");
        });
      });
    }
    function selectAfter({ tempFilePaths }) {
      temps = [...temps, ...tempFilePaths];
    }
    function deleteAfter({ index }) {
      temps.splice(index, 1);
    }
    async function uploads(cb) {
      urls = [];
      for (let i in temps) {
        let params = {
          filePath: temps[i],
          name: "file"
        };
        const res = await common_vendor.index.$http.upload("v1/upload", params);
        urls.push(res.data.url);
      }
      cb && cb();
    }
    function open() {
      itemPopup.value.open("bottom");
    }
    function close() {
      itemPopup.value.close();
    }
    function popupChange(e) {
    }
    __expose({
      open,
      close
    });
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o(selectAfter),
        b: common_vendor.o(deleteAfter),
        c: common_vendor.p({
          limit: "3",
          title: "最多选择3张图片"
        }),
        d: common_vendor.o(($event) => deliverRemark.value = $event),
        e: common_vendor.p({
          type: "textarea",
          maxlength: "200",
          placeholder: "请输入配送说明",
          modelValue: deliverRemark.value
        }),
        f: common_vendor.o(common_vendor.unref(submit)),
        g: common_vendor.p({
          title: "确定提交"
        }),
        h: common_vendor.sr(itemPopup, "f8b5114c-0", {
          "k": "itemPopup"
        }),
        i: common_vendor.o(popupChange),
        j: common_vendor.p({
          ["background-color"]: "#fff"
        })
      };
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-f8b5114c"]]);
wx.createComponent(Component);
