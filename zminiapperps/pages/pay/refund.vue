<template>
	<view class="container-v refund">
		<view class="w100">
			<v-card v-for="(item,index) in list" :key="item.gid">
				<template v-slot:content>
					<!-- <view><text class="color-">充值单号：</text><text class="color-desc">{{item.gid}}</text></view> -->
					<view><text class="color-">实充余额：</text><text class="color-desc">{{item.real_has}} / {{item.real_init}}</text></view>
					<view><text class="color-">赠送余额：</text><text class="color-desc">{{item.give_has}} / {{item.give_init}}</text></view>
					<view><text class="color-">充值时间：</text><text class="color-desc">{{item.create_at}}</text></view>
					<view><text class="color-" v-show="item.refund_at">退款时间：</text><text class="color-desc">{{item.refund_at}}</text></view>
				</template>
				<template v-slot:btns>
					<view class="btns">
						<view class="color-desc" v-if="item.status == 0">已退款</view>
						<view class="color-green" v-else-if="item.status == 1 && item.real_has > 0">使用中</view>
						<view class="color-desc" v-else-if="item.status == 1 && item.real_has <= 0">已用完</view>
						<view @click.stop="refund(item)" v-if="item.status == 1 && item.real_has > 0" class="btn-sm btn-org">申请退款</view>
						<view class="btn-sm btn-desc" v-else>申请退款</view>
					</view>
				</template>
			</v-card>
		</view>
		<uni-load-more status="nomore"></uni-load-more>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'

	const list = ref();
	
	uni.$login.judgePhone(()=>{
		getList();
	})
	
	function getList(){
		uni.$http.post("v1/getMyMoneyCardList").then(res=>{
			list.value = res.data.list;
		})
	}

	function refund(item){
		uni.$tool.confirm("确定要发起退款申请吗？", ()=>{
			uni.$tool.showLoading("受理中...");
			uni.$http.post("v1/moneyCardRefund/" + item.gid).then(res=>{
				uni.$tool.hideLoading();
				if(res.code != 1) return uni.$tool.tip(res.info||"操作失败")
				uni.$tool.tip(res.info||"受理成功")
				getList();
			})
		})
	}
	
</script>

<style lang="scss" scoped>
	.refund{
		justify-content: center;
		align-items: center;
		width: 100%;
		padding: 20rpx 10%;
		box-sizing: border-box;
	}
	.btns{
		padding: 0 20rpx;
		width: 100%;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
</style>
