"use strict";
const common_vendor = require("../common/vendor.js");
const extend_tool = require("./tool.js");
const api = {
  /**
   * 添加到购物车
   * let params = {sn, number}
   */
  add2cart(params, cb) {
    common_vendor.index.$http.post("v1/add2cart", params).then((res) => {
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.setCartBadge(res.data.total);
      cb && cb(res);
    });
  },
  /**
   * 更新到购物车
   * let params = {sn, number}
   */
  update2cart(params, cb) {
    common_vendor.index.$http.post("v1/update2cart", params).then((res) => {
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.setCartBadge(res.data.total);
      cb && cb(res);
    });
  },
  /**
   * 删除到购物车
   * let params = {sn, number}
   */
  del2cart: extend_tool.tool.debounce((params, cb) => {
    common_vendor.index.$http.post("v1/del2cart", params).then((res) => {
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.setCartBadge(res.data.total);
      cb && cb(res);
    });
  }),
  /**
   * 选择购物车
   * let params = {sn, is_checked}
   */
  check2cart: extend_tool.tool.debounce((params, cb) => {
    common_vendor.index.$http.post("v1/check2cart", params).then((res) => {
      if (res.code != 1)
        return common_vendor.index.$tool.tip(res.info || "系统错误");
      common_vendor.index.$tool.setCartBadge(res.data.total);
      cb && cb(res);
    });
  }),
  /**
   * 获取购物车数量
   */
  getCartTotal(cb) {
    common_vendor.index.$http.get("v1/getCartTotal").then((res) => {
      common_vendor.index.$tool.setCartBadge(res.data.total);
      cb && cb(res);
    });
  },
  /**
   * 得到用户信息
   * @param {Object} cb
   */
  getUserInfo(cb) {
    common_vendor.index.$http.get("v1/getUserInfo").then((res) => {
      cb && cb(res);
    });
  },
  /**
   * 支付预下单
   */
  getPayInfo(type, gid) {
    return common_vendor.index.$http.post("v1/getPayInfo", { type, gid });
  },
  /**
   * 得到应用配置
   */
  getAppConfig() {
    return common_vendor.index.$http.get("v1/getAppConfig");
  }
};
exports.api = api;
