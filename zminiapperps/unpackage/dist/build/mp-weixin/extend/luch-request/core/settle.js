"use strict";exports.settle=function(t,s,e){const o=e.config.validateStatus,a=e.statusCode;!a||o&&!o(a)?s(e):t(e)};
