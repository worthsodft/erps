"use strict";
const common_vendor = require("../../common/vendor.js");
const _sfc_main = {
  __name: "v-coupon-item",
  props: {
    item: Object,
    type: {
      type: String,
      default: "fetch"
    },
    mb: {
      type: Boolean,
      default: false
    },
    // 是否宽度占100%，用于竖向列表，false: 固定宽度400rpx，用于首页横向列表
    fullWidth: {
      type: Boolean,
      default: false
    }
  },
  emits: ["select"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const status = common_vendor.ref(props.type == "my" ? 1 : 0);
    common_vendor.watchEffect(() => {
      setCouponStatus();
    });
    function setCouponStatus() {
      if (props.type == "select")
        status.value = 1;
      else if (props.type != "my")
        status.value = props.item.has_count > 0 ? 1 : 0;
    }
    const fetch = common_vendor.index.$tool.throttle(() => {
      common_vendor.index.$login.judgePhone(() => {
        doFetch();
      });
    });
    const doFetch = () => {
      common_vendor.index.$http.post("v1/fetchCoupon/" + props.item.gid).then((res) => {
        if (res.code != 1)
          return common_vendor.index.$tool.tip(res.info || "系统错误");
        common_vendor.index.$tool.tip(res.info || "操作成功");
      });
    };
    function use() {
      common_vendor.index.$tool.navto("/pages/goods/list");
    }
    function select() {
      emit("select", props.item);
    }
    return (_ctx, _cache) => {
      return common_vendor.e({
        a: __props.item.money > 0
      }, __props.item.money > 0 ? {
        b: common_vendor.t(__props.item.money)
      } : {
        c: common_vendor.t(__props.item.discount)
      }, {
        d: common_vendor.t(__props.item.title),
        e: __props.item.expire_at
      }, __props.item.expire_at ? {
        f: common_vendor.t(__props.item.expire_at)
      } : {
        g: common_vendor.t(__props.item.expire_days)
      }, {
        h: __props.item.min_use_money == 0
      }, __props.item.min_use_money == 0 ? {} : {
        i: common_vendor.t(__props.item.min_use_money)
      }, {
        j: __props.type == "fetch"
      }, __props.type == "fetch" ? {
        k: common_vendor.t(status.value == 0 ? "已 领 完" : "立即领取"),
        l: common_vendor.n("usenow-color-" + status.value),
        m: common_vendor.o((...args) => common_vendor.unref(fetch) && common_vendor.unref(fetch)(...args))
      } : __props.type == "my" ? {
        o: common_vendor.n("usenow-color-1"),
        p: common_vendor.o(use)
      } : __props.type == "select" ? {
        r: common_vendor.n("usenow-color-1"),
        s: common_vendor.o(select)
      } : {}, {
        n: __props.type == "my",
        q: __props.type == "select",
        t: __props.fullWidth ? 1 : "",
        v: common_vendor.n("jb-" + status.value),
        w: __props.mb ? 1 : "",
        x: common_vendor.s(__props.fullWidth ? "width: 100%;" : "width: 400rpx;")
      });
    };
  }
};
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["__scopeId", "data-v-b53538ff"]]);
wx.createComponent(Component);
