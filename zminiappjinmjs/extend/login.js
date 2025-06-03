// import store from '@/store/index.js'
export default{
	// 获取登录用的code
	getCode(cb){
		// #ifdef MP-WEIXIN
		uni.login({
			success(res) {
				cb && cb(res.code);
			}
		});
		// #endif
		// #ifdef H5
		const info = uni.getSystemInfoSync();
		// const info = null;
		// info.deviceId = "obzAZ7c85NvxGhEXyOEXnP6uY0JM" // 纵横四海
		// info.deviceId = "obzAZ7eSb81qMLsW-4Lor6oPlkU4" // 英雄本色
		
		cb && cb(info.deviceId||"asdfasdf123123")
		// #endif
	},
	// 登录
	do(pid, cb){
		return new Promise((resolve, reject)=>{
			this.getCode(async code=>{
				// #ifdef MP-WEIXIN
				const res = await uni.$http.post('v1/login',{code,pid});
				// #endif
				// #ifdef H5
				const res = await uni.$http.post('v1/loginH5',{openid:code,pid})
				// #endif
				cb && cb(res)
				if(res.code == 1){
					this.updateTokenCache(res.data)
					resolve(res.data.token)
				}else{
					reject()
				}
			});
		});
	},
	updateTokenCache(data){
		uni.$tool.cache("token", data.token)
		uni.$tool.cache("expire_at", data.expire_at)
		uni.$tool.cache("expire_at_txt", data.expire_at_txt)
		uni.$tool.cache("is_phone", data.is_phone)
	},
	
	// 判断是否已登录
	async judgeLogin(cb){
		const token = uni.$tool.cache("token");
		const expire_at = uni.$tool.cache("expire_at");
		if(!token || Date.now()/1000 > expire_at){
			await this.do();
			console.log('login.do(登录)');
			cb && cb();
			return Promise.resolve()
		}else{
			cb && cb();
			return Promise.resolve()
		}
	},
	
	judgePhone(cb){
		this.judgeLogin(()=>{
			const token = uni.$tool.cache("token");
			const is_phone = uni.$tool.cache("is_phone");
			if(is_phone){
				cb && cb();
			}else{
				uni.$tool.alert("请先完善用户信息",()=>{
					uni.$tool.navto("/pages/my/form")
				})
			}
		})
	},
	
	logout(){
		uni.$tool.removeCache("token")
		uni.$tool.removeCache("expire_at")
		uni.$tool.removeCache("expire_at_txt")
		uni.$tool.removeCache("is_phone")
	},
	
	// 获取绑定手机号码
	// getPhone(params, code=null){
	// 	return new Promise((resolve,reject)=>{
	// 		if(!code){
	// 			this.getCode(code=>{
	// 				params.code = code
	// 				this.postPhone(params).then(res=>{ resolve(res) })
	// 			})
	// 		}else{
	// 			params.code = code
	// 			this.postPhone(params).then(res=>{ resolve(res) })
	// 		}
	// 	})
	// },
	// 请求后台api
	// postPhone(params){
	// 	return new Promise((resolve,reject)=>{
	// 		uni.$u.http.post(pv + '/savePhone', params, {custom:{auth:false}}).then(res=>{
	// 			if(res.code == 1){
	// 				store.commit('updateToken', res.data)
	// 				resolve(res.data.userInfo)
	// 			}
	// 		})
	// 	})
	// }
}