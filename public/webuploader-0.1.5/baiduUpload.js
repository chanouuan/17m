var baiduUpload = {

	load: function(url, data){
		var _xmlhttp = null;
		if(window.XMLHttpRequest) {  
	        _xmlhttp = new XMLHttpRequest();  
	        if(_xmlhttp.overrideMimeType) {  
	            _xmlhttp.overrideMimeType("text/html");  
	        }  
	    }else if(window.ActiveXObject){  
	        var activeName = ["MSXML2.XMLHTTP","Microsoft.XMLHTTP"];  
	        for(var i=0;i>activeName.length();i++) {  
	              try{
	            	  _xmlhttp = new ActiveXObject(activeName[i]);  
	                  break;  
	              }catch(e){}  
	        }  
	    } 
		if(!_xmlhttp) return false;
		_xmlhttp.open('POST', url + '?' + (new Date().getTime()), false);
		_xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		_xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		_xmlhttp.send(data);
	},
	
	showMessage: function(targetid, classname, reason){
		var _element = document.getElementById(targetid);
    	if(!_element) return false;
    	var _node = _element.childNodes;
    	var _error = null;
    	for(var i=0; i<_node.length; i++){
    		if(_node[i].nodeName=='DIV'&&_node[i].className==classname){
    			_error = _node[i];
    			break;
    		}
    	}
        if(!_error){
        	_error = document.createElement('div');
        	_error.className = classname;
        	_element.appendChild(_error);
        }
    	_error.innerHTML = reason || '未知错误';
	},
	
	inform: function(uploaderdom){
		var _dom = document.getElementById(uploaderdom),
			_input = document.getElementById(uploaderdom+'_input');
		if(!_dom) return false;
		var j = _dom.childNodes;
		var _list = null;
		for(var i=0; i<j.length; i++){
			if(j[i].nodeName=='DIV'&&j[i].getAttribute('id')=='fileList'){
				_list = j[i];
				break;
			}
		}
		j = _list.childNodes;
    	var _value = [];
    	for(var i=0; i<j.length; i++){
    		if(j[i].nodeName=='DIV'&&j[i].getAttribute('data-id')){
    			_value.push(j[i].getAttribute('data-id'));
    		}
    	}
    	_input && (_input.value = _value.join(','));
	},
		
	remove: function(uploaderdom, fileid, sync){
		var _input = document.getElementById(uploaderdom+'_input'),
			_element = document.getElementById(fileid),
			_parent;
		if(!_element) return false;
		_parent = _element.parentNode;
        if(_parent){
        	// 删除本地文件
        	(typeof sync == 'undefined')
        	&&_input.getAttribute('data-server')
        	&&_element.getAttribute('data-id')
        	&&this.load(_input.getAttribute('data-server'),'remove=1&id='+_element.getAttribute('data-id'));
        	_parent.removeChild(_element);  
        }
        this.inform(uploaderdom);
	},
	
	create: function(uploaderdom, formdata){
		
		var _dom = document.getElementById(uploaderdom),
			_input = document.getElementById(uploaderdom+'_input'),
			_list = null,
			_pick = null,
			_accept = {
				'image' : {
					title: 'Images',
					extensions: 'gif,jpg,jpeg,bmp,png',
					mimeTypes: 'image/*'
				},
				'video' : {
					title: 'Videos',
					extensions: '3gp,3g2,avi,mp4,mpeg,mov,tts,asx,wm,wmv,wmx,wvx,flv,mkv,rm,asf',
					mimeTypes: 'video/*'
				}
			},
			thumbnailWidth = 100*(window.devicePixelRatio||1),
	        thumbnailHeight = 100*(window.devicePixelRatio||1),
	        uploader;
		if(!_dom) return false;
		var j = _dom.childNodes;
		for(var i=0; i<j.length; i++){
			if(j[i].nodeName=='DIV'&&j[i].getAttribute('id')=='fileList'){
				_list = j[i];
			}
			if(j[i].nodeName=='DIV'&&j[i].getAttribute('id')=='filePicker'){
				_pick = j[i];
			}
		}
        uploader = WebUploader.create({ 
        	formData: formdata||{}, 
        	fileVal: 'upfile',
            auto: true,
            swf: _input.getAttribute('data-swf')||'Uploader.swf',
            server: _input.getAttribute('data-server'),
            pick: _pick,  
            accept: _input.getAttribute('data-accept')&&_accept[_input.getAttribute('data-accept')]?_accept[_input.getAttribute('data-accept')]:null,
            fileNumLimit: _input.getAttribute('data-limit')||30,//文件队列最大数量		
            chunked: true,//支持分片
            chunkSize: 20 * 1024 * 1024//分片大小
        });
        
        // 当有文件添加进来的时候
        uploader.on( 'fileQueued', function( file ) {
        	// 判断队列是否超出限制
        	if(_input.getAttribute('data-limit')){
            	var _length = 0,j = _list.childNodes;
            	for(var i=0; i<j.length; i++){
            		if(j[i].nodeName=='DIV'&&j[i].getAttribute('data-id')){
            			_length++;
            		}
            	}
        		if(_length>=parseInt(_input.getAttribute('data-limit'))){
        			alert('上传队列超出最大（'+_input.getAttribute('data-limit')+'）个文件');
        			uploader.removeFile( file );
        			return false;
        		}
        	}
        	var _item = document.createElement('div'),
        		_img = document.createElement('img'),
        		_info = document.createElement('div');
        	_item.id = file.id;
        	_item.className = 'file-item thumbnail';
        	_img.src = (typeof APPLICATION_URL == 'undefined' ? '' : APPLICATION_URL) + 'images/file.png';
        	_info.className = 'info';
        	_info.innerHTML = file.name;
        	_item.appendChild(_img);
        	_item.appendChild(_info);
        	_list.appendChild(_item);      
            // 创建缩略图
            uploader.makeThumb( file, function( error, src ) {
                if(error)return;
                _img.setAttribute('src', src);
            }, thumbnailWidth, thumbnailHeight );
            // 删除文件
            _info.addEventListener('click',function(){
            	if(confirm('确定删除?'+file.name))uploader.cancelFile( file );
            },false); 
        });
    
        uploader.on('fileDequeued', function( file ){
        	baiduUpload.remove(uploaderdom, file.id)
        });
        
        // 文件上传过程中创建进度条实时显示。
        uploader.on( 'uploadProgress', function( file, percentage ) {
        	var _element = document.getElementById(file.id);
        	if(!_element) return false;
        	var _node = _element.childNodes;
        	var _percent = null;
        	for(var i=0; i<_node.length; i++){
        		if(_node[i].nodeName=='P'&&_node[i].className=='progress'){
        			_percent = _node[i].childNodes[0];
        			break;
        		}
        	}
            if(!_percent){
            	var _div = document.createElement('p');
            	_div.className = 'progress';
            	_percent = document.createElement('span');
            	_div.appendChild(_percent);
            	_element.appendChild(_div);
            }
            _percent.style.width = percentage * 100 + '%';
            baiduUpload.showMessage(file.id, 'error', (percentage * file.size / 1024 / 1024).toFixed(2) + 'M/' + (file.size / 1024 / 1024).toFixed(2) + 'M');
        });
        
        // 文件上传成功，给item添加成功class, 用样式标记上传成功。
        uploader.on( 'uploadSuccess', function( file, response ) {
    		if(response.errorcode==0){
    			var _element = document.getElementById(file.id);
    			if(!_element) return false;
    			response.data && _element.setAttribute('data-id',response.data);
    			baiduUpload.inform(uploaderdom);
    			baiduUpload.showMessage(file.id, 'success', '上传成功');
    			typeof(baiduUploadSuccess) == 'function' && baiduUploadSuccess(response.data);
    		}else{
    			baiduUpload.showMessage(file.id, 'error', response.data);
    		}
        });
        
        // 文件上传失败，现实上传出错。
        uploader.on( 'uploadError', function( file, reason ) {
        	baiduUpload.showMessage(file.id, 'error', '上传失败');
        });
        
        // 完成上传完了，成功或者失败，先删除进度条。
        uploader.on( 'uploadComplete', function( file ) {
        	var _element = document.getElementById(file.id);
        	if(!_element) return false;
        	var _node = _element.childNodes;
        	for(var i=0; i<_node.length; i++){
        		if(_node[i].nodeName=='P'&&_node[i].className=='progress'){
        			_element.removeChild(_node[i]);
        			break;
        		}
        	}
        });
        
        uploader.on( 'error', function( code ) {
        	console.log( 'Eroor: ' + code );
        	switch(code){
        	case 'Q_EXCEED_NUM_LIMIT':
        		alert('上传队列超出最大（'+(_input.getAttribute('data-limit')||30)+'）个文件');
        		break;
        	}
        });

	}
};

(function(){
	var _uploader = document.querySelectorAll('.uploader');
	if(_uploader.length){
		for(var i=0,j=_uploader.length; i<j; i++){
			if(_uploader[i].getAttribute('id')){
				baiduUpload && baiduUpload.create(_uploader[i].getAttribute('id'));
			}
		}
	}
}());