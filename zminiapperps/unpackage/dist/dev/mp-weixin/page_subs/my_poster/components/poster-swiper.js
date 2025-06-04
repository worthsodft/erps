"use strict";
const common_vendor = require("../../../common/vendor.js");
const _sfc_main = {
  props: {
    // 轮播图
    imgList: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      currentIndex: 0,
      // 当前显示图片
      previous_next: "80rpx"
      // 前后边距
    };
  },
  methods: {
    swiperTab(e) {
      this.currentIndex = e.detail.current;
      this.$emit("change", this.currentIndex);
    },
    preview() {
      common_vendor.index.previewImage({
        current: this.currentIndex,
        urls: this.imgList
      });
    }
  }
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return {
    a: common_vendor.f($props.imgList, (item, index, i0) => {
      return {
        a: item,
        b: common_vendor.o((...args) => $options.preview && $options.preview(...args), index),
        c: common_vendor.n($data.currentIndex === index ? "swiperItemActive" : ""),
        d: index
      };
    }),
    b: $data.currentIndex,
    c: common_vendor.o((...args) => $options.swiperTab && $options.swiperTab(...args)),
    d: $data.previous_next,
    e: $data.previous_next
  };
}
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["render", _sfc_render], ["__scopeId", "data-v-81304117"]]);
wx.createComponent(Component);
