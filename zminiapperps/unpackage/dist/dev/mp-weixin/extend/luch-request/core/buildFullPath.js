"use strict";
const extend_luchRequest_helpers_isAbsoluteURL = require("../helpers/isAbsoluteURL.js");
const extend_luchRequest_helpers_combineURLs = require("../helpers/combineURLs.js");
function buildFullPath(baseURL, requestedURL) {
  if (baseURL && !extend_luchRequest_helpers_isAbsoluteURL.isAbsoluteURL(requestedURL)) {
    return extend_luchRequest_helpers_combineURLs.combineURLs(baseURL, requestedURL);
  }
  return requestedURL;
}
exports.buildFullPath = buildFullPath;
