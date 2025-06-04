"use strict";
const extend_luchRequest_adapters_index = require("../adapters/index.js");
const dispatchRequest = (config) => {
  return extend_luchRequest_adapters_index.adapter(config);
};
exports.dispatchRequest = dispatchRequest;
