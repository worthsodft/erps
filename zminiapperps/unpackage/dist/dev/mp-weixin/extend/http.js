"use strict";
const common_vendor = require("../common/vendor.js");
const extend_config = require("./config.js");
const extend_login = require("./login.js");
const extend_luchRequest_core_Request = require("./luch-request/core/Request.js");
const http = new extend_luchRequest_core_Request.Request();
http.setConfig((config_) => {
  config_.baseURL = extend_config.config.domain;
  return config_;
});
http.interceptors.request.use((config) => {
  config.data = config.data || {};
  config.header.token = common_vendor.index.$tool.cache("token");
  return config;
}, (config) => {
  return Promise.reject(config);
});
http.interceptors.response.use(async (response) => {
  const data = response.data;
  if (data.code == 401) {
    console.log("token过期，重调");
    await extend_login.login.do();
    response.config.header.token = common_vendor.index.$tool.cache("token");
    return http.request(response.config);
  } else if (data.code == 1) {
    return Promise.resolve(data);
  } else {
    common_vendor.index.$tool.tip(data.info || "系统错误", false, null, 3);
    return Promise.reject(data);
  }
}, (response) => {
  return Promise.reject(response);
});
const doRequest = (url, data = {}, auth = true, method = "get") => {
  method = method.toUpperCase();
  let config = {
    method,
    url
  };
  if (method == "GET") {
    config.params = data;
  } else {
    config.data = data;
  }
  return http.request(config);
};
const doUpload = (url, data = {}, auth = true) => {
  let config = {
    ...data
  };
  return http.upload(url, config);
};
const http$1 = {
  request(url, params, auth, method = "get") {
    return doRequest(url, params, auth, method);
  },
  get(url, params = {}, auth = true) {
    url = "/api/" + url;
    return this.request(url, params, auth, "get");
  },
  post(url, params = {}, auth = true) {
    url = "/api/" + url;
    return this.request(url, params, auth, "post");
  },
  upload(url, params = {}, auth = true) {
    url = "/api/" + url;
    return doUpload(url, params, auth);
  },
  addon: {
    get(url, params = {}, auth = true) {
      url = "/addon" + url;
      return doRequest(url, params, auth, "get");
    },
    post(url, params = {}, auth = true) {
      url = "/addon" + url;
      return doRequest(url, params, auth, "post");
    },
    upload(url, params = {}, auth = true) {
      url = "/addon" + url;
      return doUpload(url, params, auth);
    }
  }
};
exports.http = http$1;
