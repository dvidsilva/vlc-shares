(function($){
	
	var properties = {
		funcs : {},
		receivers : '*',
		defaultDest : null,
		stringify : function (obj) {return JSON.stringify(obj, null, 2)},
		parse : JSON.parse,
		autoListen : true,
		logger : function(msg){} //console.log // or function(){} for null logger
	};
	
	var _locks = {
		listener : false	
	};
	
	var methods = {
		init : function( options ) {
			$.extend( properties, options );
			if ( properties.autoListen ) {
				methods.listen();
			}
		},
		listen : function() {
			if ( !_locks.listener ) {
				_locks.listener = true;
				window.addEventListener("message", methods.handler, false);
				properties.logger('Listener attached');
			}
		},
		unlisten : function() {
			if ( _locks.listener ) {
				_locks.listener = false;
				window.removeEventListener("message", methods.handler, false);
				properties.logger('Listener detached');
			}
		},
		
		register : function( name, func ) {
			properties.funcs[name] = func;
			properties.logger('Function registered: '+name);
		},
		
		unregister : function( name ) {
			if ( properties.funcs[name] ) {
				delete properties.funcs[name];
			} else {
				properties.logger('Function can\'t be unregistered: '+name);
			}
		},
		
		handler : function (e) {
			var data = e.data;
			var origin = e.origin;
			var source = e.source;
			
			try {
				var parsed = properties.parse(data);
				properties.funcs[parsed.func](parsed.args, origin, source);
			} catch (err) {
				// invalid protocol
				properties.logger('Error handling rpc: ' + err);
			}
		},
		
		send : function (funcName, funcArgs) {
			if ( properties.defaultDest != null ) {
				methods.sendTo(properties.defaultDest, funcName, funcArgs);
			} else {
				properties.logger('No default destination available');
			}
		},
		
		sendTo : function (dest, funcName, funcArgs) {
			try {
				dest.postMessage(properties.stringify({
					func: funcName,
					args: funcArgs
				}), properties.receivers);
			} catch (e) {
				properties.logger('Error sending rpc: '+e);
			}
		},
		
		sendToParent : function (funcName, funcArgs) {
			if ( parent && parent != window ) {
				methods.sendTo(parent, funcName, funcArgs);
			} else {
				properties.logger('Can\'t send message to parent. No parent available');
			}
		}
		
	};	

	$.fn.xfc = function( method ) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.xfc' );
		}    
	};
	
	// loaded, trigger event
	$(document).trigger('_xfc_ready');
	
})( jQuery );




