import tool from '@/extend/tool.js';
// import tool from '@/extend/tool.js';
// import tool from '@/extend/tool.js';
export default {
	/**
	 * 添加到购物车
	 * let params = {sn, number}
	 */
	add2cart(params, cb){
		uni.$http.post("v1/add2cart", params).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			uni.$tool.setCartBadge(res.data.total)
			cb && cb(res)
		})
	},
	/**
	 * 更新到购物车
	 * let params = {sn, number}
	 */
	update2cart(params, cb){
		uni.$http.post("v1/update2cart", params).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			uni.$tool.setCartBadge(res.data.total)
			cb && cb(res)
		})
	},
	/**
	 * 删除到购物车
	 * let params = {sn, number}
	 */
	del2cart: tool.debounce((params, cb)=>{
		uni.$http.post("v1/del2cart", params).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			uni.$tool.setCartBadge(res.data.total)
			cb && cb(res)
		})
	}),
	/**
	 * 选择购物车
	 * let params = {sn, is_checked}
	 */
	check2cart: tool.debounce((params, cb)=>{
		uni.$http.post("v1/check2cart", params).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			uni.$tool.setCartBadge(res.data.total)
			cb && cb(res)
		})
	}),
	
	/**
	 * 获取购物车数量
	 */
	getCartTotal(cb){
		uni.$http.get("v1/getCartTotal").then(res=>{
			uni.$tool.setCartBadge(res.data.total)
			cb && cb(res)
		})
	},
	
	/**
	 * 得到用户信息
	 * @param {Object} cb
	 */
	getUserInfo(cb){
		uni.$http.get("v1/getUserInfo").then(res=>{
			cb && cb(res)
		})
	},
	
	/**
	 * 支付预下单
	 */
	getPayInfo(type, gid){
		return uni.$http.post("v1/getPayInfo", {type,gid})
	},
	
	/**
	 * 得到应用配置
	 */
	getAppConfig(){
		return uni.$http.get("v1/getAppConfig")
	}
	
	
}