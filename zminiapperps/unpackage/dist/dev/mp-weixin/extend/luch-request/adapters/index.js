"use strict";
const common_vendor = require("../../../common/vendor.js");
const extend_luchRequest_helpers_buildURL = require("../helpers/buildURL.js");
const extend_luchRequest_core_buildFullPath = require("../core/buildFullPath.js");
const extend_luchRequest_core_settle = require("../core/settle.js");
const extend_luchRequest_utils = require("../utils.js");
const mergeKeys = (keys, config2) => {
  let config = {};
  keys.forEach((prop) => {
    if (!extend_luchRequest_utils.isUndefined(config2[prop])) {
      config[prop] = config2[prop];
    }
  });
  return config;
};
const adapter = (config) => {
  return new Promise((resolve, reject) => {
    let fullPath = extend_luchRequest_helpers_buildURL.buildURL(extend_luchRequest_core_buildFullPath.buildFullPath(config.baseURL, config.url), config.params, config.paramsSerializer);
    const _config = {
      url: fullPath,
      header: config.header,
      complete: (response) => {
        config.fullPath = fullPath;
        response.config = config;
        response.rawData = response.data;
        try {
          let jsonParseHandle = false;
          const forcedJSONParsingType = typeof config.forcedJSONParsing;
          if (forcedJSONParsingType === "boolean") {
            jsonParseHandle = config.forcedJSONParsing;
          } else if (forcedJSONParsingType === "object") {
            const includesMethod = config.forcedJSONParsing.include || [];
            jsonParseHandle = includesMethod.includes(config.method);
          }
          if (jsonParseHandle && typeof response.data === "string") {
            response.data = JSON.parse(response.data);
          }
        } catch (e) {
        }
        extend_luchRequest_core_settle.settle(resolve, reject, response);
      }
    };
    let requestTask;
    if (config.method === "UPLOAD") {
      delete _config.header["content-type"];
      delete _config.header["Content-Type"];
      let otherConfig = {
        filePath: config.filePath,
        name: config.name
      };
      const optionalKeys = [
        "timeout",
        "formData"
      ];
      requestTask = common_vendor.index.uploadFile({ ..._config, ...otherConfig, ...mergeKeys(optionalKeys, config) });
    } else if (config.method === "DOWNLOAD") {
      const optionalKeys = [
        "timeout",
        "filePath"
      ];
      requestTask = common_vendor.index.downloadFile({ ..._config, ...mergeKeys(optionalKeys, config) });
    } else {
      const optionalKeys = [
        "data",
        "method",
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
      requestTask = common_vendor.index.request({ ..._config, ...mergeKeys(optionalKeys, config) });
    }
    if (config.getTask) {
      config.getTask(requestTask, config);
    }
  });
};
exports.adapter = adapter;
