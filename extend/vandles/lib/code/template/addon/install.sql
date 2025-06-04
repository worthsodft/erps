CREATE TABLE IF NOT EXISTS `a_user_poster`
(
    `id`        bigint unsigned     NOT NULL AUTO_INCREMENT comment '{"title":"ID","type":"number"}',
    `openid`    varchar(50)         NOT NULL comment '{"title":"OPENID"}',
    `uid`       bigint              not null default 0 comment '{"title":"用户UID"}',
    `pic`       varchar(500)        null comment '{"title":"海报路径","type":"pic"}',
    `tpl_id`    bigint(20) UNSIGNED NULL     DEFAULT 0 COMMENT '{"title":"模板ID","type":"number"}',

    `status`    tinyint(1)          not null default 1 comment '{"title":"状态","type":"select","opts":["已隐藏","已显示"]}',
    `sort`      bigint(20) unsigned null     default 0 comment '{"title":"排序权重","type":"number"}',

    `create_at` timestamp           not null default current_timestamp comment '{"title":"创建时间","type":"datetime"}',
    `update_at` datetime                     default null on update current_timestamp comment '{"title":"更新时间","type":"datetime"}',
    `deleted_time`   int unsigned not null default 0 comment '{"title":"删除时间","type":"number","opts":["未删除","已删除"]}',
    primary key (`id`) using btree,
    index `idx_a_user_poster4` (`openid`) using btree,
    index `idx_a_user_poster5` (`uid`) using btree,
    INDEX `idx_a_user_qrcode6` (`tpl_id`) USING BTREE,

    index `idx_a_user_poster1` (`sort`) using btree,
    index `idx_a_user_poster2` (`status`) using btree,
    index `idx_a_user_poster3` (`deleted_time`) using btree
) engine = InnoDB
  character set = utf8mb4
  collate = utf8mb4_unicode_ci comment = '用户海报';

CREATE TABLE if not exists `a_user_qrcode`
(
    `id`        bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '{"title":"ID","type":"number"}',
    `openid`    varchar(50)         NOT NULL COMMENT '{"title":"OPENID"}',
    `qr_code`   varchar(200)        NOT NULL COMMENT '{"title":"二维码地址","type":"pic"}',
    `remark`    varchar(200)        NULL     DEFAULT NULL COMMENT '{"title":"备注"}',
    `status`    tinyint(1)          NOT NULL DEFAULT 1 COMMENT '{"title":"状态","type":"select","opts":["已隐藏","已显示"]}',
    `sort`      bigint(20) UNSIGNED NULL     DEFAULT 0 COMMENT '{"title":"排序权重","type":"number"}',
    `create_at` timestamp           NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '{"title":"创建时间","type":"datetime"}',
    `update_at` datetime            NULL     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '{"title":"更新时间","type":"datetime"}',
    `deleted_time`   int UNSIGNED NOT NULL DEFAULT 0 COMMENT '{"title":"删除时间","type":"number","opts":["未删除","已删除"]}',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `idx_a_user_qrcode4` (`openid`) USING BTREE,
    INDEX `idx_a_user_qrcode1` (`sort`) USING BTREE,
    INDEX `idx_a_user_qrcode2` (`status`) USING BTREE,
    INDEX `idx_a_user_qrcode3` (`deleted_time`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT = '用户二维码';

CREATE TABLE IF NOT EXISTS `a_user_poster_tpl`
(
    `id`        bigint unsigned     NOT NULL AUTO_INCREMENT comment '{"title":"ID","type":"number"}',
    `pic`       varchar(500)        null comment '{"title":"模板路径","type":"pic","remark":"建议尺寸：1000×1800px"}',

    `status`    tinyint(1)          not null default 1 comment '{"title":"状态","type":"select","opts":["已隐藏","已显示"]}',
    `sort`      bigint(20) unsigned null     default 0 comment '{"title":"排序权重","type":"number"}',

    `create_at` timestamp           not null default current_timestamp comment '{"title":"创建时间","type":"datetime"}',
    `update_at` datetime                     default null on update current_timestamp comment '{"title":"更新时间","type":"datetime"}',
    `deleted_time`   int unsigned not null default 0 comment '{"title":"删除时间","type":"number","opts":["未删除","已删除"]}',
    primary key (`id`) using btree,

    index `idx_a_user_poster_tpl1` (`sort`) using btree,
    index `idx_a_user_poster_tpl2` (`status`) using btree,
    index `idx_a_user_poster_tpl3` (`deleted_time`) using btree
) engine = InnoDB
  character set = utf8mb4
  collate = utf8mb4_unicode_ci comment = '海报模板';