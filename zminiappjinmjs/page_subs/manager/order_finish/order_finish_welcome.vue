<template>
	<view class="container-v main">
		<view class="container-h v-mb-20">
			<v-line-title title="订单编号："></v-line-title>
		</view>
		<view class="container-h v-mb-20">
			<uni-easyinput v-model="sn" suffixIcon="scan" @iconClick="getSnFromScan" placeholder="请扫描或输入订单编号"></uni-easyinput>
		</view>
		<view class="v-mt-200 v-text-right">
			<button @click="toFinish" type="primary" style="background-color: #5aa1d8;color:#fff;">确定查询</button>
			<view class="color-green v-mt-10">
				<text class="v-mr-40" @click="showPick">查看本站配货单</text>
				<text @click="show">查看我的核销</text>
			</view>
		</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const sn = ref("")
	
	async function toFinish(){
		let sn_ = sn.value || "";
		sn.value = "";
		if(!sn_) return uni.$tool.tip("请扫描或输入订单编号")
		const res = await uni.$http.post("v1/isAuthFinishOrder");
		if (res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		uni.$tool.navto("/page_subs/manager/order_finish/order_finish_index?sn="+sn_);
	}
	
	// 扫描二维码，得到订单号
	function getSnFromScan(){
		// #ifdef MP-WEIXIN
		uni.scanCode({
			success({result}) {
				if(result){
					sn.value = result;
					toFinish();
				}
				else console.log("扫码失败")
			},
			fail(err) {
				console.log(err);
			}
		})
		// #endif
		// #ifdef H5
		sn.value = "ORDERWE9IQKUNGBF2DMS"
		toFinish();
		// #endif
	}
	
	function showPick(){
		uni.$tool.navto("/page_subs/manager/order_finish/order_pick_list")
	}
	function show(){
		uni.$tool.navto("/page_subs/manager/order_finish/order_finish_list")
	}
	
	
	
	
</script>

<style lang="scss" scoped>
	$width: 35%;
	.main{
		padding: 40rpx 60rpx;
		.way-list{
			display: flex;
			// justify-content: space-around;
			width: 100%;
			flex-wrap: wrap;
			.way-item{
				width: $width;
				border: 1px solid $green2;
				border-radius: 10rpx;
				display: flex;
				flex-direction: column;
				justify-content: space-around;
				align-items: center;
				margin-bottom: 40rpx;
				margin-left: calc((100% - $width * 2) / 3);
				padding: 20rpx 0;
				.give{
					color: $org;
				}
				&.curr{
					background-color: $green;
					color: #fff;
				}
			}
		}
	}
</style>
