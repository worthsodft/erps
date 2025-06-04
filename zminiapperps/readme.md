# 进销存系统说明书

## 一、版本对比

<style>
    body{
        margin: 0 auto;
    }
</style>

<table>
    <thead>
        <tr>
            <th>功能模块</th>
            <th>子功能</th>
            <th>标准版</th>
            <th>专业版</th>
        </tr>
    </thead>
    <tbody>
        <!-- 数据统计看板 -->
        <tr class="module-row">
            <td>数据统计看板</td>
            <td>数据统计看板</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <!-- 基础数据 -->
        <tr class="module-row">
            <td rowspan="11">基础数据</td>
            <td>小程序用户管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>企业用户管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>仓库信息管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>水站信息管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>商品信息管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>原材料管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>实物卡商品管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>商品分类管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>供应商管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>往来合同管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>生产员工管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <!-- 销售管理 -->
        <tr class="module-row">
            <td rowspan="8">销售管理</td>
            <td>小程序订单管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>企业订单管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>经销商订单管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>优惠券模板管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>优惠券发布管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>充值优惠管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>实物卡管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>实物卡变动记录</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <!-- 生产管理 -->
        <tr class="module-row">
            <td rowspan="5">生产管理</td>
            <td>历史销量管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>生产计划管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>生产批量入库</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>原料申领管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>原料退还管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <!-- 采购管理 -->
        <tr class="module-row">
            <td>采购管理</td>
            <td>采购单管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <!-- 库存管理 -->
        <tr class="module-row">
            <td rowspan="8">库存管理</td>
            <td>商品入库管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>商品出库管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>库存调拨管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>库存损益管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>商品损益报表</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>库存盘点管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>库存变更记录</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>商品库存报表</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <!-- 财务管理 -->
        <tr class="module-row">
            <td rowspan="8">财务管理</td>
            <td>收款单管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>应收账款统计</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>应收期初期末</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>付款单管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>应付账款统计</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>应付期初期末</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>企业预收款管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>订单开票管理</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <!-- 统计分析 -->
        <tr class="module-row">
            <td rowspan="10">统计分析</td>
            <td>余额变动记录</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>余额充值记录</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>余额消费明细</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>余额期初期末</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>实物卡期初期末</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>用户地址报表</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>订单商品报表</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>订单商品简表</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>日统计报表</td>
            <td>✅</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>原材料损耗率</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <!-- 用户推广 -->
        <tr class="module-row">
            <td rowspan="4">用户推广</td>
            <td>海报管理</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>用户二维码</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>海报模板</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
        <tr>
            <td>用户推广</td>
            <td>❌</td>
            <td>✅</td>
        </tr>
    </tbody>
</table>

## 二、演示地址

- 专业版演示地址: [https://erpp.yaodianma.com](https://erpp.yaodianma.com)
  演示账号/密码: master/master1
- 标准版演示地址: [https://erps.yaodianma.com](https://erps.yaodianma.com)
  演示账号/密码: master/master1

