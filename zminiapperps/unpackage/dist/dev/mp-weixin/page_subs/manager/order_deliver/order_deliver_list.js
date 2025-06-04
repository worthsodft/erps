"use strict";
const common_vendor = require("../../../common/vendor.js");
if (!Array) {
  const _easycom_uni_segmented_control2 = common_vendor.resolveComponent("uni-segmented-control");
  const _easycom_uni_section2 = common_vendor.resolveComponent("uni-section");
  const _easycom_uni_datetime_picker2 = common_vendor.resolveComponent("uni-datetime-picker");
  const _easycom_uni_data_checkbox2 = common_vendor.resolveComponent("uni-data-checkbox");
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  (_easycom_uni_segmented_control2 + _easycom_uni_section2 + _easycom_uni_datetime_picker2 + _easycom_uni_data_checkbox2 + _easycom_uni_load_more2)();
}
const _easycom_uni_segmented_control = () => "../../../uni_modules/uni-segmented-control/components/uni-segmented-control/uni-segmented-control.js";
const _easycom_uni_section = () => "../../../uni_modules/uni-section/components/uni-section/uni-section.js";
const _easycom_uni_datetime_picker = () => "../../../uni_modules/uni-datetime-picker/components/uni-datetime-picker/uni-datetime-picker.js";
const _easycom_uni_data_checkbox = () => "../../../uni_modules/uni-data-checkbox/components/uni-data-checkbox/uni-data-checkbox.js";
const _easycom_uni_load_more = () => "../../../uni_modules/uni-load-more/components/uni-load-more/uni-load-more.js";
if (!Math) {
  (_easycom_uni_segmented_control + _easycom_uni_section + _easycom_uni_datetime_picker + _easycom_uni_data_checkbox + vOrderItem2Show + _easycom_uni_load_more + vGoodsList)();
}
const vOrderItem2Show = () => "../components/v-order-item2-show/v-order-item2-show.js";
const vGoodsList = () => "../components/v-goods-list/v-goods-list.js";
const _sfc_main = {
  __name: "order_deliver_list",
  setup(__props) {
    const current = common_vendor.ref(0);
    const dateTitle = common_vendor.ref("创建时间");
    const district = common_vendor.ref([]);
    const districts = common_vendor.ref([]);
    let today = common_vendor.index.$tool.today();
    console.log(today);
    let lastday = common_vendor.index.$tool.oneMonthAgo();
    const takeAtRange = common_vendor.ref([lastday + " 00:00:00", today + " 23:59:59"]);
    const loadStatus = common_vendor.ref("more");
    const list = common_vendor.ref([]);
    common_vendor.ref({});
    const total = common_vendor.ref(0);
    let page = 1;
    const goodsList = common_vendor.ref({});
    const goodsTotal = common_vendor.ref(0);
    const goodsListRef = common_vendor.ref();
    const stationList = common_vendor.ref([]);
    getUserInfoDistricts();
    getList();
    getStationOpeningList();
    function pick(station_gid) {
      let sns = [];
      list.value.forEach((item) => {
        if (item.checked)
          sns.push(item.sn);
      });
      if (!station_gid)
        return common_vendor.index.$tool.tip("请选择水站信息");
      common_vendor.index.$http.post("v1/orderPick", { sns, station_gid }).then((res) => {
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        common_vendor.index.$tool.tip(res.info || "操作成功");
        reset();
        getList("reset");
        goodsListRef.value.close();
      });
    }
    function showGoodsListPopup() {
      if (!goodsList.value || Object.keys(goodsList.value).length == 0)
        return common_vendor.index.$tool.tip("请选择配货商品");
      goodsListRef.value.open();
    }
    function selectItem(index) {
      let order = list.value[index];
      order.checked = !order.checked;
      if (order.checked) {
        for (let i in order.subs) {
          let sub = order.subs[i];
          if (goodsList.value[sub.goods_sn])
            goodsList.value[sub.goods_sn].goods_number += sub.goods_number;
          else
            goodsList.value[sub.goods_sn] = {
              goods_sn: sub.goods_sn,
              goods_cover: sub.goods_cover,
              goods_name: sub.goods_name,
              goods_number: sub.goods_number,
              goods_unit: sub.goods_unit
            };
          goodsTotal.value += sub.goods_number;
        }
        common_vendor.index.$tool.tip("配货商品已添加");
      } else {
        for (let i in order.subs) {
          let sub = order.subs[i];
          let number = goodsList.value[sub.goods_sn].goods_number - sub.goods_number;
          if (number <= 0)
            delete goodsList.value[sub.goods_sn];
          else
            goodsList.value[sub.goods_sn].goods_number = number;
          goodsTotal.value -= sub.goods_number;
        }
        common_vendor.index.$tool.tip("配货商品已去除");
      }
      list.value[index] = order;
    }
    function currentChange({ currentIndex }) {
      current.value = currentIndex;
      if (currentIndex == 2)
        dateTitle.value = "配送时间";
      else
        dateTitle.value = "创建时间";
      goodsList.value = {};
      goodsTotal.value = 0;
      reset();
      list.value = [];
      getList("reset");
    }
    function takeAtRangeChange(e) {
      if (e.length == 0) {
        takeAtRange.value = [lastday + " 00:00:00", today + " 23:59:59"];
      } else {
        takeAtRange.value = e;
      }
      reset();
      getList("reset");
    }
    function districtChange({ detail }) {
      reset();
      getList("reset");
    }
    function reset() {
      loadStatus.value = "more";
      page = 1;
    }
    function getStationOpeningList() {
      common_vendor.index.$http.post("v1/getStationOpeningList").then((res) => {
        stationList.value = res.data.stationList;
      });
    }
    function getList(type) {
      if (loadStatus.value != "more")
        return;
      loadStatus.value = "loading";
      let params = {
        page,
        takeAtRange: takeAtRange.value,
        current: current.value,
        district: district.value
      };
      common_vendor.index.$http.post("v1/getMyDeliverOrderPageData", params).then((res) => {
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        let pageData = res.data.pageData;
        total.value = pageData.total;
        if (pageData.total == 0) {
          loadStatus.value = "nomore";
          list.value = [];
          goodsTotal.value = 0;
          return;
        }
        if (pageData.data.length == 0) {
          loadStatus.value = "nomore";
          return;
        }
        if (type == "reset")
          list.value = [...pageData.data];
        else
          list.value = [...list.value, ...pageData.data];
        if (list.value.length >= total.value) {
          loadStatus.value = "nomore";
        } else {
          loadStatus.value = "more";
          page++;
        }
      });
    }
    function getUserInfoDistricts() {
      common_vendor.index.$http.post("v1/getUserInfoDistricts").then((res) => {
        districts.value = res.data.districts;
      });
    }
    common_vendor.onReachBottom(() => {
      getList();
    });
    common_vendor.onPullDownRefresh(() => {
      reset();
      getList("reset");
      setTimeout(() => {
        common_vendor.index.stopPullDownRefresh();
      }, 200);
    });
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: common_vendor.o(currentChange),
        b: common_vendor.p({
          activeColor: "#6abfff",
          current: current.value,
          values: ["待配货", "配送中", "已配送"],
          styleType: "button"
        }),
        c: common_vendor.p({
          title: dateTitle.value + "：",
          type: "line"
        }),
        d: common_vendor.o(takeAtRangeChange),
        e: common_vendor.o(($event) => takeAtRange.value = $event),
        f: common_vendor.p({
          type: "datetimerange",
          rangeSeparator: "至",
          modelValue: takeAtRange.value
        }),
        g: common_vendor.p({
          title: "配送区域：",
          type: "line"
        }),
        h: common_vendor.o(districtChange),
        i: common_vendor.o(($event) => district.value = $event),
        j: common_vendor.p({
          multiple: true,
          localdata: districts.value,
          modelValue: district.value
        }),
        k: common_vendor.p({
          title: "总订单数：" + total.value,
          type: "line"
        }),
        l: current.value == 0
      }, current.value == 0 ? {
        m: common_vendor.o(showGoodsListPopup),
        n: common_vendor.p({
          title: "配货商品总件数：" + goodsTotal.value + " 件",
          type: "line"
        })
      } : {}, {
        o: common_vendor.f(list.value, (vo, index, i0) => {
          return {
            a: common_vendor.o(selectItem, vo.id),
            b: "c13edc82-7-" + i0,
            c: common_vendor.p({
              item: vo,
              index
            }),
            d: vo.id
          };
        }),
        p: common_vendor.o(getList),
        q: common_vendor.p({
          status: loadStatus.value
        }),
        r: common_vendor.sr(goodsListRef, "c13edc82-9", {
          "k": "goodsListRef"
        }),
        s: common_vendor.o(pick),
        t: common_vendor.p({
          list: goodsList.value,
          stationList: stationList.value
        })
      });
    };
  }
};
const MiniProgramPage = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-c13edc82"]]);
wx.createPage(MiniProgramPage);
