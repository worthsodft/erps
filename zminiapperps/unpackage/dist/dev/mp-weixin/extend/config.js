"use strict";
const shortdomain = "erpp.yaodianma.com";
const version = "1.0.19";
const config = {
  version,
  shortdomain,
  domain: "https://" + shortdomain,
  wssDomain: "wss://" + shortdomain,
  defaultAvatar: "https://" + shortdomain + "/upload/images/avatar.png"
};
exports.config = config;
