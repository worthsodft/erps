"use strict";
const extend_luchRequest_utils = require("../utils.js");
const mergeKeys = (keys, globalsConfig, config2) => {
  let config = {};
  keys.forEach((prop) => {
    if (!extend_luchRequest_utils.isUndefined(config2[prop])) {
      config[prop] = config2[prop];
    } else if (!extend_luchRequest_utils.isUndefined(globalsConfig[prop])) {
      config[prop] = globalsConfig[prop];
    }
  });
  return config;
};
const mergeConfig = (globalsConfig, config2 = {}) => {
  const method = config2.method || globalsConfig.method || "GET";
  let config = {
    baseURL: config2.baseURL || globalsConfig.baseURL || "",
    method,
    url: config2.url || "",
    params: config2.params || {},
    custom: { ...globalsConfig.custom || {}, ...config2.custom || {} },
    header: extend_luchRequest_utils.deepMerge(globalsConfig.header || {}, config2.header || {})
  };
  const defaultToConfig2Keys = ["getTask", "validateStatus", "paramsSerializer", "forcedJSONParsing"];
  config = { ...config, ...mergeKeys(defaultToConfig2Keys, globalsConfig, config2) };
  if (method === "DOWNLOAD") {
    const downloadKeys = [
      "timeout",
      "filePath"
    ];
    config = { ...config, ...mergeKeys(downloadKeys, globalsConfig, config2) };
  } else if (method === "UPLOAD") {
    delete config.header["content-type"];
    delete config.header["Content-Type"];
    const uploadKeys = [
      "filePath",
      "name",
      "timeout",
      "formData"
    ];
    uploadKeys.forEach((prop) => {
      if (!extend_luchRequest_utils.isUndefined(config2[prop])) {
        config[prop] = config2[prop];
      }
    });
    if (extend_luchRequest_utils.isUndefined(config.timeout) && !extend_luchRequest_utils.isUndefined(globalsConfig.timeout)) {
      config["timeout"] = globalsConfig["timeout"];
    }
  } else {
    const defaultsKeys = [
      "data",
      "timeout",
      "dataType",
      "responseType",
      "enableHttp2",
      "enableQuic",
      "enableCache",
      "enableHttpDNS",
      "httpDNSServiceId",
      "enableChunked",
      "forceCellularNetwork"
    ];
    config = { ...config, ...mergeKeys(defaultsKeys, globalsConfig, config2) };
  }
  return config;
};
exports.mergeConfig = mergeConfig;
