"use strict";
const common_vendor = require("../common/vendor.js");
const extend_config = require("./config.js");
const tool = {
  back(delta) {
    common_vendor.index.navigateBack({ delta: delta || 1 });
  },
  navto(url, cb) {
    common_vendor.index.navigateTo({
      url: url || "/pages/index/index",
      fail(e) {
        common_vendor.index.switchTab({ url, fail(err) {
          console.log(err);
          cb && cb(err);
        } });
      }
    });
  },
  redirect(url) {
    common_vendor.index.redirectTo({ url: url || "/pages/index/index", fail(err) {
      console.log(err);
    } });
  },
  relaunch(url) {
    common_vendor.index.reLaunch({ url: url || "/pages/index/index", fail(err) {
      console.log(err);
    } });
  },
  index() {
    this.relaunch("/pages/index/index");
  },
  showLoading(title = "加载中", mask = true) {
    common_vendor.index.showLoading({ title, mask });
  },
  hideLoading() {
    common_vendor.index.hideLoading();
  },
  tip(content, isMask = false, cb, duration = 1) {
    duration *= 1e3;
    common_vendor.index.showToast({
      title: content || "提示内容",
      icon: "none",
      mask: isMask,
      duration,
      success() {
        if (duration > 0) {
          setTimeout(() => {
            cb && cb();
          }, duration);
        } else {
          cb && cb();
        }
      }
    });
  },
  success(content, isMask = false, cb, duration = 1) {
    duration *= 1e3;
    common_vendor.index.showToast({
      title: content || "提示内容",
      icon: "success",
      mask: isMask,
      duration,
      success() {
        if (duration > 0) {
          setTimeout(() => {
            cb && cb();
          }, duration);
        } else {
          cb && cb();
        }
      }
    });
  },
  error(content, isMask = false, cb, duration = 1) {
    duration *= 1e3;
    common_vendor.index.showToast({
      title: content || "提示内容",
      icon: "error",
      mask: isMask,
      duration,
      success() {
        if (duration > 0) {
          setTimeout(() => {
            cb && cb();
          }, duration);
        } else {
          cb && cb();
        }
      }
    });
  },
  alert(content, cb) {
    common_vendor.index.showModal({
      title: "提示",
      content: content || "提示内容",
      showCancel: false,
      success() {
        cb && cb();
      }
    });
  },
  confirm(content, cb0, cb1, opts = {}) {
    common_vendor.index.showModal({
      title: "确认",
      content: content || "确认内容",
      cancelText: opts.cancelText || "取消",
      confirmText: opts.confirmText || "确定",
      success(res) {
        if (res.confirm)
          cb0 && cb0();
        else
          cb1 && cb1();
      }
    });
  },
  cache(key, value = null) {
    if (value === null) {
      let data = common_vendor.index.getStorageSync(key);
      return data;
    } else
      return common_vendor.index.setStorageSync(key, value);
  },
  removeCache(key) {
    return common_vendor.index.removeStorageSync(key);
  },
  clearCache(key) {
    return common_vendor.index.clearStorageSync();
  },
  setNavTitle(title) {
    common_vendor.index.setNavigationBarTitle({
      title
    });
  },
  /**
   * 去抖 将多次执行变为最后一次执行
   * @desc 函数防抖---“立即执行版本” 和 “非立即执行版本” 的组合版本
   * @param func 需要执行的函数
   * @param wait 延迟执行时间（毫秒）
   * @param immediate---true 表立即执行，false 表非立即执行
   **/
  debounce(func, wait = 1e3, immediate = true) {
    let timer;
    return function() {
      let context = this;
      let args = arguments;
      if (timer)
        clearTimeout(timer);
      if (immediate) {
        let callNow = !timer;
        timer = setTimeout(() => {
          timer = null;
        }, wait);
        if (callNow)
          func.apply(context, args);
      } else {
        timer = setTimeout(function() {
          func.apply(context, args);
        }, wait);
      }
    };
  },
  /**
   * 节流 将多次执行变为每隔一段时间执行
   * 当持续触发事件时，保证在一定时间内只调用一次事件处理函数
   */
  throttle(func, wait = 1e3) {
    let prev = Date.now();
    return function() {
      let now = Date.now();
      let that = this;
      if (now - prev >= wait) {
        func.apply(that, arguments);
        prev = Date.now();
      }
    };
  },
  // 得到当前页
  getCurrPage() {
    let pages = getCurrentPages();
    let length = pages.length;
    if (length < 1)
      return null;
    return pages[pages.length - 1];
  },
  // 得到上一页
  getPrevPage() {
    let pages = getCurrentPages();
    let length = pages.length;
    if (length <= 1)
      return null;
    return pages[length - 2];
  },
  sec2str(sec) {
    let middle = 0;
    let hour = 0;
    if (sec > 59) {
      middle = parseInt(sec / 60);
      sec = parseInt(sec % 60);
    }
    if (middle > 59) {
      hour = parseInt(middle / 60);
      middle = parseInt(middle % 60);
    }
    sec < 10 ? sec = "0" + sec : sec = sec;
    middle < 10 ? middle = "0" + middle : middle = middle;
    hour < 10 ? hour = "0" + hour : hour = hour;
    return hour + ":" + middle + ":" + sec;
  },
  secondsToHms(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor(seconds % 3600 / 60);
    const s = Math.floor(seconds % 60);
    const hDisplay = h > 0 ? (h < 10 ? "0" + h : h) + "时" : "";
    const mDisplay = m > 0 ? (m < 10 ? "0" + m : m) + "分" : "";
    const sDisplay = s > 0 ? (s < 10 ? "0" + s : s) + "秒" : "";
    return hDisplay + mDisplay + sDisplay;
  },
  isPhone(phone) {
    return /^1[3-9][0-9]{9}$/.test(phone);
  },
  isEmail(value) {
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
  },
  //判断是否是数字
  isNumber(val) {
    if (typeof val == "number")
      return true;
    var regPos = /^[0-9]+.?[0-9]*/;
    return regPos.test(val);
  },
  //判断是否是字母和数字
  isAlphaNum(val) {
    if (!val)
      return false;
    var regPos = /^[A-Za-z0-9]+$/;
    return regPos.test(val);
  },
  uuid() {
    var d = (/* @__PURE__ */ new Date()).getTime();
    var uuid = "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function(c) {
      var r = (d + Math.random() * 16) % 16 | 0;
      d = Math.floor(d / 16);
      return (c == "x" ? r : r & 3 | 8).toString(16);
    });
    return uuid;
  },
  format(timestamp) {
    let i = 1;
    if (timestamp <= 9999999999)
      i = 1e3;
    var date = new Date(timestamp * i);
    var Y = date.getFullYear() + "-";
    var M = (date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1) + "-";
    var D = (date.getDate() < 10 ? "0" + date.getDate() : date.getDate()) + " ";
    var h = (date.getHours() < 10 ? "0" + date.getHours() : date.getHours()) + ":";
    var m = (date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes()) + ":";
    var s = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
    return Y + M + D + h + m + s;
  },
  today() {
    var date = /* @__PURE__ */ new Date();
    var Y = date.getFullYear() + "-";
    var M = (date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1) + "-";
    var D = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
    return Y + M + D;
  },
  todaytime() {
    var date = /* @__PURE__ */ new Date();
    var Y = date.getFullYear() + "-";
    var M = (date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1) + "-";
    var D = (date.getDate() < 10 ? "0" + date.getDate() : date.getDate()) + " ";
    var h = (date.getHours() < 10 ? "0" + date.getHours() : date.getHours()) + ":";
    var m = (date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes()) + ":";
    var s = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
    return Y + M + D + h + m + s;
  },
  oneMonthAgo() {
    var date = /* @__PURE__ */ new Date();
    date.setMonth(date.getMonth() - 1);
    var Y = date.getFullYear() + "-";
    var M = (date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1) : date.getMonth() + 1) + "-";
    var D = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
    return Y + M + D;
  },
  /**
   * 得到url携带的参数，根据参数名称
   * @param {Object} name
   */
  getUrlParam(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    let regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
    let results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
  },
  /**
   * 给定路径，得到所有url的参数
   * @param {string} url
   */
  getUrlParams(url) {
    let urlStr = url.split("?")[1];
    let obj = {};
    let paramsArr = urlStr.split("&");
    for (let i = 0, len = paramsArr.length; i < len; i++) {
      let arr = paramsArr[i].split("=");
      obj[arr[0]] = arr[1];
    }
    return obj;
  },
  /**
   * 浮点保留2位小数
   */
  round2(num) {
    return Math.round(num * 100) / 100;
  },
  /**
   * 浮点保留2位小数
   */
  round3(num) {
    return Math.round(num * 1e3) / 1e3;
  },
  getTabBarHeight() {
    let height = this.cache("tabBarHeight");
    if (height)
      return height;
    const sysInfo = common_vendor.index.getSystemInfoSync();
    height = sysInfo.screenHeight - sysInfo.safeArea.bottom;
    return height;
  },
  /**
   * 添加角标到购物车菜单
   * @param {Object} number
   */
  setCartBadge(number) {
    if (number == 0) {
      common_vendor.index.hideTabBarRedDot({
        index: 2,
        fail(err) {
          console.log(err);
        }
      });
      return;
    }
    common_vendor.index.setTabBarBadge({
      index: 2,
      text: number + "",
      fail(err) {
        console.log(err);
      }
    });
  },
  /**
   * 对象的值与属性翻转
   * @param {Object} obj
   */
  invert(obj) {
    const invertedObj = Object.keys(obj).reduce((inverted, key) => {
      inverted[obj[key]] = key;
      return inverted;
    }, {});
    return invertedObj;
  },
  /**
   * 路径是否是远程路径（是否包含域名）
   * @param {Object} url
   */
  isRemoteUrl(url) {
    return url.indexOf("https") === 0 && url.indexOf(extend_config.config.shortdomain) !== -1;
  },
  payment(payInfo, cb, cb2) {
    common_vendor.index.requestPayment({
      ...payInfo,
      success(res) {
        common_vendor.index.$tool.tip("已支付", true, () => {
          cb && cb();
        });
      },
      fail(err) {
        console.log(err);
        cb2 && cb2();
      }
    });
  },
  /**
   * 检查小程序更新
   */
  checkUpdateApp(isSilence = true) {
    const that = this;
    const updateManager = common_vendor.index.getUpdateManager();
    !isSilence && that.showLoading("检查中");
    updateManager.onCheckForUpdate(function(res) {
      !isSilence && that.hideLoading();
      if (res.hasUpdate) {
        updateManager.onUpdateReady(function(res2) {
          common_vendor.index.showModal({
            title: "更新提示",
            content: "新版本已经准备好，是否重启应用？",
            success(res3) {
              console.log(res3);
              if (res3.confirm)
                updateManager.applyUpdate();
            }
          });
        });
        updateManager.onUpdateFailed(function(res2) {
          console.log("新的版本下载失败");
        });
      } else {
        !isSilence && that.tip("当前已是最新版");
      }
    });
  }
};
exports.tool = tool;
