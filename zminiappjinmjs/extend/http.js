import config from "@/extend/config.js"
import login from "@/extend/login.js"
import Request from '@/extend/luch-request/index.js'

// import base64 from "@/extend/base64.js"

const http = new Request();

http.setConfig((config_) => {
    config_.baseURL = config.domain;
    return config_
})

// 请求拦截
http.interceptors.request.use((config) => {
	config.data = config.data || {}
	config.header.token = uni.$tool.cache("token")
	// console.log(config.header.token||'token为空')
	return config 
}, config => {
	return Promise.reject(config)
})

// 响应拦截
http.interceptors.response.use(async(response) => {
	const data = response.data
	if (data.code == 401) {
		console.log('token过期，重调')
		await login.do()
		response.config.header.token = uni.$tool.cache("token")
		return http.request(response.config)
	}else if (data.code == 1) {
		return Promise.resolve(data)
	}else{
		uni.$tool.tip(data.info||"系统错误",false,null,3)
		return Promise.reject(data)
	}
}, (response) => { 
	return Promise.reject(response)
})


const doRequest = (url, data={}, auth=true, method="get")=>{
	method = method.toUpperCase()
	let config = {
		method,url
	}
	if(method == 'GET'){
		config.params = data
	}else{
		config.data = data
	}
	return http.request(config)
}
const doUpload = (url, data={}, auth=true)=>{
	let config = {
		...data
	}
	return http.upload(url, config)
}

export default{
	request(url, params, auth, method="get"){
		// 数据加密传输
		// if(params) {
		// 	const content = base64.base64Encode(JSON.stringify(params));
		// 	params = {content};
		// }
		return doRequest(url, params, auth, method);
	},
	get(url, params={}, auth=true){
		url = "/api/" + url;
		return this.request(url, params, auth, "get");
	},
	post(url, params={}, auth=true){
		url = "/api/" + url;
		return this.request(url, params, auth, "post");
	},
	upload(url, params={}, auth=true){
		url = "/api/" + url;
		return doUpload(url, params, auth);
	},
	addon: {
		get(url, params={}, auth=true){
			url = "/addon" + url;
			return doRequest(url, params, auth, "get");
		},
		post(url, params={}, auth=true){
			url = "/addon" + url;
			return doRequest(url, params, auth, "post");
		},
		upload(url, params={}, auth=true){
			url = "/addon" + url;
			return doUpload(url, params, auth);
		},
	}
}