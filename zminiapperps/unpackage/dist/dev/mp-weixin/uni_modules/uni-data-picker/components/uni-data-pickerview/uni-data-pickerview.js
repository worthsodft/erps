"use strict";
const uni_modules_uniDataPicker_components_uniDataPickerview_uniDataPicker = require("./uni-data-picker.js");
const common_vendor = require("../../../../common/vendor.js");
const _sfc_main = {
  name: "UniDataPickerView",
  emits: ["nodeclick", "change", "datachange", "update:modelValue"],
  mixins: [uni_modules_uniDataPicker_components_uniDataPickerview_uniDataPicker.dataPicker],
  props: {
    managedMode: {
      type: Boolean,
      default: false
    },
    ellipsis: {
      type: Boolean,
      default: true
    }
  },
  created() {
    if (!this.managedMode) {
      this.$nextTick(() => {
        this.loadData();
      });
    }
  },
  methods: {
    onPropsChange() {
      this._treeData = [];
      this.selectedIndex = 0;
      this.$nextTick(() => {
        this.loadData();
      });
    },
    handleSelect(index) {
      this.selectedIndex = index;
    },
    handleNodeClick(item, i, j) {
      if (item.disable) {
        return;
      }
      const node = this.dataList[i][j];
      const text = node[this.map.text];
      const value = node[this.map.value];
      if (i < this.selected.length - 1) {
        this.selected.splice(i, this.selected.length - i);
        this.selected.push({
          text,
          value
        });
      } else if (i === this.selected.length - 1) {
        this.selected.splice(i, 1, {
          text,
          value
        });
      }
      if (node.isleaf) {
        this.onSelectedChange(node, node.isleaf);
        return;
      }
      const {
        isleaf,
        hasNodes
      } = this._updateBindData();
      if (this.isLocalData) {
        this.onSelectedChange(node, !hasNodes || isleaf);
      } else if (this.isCloudDataList) {
        this.onSelectedChange(node, true);
      } else if (this.isCloudDataTree) {
        if (isleaf) {
          this.onSelectedChange(node, node.isleaf);
        } else if (!hasNodes) {
          this.loadCloudDataNode((data) => {
            if (!data.length) {
              node.isleaf = true;
            } else {
              this._treeData.push(...data);
              this._updateBindData(node);
            }
            this.onSelectedChange(node, node.isleaf);
          });
        }
      }
    },
    updateData(data) {
      this._treeData = data.treeData;
      this.selected = data.selected;
      if (!this._treeData.length) {
        this.loadData();
      } else {
        this._updateBindData();
      }
    },
    onDataChange() {
      this.$emit("datachange");
    },
    onSelectedChange(node, isleaf) {
      if (isleaf) {
        this._dispatchEvent();
      }
      if (node) {
        this.$emit("nodeclick", node);
      }
    },
    _dispatchEvent() {
      this.$emit("change", this.selected.slice(0));
    }
  }
};
if (!Array) {
  const _easycom_uni_load_more2 = common_vendor.resolveComponent("uni-load-more");
  _easycom_uni_load_more2();
}
const _easycom_uni_load_more = () => "../../../uni-load-more/components/uni-load-more/uni-load-more.js";
if (!Math) {
  _easycom_uni_load_more();
}
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return common_vendor.e({
    a: !_ctx.isCloudDataList
  }, !_ctx.isCloudDataList ? {
    b: common_vendor.f(_ctx.selected, (item, index, i0) => {
      return {
        a: common_vendor.t(item.text || ""),
        b: index,
        c: index == _ctx.selectedIndex ? 1 : "",
        d: common_vendor.o(($event) => $options.handleSelect(index), index)
      };
    })
  } : {}, {
    c: common_vendor.f(_ctx.dataList[_ctx.selectedIndex], (item, j, i0) => {
      return common_vendor.e({
        a: common_vendor.t(item[_ctx.map.text]),
        b: _ctx.selected.length > _ctx.selectedIndex && item[_ctx.map.value] == _ctx.selected[_ctx.selectedIndex].value
      }, _ctx.selected.length > _ctx.selectedIndex && item[_ctx.map.value] == _ctx.selected[_ctx.selectedIndex].value ? {} : {}, {
        c: !!item.disable ? 1 : "",
        d: j,
        e: common_vendor.o(($event) => $options.handleNodeClick(item, _ctx.selectedIndex, j), j)
      });
    }),
    d: _ctx.loading
  }, _ctx.loading ? {
    e: common_vendor.p({
      contentText: _ctx.loadMore,
      status: "loading"
    })
  } : {}, {
    f: _ctx.errorMessage
  }, _ctx.errorMessage ? {
    g: common_vendor.t(_ctx.errorMessage)
  } : {});
}
const Component = /* @__PURE__ */ common_vendor._export_sfc(_sfc_main, [["render", _sfc_render]]);
wx.createComponent(Component);
