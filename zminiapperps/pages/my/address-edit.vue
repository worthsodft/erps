<template>
	<view class="container-v main">
		<form>
			<view class="form-item">
				<view class="uni-h6 v-mb-10 require">选择区域:</view>
				<v-address-picker :cities="cities" @change="addressChange" :value="item.district"></v-address-picker>
			</view>
			<view class="form-item">
				<view class="uni-h6 v-mb-10 require">详细地址:</view>
				<uni-easyinput type="textarea" maxlength="200" autoHeight v-model="item.detail" placeholder="请输入详细地址"></uni-easyinput>
			</view>
			<view class="form-item">
				<view class="uni-h6 v-mb-10 require">联系人:</view>
				<uni-easyinput autoHeight maxlength="20" v-model="item.link_name" placeholder="请输入联系人姓名"></uni-easyinput>
			</view>
			<view class="form-item">
				<view class="uni-h6 v-mb-10 require">联系电话:</view>
				<uni-easyinput autoHeight type="number" maxlength="20" v-model="item.link_phone" placeholder="请输入联系电话"></uni-easyinput>
			</view>
			<view class="form-item">
				<view class="uni-h6 v-mb-10">是否默认:
					<view style="font-size: 24rpx; display: inline;">
						<switch :checked="item.is_default==1" @change="changeDefault" color="#2979ff" style="transform:scale(0.5)" />
					</view>
				</view>
			</view>
			<v-button-w80 @action="save" title="提交保存"></v-button-w80>
		</form>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	let gid = ""
	const item = ref({
		gid: "",
		district: "",
		detail: "",
		link_name: "",
		link_phone: "",
		is_default: 0,
	})
	const cities = ref([])
	onLoad((opts)=>{
		if(opts.gid) {
			gid = opts.gid
			getDetail()
		}
		getCities()
	})
	
	const getCities = async ()=>{
		const res = await uni.$http.get("v1/getCities")
		cities.value = res.data.cities
	}
	
	async function getDetail(){
		const res = await uni.$http.get("v1/getAddressDetail/"+gid)
		item.value = res.data.address
		item.value.district = res.data.address.district
	}
	
	function addressChange(e){
		item.value.district = e[0].value
	}
	
	const save = uni.$tool.throttle(async ()=>{
		if(!valid()) return
		const res = await uni.$http.post("v1/saveAddress", item.value)
		uni.$tool.tip(res.info, true, ()=>{
			uni.$tool.navto("/pages/my/address")
		})
		console.log(res);
	})
	
	function valid(){
		if(!item.value.district) return uni.$tool.tip("请选择区域")
		if(!item.value.detail) return uni.$tool.tip("请输入详细地址")
		if(!item.value.link_name) return uni.$tool.tip("请输入联系人")
		if(!item.value.link_phone) return uni.$tool.tip("请输入联系电话")
		if(!uni.$tool.isPhone(item.value.link_phone)) return uni.$tool.tip("电话号码格式错误")
		return true
	}
	
	function changeDefault({detail}){
		item.value.is_default = detail.value?1:0;
	}
	
</script>

<style lang="scss" scoped>
	.main{
		padding: 0 20rpx;
		form{
			.form-item{
				width: 100%;
				padding: 20rpx 0;
				// border-bottom: 1px solid $uni-border-1;
			}
		}

	}
</style>
