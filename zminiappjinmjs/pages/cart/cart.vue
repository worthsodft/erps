<template>
	<view class="container-v cart">
		<v-cart-item2 v-for="(item,index) in cart.list" :key="item.id" :item="item" :index="index"
		@delItem="delItem"
		@numbeChange="numbeChange"
		@check="check"/>
		<uni-load-more v-if="cart.list && cart.list.length < 1" status="nomore"></uni-load-more>

	</view>
	<view class="total-info"  :style="{bottom: tabBarHeight}">
		<view style="display: flex;align-items: center;">
			<view class="item-check" @click="checkAll">
				<uni-icons v-if="cart.is_checked" type="checkbox-filled" size="20" color="#5aa1d8"></uni-icons>
				<uni-icons v-else type="checkbox"  size="20" color="#ccc"></uni-icons>
				全选
			</view>
			<view class="item">合计：<text class="total-price">￥{{cart.amount||"0.00"}}</text></view>
		</view>

		<view class="v-button btn-green" @click.stop="buyNow">去结算({{cart.total||0}})</view>
	</view>
</template>

<script setup>
	import {onLoad,onShow} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const cart = ref({})
	
	// #ifdef MP-WEIXIN
	const tabBarHeight = ref(0);
	// #endif
	// #ifdef H5
	const tabBarHeight = ref(uni.$tool.getTabBarHeight() + "rpx");
	// #endif

	uni.$login.judgeLogin(()=>{
		uni.$api.getCartTotal()
	})
	onShow(()=>{
		getInitDataCart()
	})
	
	const check = (item)=>{
		doCheck(item)
	}
	const checkAll = ()=>{
		let item = {
			index: -1,
			is_checked: !cart.value.is_checked
		}
		doCheck(item)
	}
	const doCheck = (e)=>{
		let params = {
			sn: e.index == -1 ? -1 : cart.value.list[e.index].sn,
			is_checked: e.is_checked
		}
		uni.$api.check2cart(params, (res)=>{
			cart.value = res.data.cart
		})
	}
	
	
	function getInitDataCart(){
		uni.$http.get("v1/getInitDataCart").then(res=>{
			cart.value = res.data.cart
			uni.$tool.setCartBadge(cart.value.total)
		})
	}
	
	function numbeChange(e){
		let list = cart.value.list;
		let item = list[e.index];
		let params = {
			sn: list[e.index].sn,
			number: e.number
		}
		uni.$api.update2cart(params, (res)=>{
			cart.value = res.data.cart
		})
	}
	
	function delItem(index){
		uni.$tool.confirm("确认要从购物车删除此项吗？", ()=>{
			let list = cart.value.list;
			let item = list[index];
			let params = {
				sn: list[index].sn,
			}
			del2cart(params)
		})
	}
	
	function del2cart(params){
		uni.$api.del2cart(params, (res)=>{
			cart.value = res.data.cart
			// cart.value.list.splice(index, 1);
		})
	}
	
	function buyNow(){
		if(cart.value.total == 0) return uni.$tool.tip("商品列表不能为空")
		uni.$login.judgePhone(()=>{
			uni.$tool.navto("/pages/order/confirm?from=cart");
		})
	}
</script>

<style lang="scss" scoped>
	.cart{
		padding: 0 20rpx;
		// margin-bottom: 70rpx;
		padding-bottom: 70rpx;
	}
	.total-info{
		background-color: #f5f5f5;
		color: #515151;
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20rpx;
		width: 100%;
		position: fixed;
		// bottom: 100rpx;
		left: 0;
		box-sizing: border-box;
		z-index: 99;
		.item-check{
			display: flex;
			align-items: center;
			padding: 10rpx;
		}
		.item{
			font-size: 32rpx;
		}
		.total-price{
			color: $org;
			font-weight: bold;
		}
	}
</style>
