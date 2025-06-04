"use strict";
const common_vendor = require("../../common/vendor.js");
if (!Array) {
  const _easycom_uni_segmented_control2 = common_vendor.resolveComponent("uni-segmented-control");
  const _easycom_v_order_item22 = common_vendor.resolveComponent("v-order-item2");
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  const _easycom_uni_popup_dialog2 = common_vendor.resolveComponent("uni-popup-dialog");
  const _easycom_uni_popup2 = common_vendor.resolveComponent("uni-popup");
  const _easycom_v_button_w802 = common_vendor.resolveComponent("v-button-w80");
  const _easycom_uni_data_checkbox2 = common_vendor.resolveComponent("uni-data-checkbox");
  const _easycom_uni_easyinput2 = common_vendor.resolveComponent("uni-easyinput");
  (_easycom_uni_segmented_control2 + _easycom_v_order_item22 + _easycom_uni_load_more2 + _easycom_uni_popup_dialog2 + _easycom_uni_popup2 + _easycom_v_button_w802 + _easycom_uni_data_checkbox2 + _easycom_uni_easyinput2)();
}
const _easycom_uni_segmented_control = () => "../../uni_modules/uni-segmented-control/components/uni-segmented-control/uni-segmented-control.js";
const _easycom_v_order_item2 = () => "../../components/v-order-item2/v-order-item2.js";
const _easycom_uni_load_more = () => "../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
const _easycom_uni_popup_dialog = () => "../../uni_modules/uni-popup/components/uni-popup-dialog/uni-popup-dialog.js";
const _easycom_uni_popup = () => "../../uni_modules/uni-popup/components/uni-popup/uni-popup.js";
const _easycom_v_button_w80 = () => "../../components/v-button-w80/v-button-w80.js";
const _easycom_uni_data_checkbox = () => "../../uni_modules/uni-data-checkbox/components/uni-data-checkbox/uni-data-checkbox.js";
const _easycom_uni_easyinput = () => "../../uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput.js";
if (!Math) {
  (_easycom_uni_segmented_control + _easycom_v_order_item2 + _easycom_uni_load_more + _easycom_uni_popup_dialog + _easycom_uni_popup + _easycom_v_button_w80 + _easycom_uni_data_checkbox + _easycom_uni_easyinput)();
}
const _sfc_main = {
  __name: "list",
  setup(__props) {
    const invoiceInputStyle = common_vendor.ref({ width: "400rpx" });
    const orderTabs = common_vendor.ref();
    const orderStausIndexMap = { 0: 0, 1: 1, 2: 2, 9: 3 };
    const group = common_vendor.ref(
      [
        {
          loadStatus: "more",
          page: 1,
          total: 0,
          list: []
        },
        {
          loadStatus: "more",
          page: 1,
          total: 0,
          list: []
        },
        {
          loadStatus: "more",
          page: 1,
          total: 0,
          list: []
        },
        {
          loadStatus: "more",
          page: 1,
          total: 0,
          list: []
        }
      ]
    );
    const isHidePopup = common_vendor.ref(true);
    const current = common_vendor.ref(0);
    const isLoading = common_vendor.ref(false);
    const refundReasonPopup = common_vendor.ref();
    const currOrder = common_vendor.ref({});
    const finishQrcodePopup = common_vendor.ref();
    const finishQrcodeTip = common_vendor.ref("");
    const applyInvoiceFormPopup = common_vendor.ref();
    const applyInvoiceList = common_vendor.ref([]);
    const invoiceFormData = common_vendor.ref({ buyer_type: 1, invoice_type: 1 });
    let formData = common_vendor.index.$tool.cache("invoiceFormData");
    if (formData)
      invoiceFormData.value = formData;
    common_vendor.onLoad((opts) => {
      current.value = orderStausIndexMap[+opts.status || 0];
      getTabsData();
      getList();
    });
    function selectInvoice(sn) {
      let pos = applyInvoiceList.value.indexOf(sn);
      if (pos == -1) {
        applyInvoiceList.value.push(sn);
      } else {
        applyInvoiceList.value.splice(pos, 1);
      }
    }
    function showInvoiceFormPopup(e) {
      currOrder.value = e;
      invoiceFormData.value.sns = applyInvoiceList.value;
      applyInvoiceFormPopup.value.open();
    }
    function showRefundReasonPopup(e) {
      currOrder.value = e;
      refundReasonPopup.value.open();
    }
    function showFinishOrderPopup(e) {
      currOrder.value = e;
      finishQrcodePopup.value.open();
    }
    function buyerTypeChange({ currentIndex }) {
      invoiceFormData.value.buyer_type = currentIndex;
      if (currentIndex == 0)
        invoiceFormData.value.invoice_type = 0;
    }
    function submitApplyInvoice() {
      if (!validInvoiceForm())
        return;
      invoiceFormData.value;
      common_vendor.index.$tool.cache("invoiceFormData", invoiceFormData.value);
      common_vendor.index.$tool.confirm("确认所填写信息全部正确，并提交开票申请吗？", () => {
        common_vendor.index.$http.post("v1/invoiceApply", invoiceFormData.value).then((res) => {
          common_vendor.index.$tool.tip(res.info || "操作成功");
          applyInvoiceFormPopup.value.close();
          applyInvoiceList.value = [];
          reload();
        });
      });
    }
    function validInvoiceForm() {
      let formData2 = invoiceFormData.value;
      if (applyInvoiceList.value.length == 0)
        return common_vendor.index.$tool.tip("请选择要开票的订单");
      if (![0, 1].includes(formData2.buyer_type))
        return common_vendor.index.$tool.tip("请选择购买方类型");
      if (![0, 1].includes(formData2.invoice_type))
        return common_vendor.index.$tool.tip("请选择开票类型");
      if (formData2.buyer_type == common_vendor.index.$enum.invoice.BUYER_TYPE_P && formData2.invoice_type == common_vendor.index.$enum.invoice.INVOICE_TYPE_S)
        return common_vendor.index.$tool.tip("个人不能开具专用发票");
      if (!formData2.title)
        return common_vendor.index.$tool.tip("请输入开票名称");
      if (formData2.buyer_type == common_vendor.index.$enum.invoice.BUYER_TYPE_E && !formData2.taxno)
        return common_vendor.index.$tool.tip("请输入公司税号");
      if (!formData2.email)
        return common_vendor.index.$tool.tip("请输入收票邮箱");
      if (!common_vendor.index.$tool.isEmail(formData2.email))
        return common_vendor.index.$tool.tip("请输入正确的邮箱地址");
      return true;
    }
    function refundConfirm(reason) {
      if (!reason)
        return common_vendor.index.$tool.tip("请输入退款原因");
      doRefundApply(reason);
    }
    function doRefundApply(reason) {
      common_vendor.index.$http.post("v1/refundApply/" + currOrder.value.sn, { reason }).then((res) => {
        if (res.code == 1)
          common_vendor.index.$tool.success(res.info, true, () => {
            refundApplySuccess();
          });
        else
          common_vendor.index.$tool.tip(res.info || "申请退款失败");
      });
    }
    function takeSuccess() {
      reload();
    }
    function cancelSuccess() {
      reload();
    }
    function refundApplySuccess() {
      refundReasonPopup.value.close();
      reload();
      setTimeout(() => {
        current.value = 3;
        reload();
      }, 1e3);
    }
    function getTabsData() {
      common_vendor.index.$http.get("v1/getOrderTabsData").then((res) => {
        orderTabs.value = res.data.tabsData;
        finishQrcodeTip.value = res.data.finishQrcodeTip;
      });
    }
    function getList(type) {
      let groupItem = group.value[current.value];
      if (groupItem.loadStatus != "more")
        return;
      groupItem.loadStatus = "loading";
      let status = common_vendor.index.$tool.invert(orderStausIndexMap)[current.value] || 0;
      let params = {
        page: groupItem.page
      };
      common_vendor.index.$http.get("v1/getOrderPageDataByStatus/" + status, params).then((res) => {
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        let pageData = res.data.pageData;
        if (pageData.total == 0) {
          groupItem.loadStatus = "nomore";
          groupItem.list = [];
          group.value[current.value] = groupItem;
          return;
        }
        if (pageData.data.length == 0) {
          groupItem.loadStatus = "nomore";
          group.value[current.value] = groupItem;
          return;
        }
        if (type == "reset")
          groupItem.list = [...pageData.data];
        else
          groupItem.list = [...groupItem.list, ...pageData.data];
        if (groupItem.total == 0)
          groupItem.total = pageData.total;
        if (groupItem.list.length >= groupItem.total) {
          groupItem.loadStatus = "nomore";
        } else {
          groupItem.loadStatus = "more";
          groupItem.page++;
        }
        group.value[current.value] = groupItem;
        isLoading.value = false;
      });
    }
    function reset() {
      let groupItem = group.value[current.value];
      groupItem.loadStatus = "more";
      groupItem.page = 1;
      groupItem.total = 0;
      group.value[current.value] = groupItem;
    }
    function reload() {
      if (isLoading.value)
        return;
      isLoading.value = true;
      setTimeout(() => isLoading.value = false, 500);
      reset();
      getList("reset");
    }
    function reloadStop() {
      isLoading.value = false;
    }
    function select({ currentIndex }) {
      current.value = currentIndex;
    }
    function swiperChange({ detail }) {
      applyInvoiceList.value = [];
      if (current.value != detail.current)
        current.value = detail.current;
      if (group.value[current.value].total == 0) {
        getList();
      }
    }
    function scrolltolower() {
      getList();
    }
    return (_ctx, _cache) => {
      return {
        a: common_vendor.o(select),
        b: common_vendor.p({
          current: current.value,
          values: orderTabs.value,
          ["style-type"]: "text",
          ["active-color"]: "#5aa1d8"
        }),
        c: common_vendor.f(group.value, (item, index, i0) => {
          return common_vendor.e(current.value == 2 && applyInvoiceList.value.length > 0 ? {
            a: common_vendor.o(showInvoiceFormPopup, index)
          } : {}, {
            b: common_vendor.f(item.list, (vo, index2, i1) => {
              return {
                a: common_vendor.o(($event) => isHidePopup.value = false, vo.id),
                b: common_vendor.o(($event) => isHidePopup.value = true, vo.id),
                c: common_vendor.o(takeSuccess, vo.id),
                d: common_vendor.o(cancelSuccess, vo.id),
                e: common_vendor.o(refundApplySuccess, vo.id),
                f: common_vendor.o(showFinishOrderPopup, vo.id),
                g: common_vendor.o(showRefundReasonPopup, vo.id),
                h: common_vendor.o(selectInvoice, vo.id),
                i: "456ecf67-1-" + i0 + "-" + i1,
                j: common_vendor.p({
                  item: vo,
                  isInvoice: applyInvoiceList.value.includes(vo.sn)
                }),
                k: vo.id
              };
            }),
            c: common_vendor.o(getList, index),
            d: "456ecf67-2-" + i0,
            e: common_vendor.p({
              status: item.loadStatus
            }),
            f: common_vendor.o(scrolltolower, index),
            g: common_vendor.o(reload, index),
            h: common_vendor.o(reloadStop, index),
            i: common_vendor.o(reloadStop, index),
            j: index
          });
        }),
        d: current.value == 2 && applyInvoiceList.value.length > 0,
        e: isHidePopup.value,
        f: isLoading.value,
        g: current.value,
        h: common_vendor.o(swiperChange),
        i: common_vendor.o(($event) => refundReasonPopup.value.close()),
        j: common_vendor.o(refundConfirm),
        k: common_vendor.p({
          mode: "input",
          maxlength: 50,
          beforeClose: true,
          title: "退款原因",
          placeholder: "请输入退款原因"
        }),
        l: common_vendor.sr(refundReasonPopup, "456ecf67-3", {
          "k": "refundReasonPopup"
        }),
        m: common_vendor.p({
          type: "dialog"
        }),
        n: currOrder.value.qrcode,
        o: common_vendor.t(currOrder.value.sn),
        p: common_vendor.t(finishQrcodeTip.value),
        q: common_vendor.o(($event) => finishQrcodePopup.value.close()),
        r: common_vendor.p({
          title: "关 闭"
        }),
        s: common_vendor.sr(finishQrcodePopup, "456ecf67-5", {
          "k": "finishQrcodePopup"
        }),
        t: common_vendor.p({
          ["background-color"]: "#fff"
        }),
        v: common_vendor.o(buyerTypeChange),
        w: common_vendor.p({
          activeColor: "#6abfff",
          current: invoiceFormData.value.buyer_type ?? 0,
          values: ["自然人", "公司"],
          styleType: "button"
        }),
        x: common_vendor.t(applyInvoiceList.value.length),
        y: common_vendor.o(($event) => invoiceFormData.value.invoice_type = $event),
        z: common_vendor.p({
          localdata: [{
            text: "普通发票",
            value: 0
          }, {
            text: "专用发票",
            value: 1,
            disable: invoiceFormData.value.buyer_type == 0
          }],
          modelValue: invoiceFormData.value.invoice_type
        }),
        A: common_vendor.s(invoiceInputStyle.value),
        B: common_vendor.o(($event) => invoiceFormData.value.title = $event),
        C: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.title
        }),
        D: invoiceFormData.value.buyer_type == 1 ? 1 : "",
        E: common_vendor.s(invoiceInputStyle.value),
        F: common_vendor.o(($event) => invoiceFormData.value.taxno = $event),
        G: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.taxno
        }),
        H: common_vendor.s(invoiceInputStyle.value),
        I: common_vendor.o(($event) => invoiceFormData.value.address = $event),
        J: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.address
        }),
        K: common_vendor.s(invoiceInputStyle.value),
        L: common_vendor.o(($event) => invoiceFormData.value.phone = $event),
        M: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.phone
        }),
        N: common_vendor.s(invoiceInputStyle.value),
        O: common_vendor.o(($event) => invoiceFormData.value.bank_name = $event),
        P: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.bank_name
        }),
        Q: common_vendor.s(invoiceInputStyle.value),
        R: common_vendor.o(($event) => invoiceFormData.value.bank_account = $event),
        S: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.bank_account
        }),
        T: common_vendor.s(invoiceInputStyle.value),
        U: common_vendor.o(($event) => invoiceFormData.value.email = $event),
        V: common_vendor.p({
          clearable: false,
          modelValue: invoiceFormData.value.email
        }),
        W: common_vendor.o(submitApplyInvoice),
        X: common_vendor.p({
          title: "确定提交"
        }),
        Y: common_vendor.sr(applyInvoiceFormPopup, "456ecf67-7", {
          "k": "applyInvoiceFormPopup"
        }),
        Z: common_vendor.p({
          ["background-color"]: "#fff"
        })
      };
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-456ecf67"]]);
wx.createPage(MiniProgramPage);
