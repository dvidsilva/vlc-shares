
var x_advancedDebug = false;

var x_zend_defaultBlacklistKey = ['clickid', 'webkit_disabled', 'webkit:json', 'webkit', 'clearStorage', 'webkit_flash'];

function x_zend_buildroute(mappableObject, blacklistKey, explicitBaseUrl, forceDefaultModule ) {
	
	var explicitBaseUrl = explicitBaseUrl || (baseUrl ? baseUrl : "");
	var forceDefaultModule = forceDefaultModule || false;
	var controller = mappableObject.controller || "index";
	var action = mappableObject.action || "index";
	var module = mappableObject.module || ( forceDefaultModule ? "default" : "");
	if (module) module += "/";
	
	var url = explicitBaseUrl + "/" + module + controller + "/" + action + "/";
	
	for(var key in mappableObject) {
		var value = mappableObject[key];
		if ( value != null && value != 'null' && key != 'controller' && key != 'action' && key != 'module' && $.inArray(key, blacklistKey) == -1 ) {
			url += key + "/" + value + "/";
		}
	}
	
	return url;
}


var _breadcrumb = {
	previousState : "",
	container : null,
	refresh : function () {
		var thisParent = this;
		$(this.container).find('li').not(':first, :last').css('max-width', '2em').unbind('click').click(function (e) {
			if ( $(this).css('max-width') != 'none' ) {
				$(this).css('max-width', '');
				$(this).parent().find('li').not($(this)).not(':first').css({'max-width': '2em'});
				return false;
			}/* else {
				thisParent.truncate(this);
			}*/
		});
		$(this.container).find('li:last').css('max-width', '');
	},
	truncate : function( elem ) {
		if ( $(this.container, elem ) ) {
			var $tmpElem = $(elem).parent();
			$tmpElem.nextAll('li').remove();
			var oldText = $(elem).html();
			$(elem).empty();
			$(elem).html(oldText);
		}
	},
	append : function(label, currentStatus) {
		var $last = $(this.container).find('li:last');
		var $lastText = $last.html();
		var $new = $(document.createElement('a')).attr('href', "#" + this.previousState).html($lastText);
		this.previousState = currentStatus;
		$last.empty().append($new);
		
		var $newLi = $(document.createElement('li')).html(label);
		$(this.container).append($newLi);
		
		this.refresh();
	},
	exists : function(statusSign) {
		return ( $(this.container).find('a[href="#'+statusSign+'"]').size() > 0 );
	},
	getBySign : function(statusSign) {
		return $(this.container).find('a[href="#'+statusSign+'"]');
	}
};

function webkit_initBreadcrumb(breadcrumbContainer) {
	if ( breadcrumbContainer && breadcrumbContainer.length >= 1 ) {
		_breadcrumb.container = breadcrumbContainer;
		_breadcrumb.refresh();
		/*
		setTimeout(function () {
			alert('Appending');
			_breadcrumb.append("New appending", "#" + $.param.fragment());
		}, '10000');
		*/
	} else {
		debug.log("Invalid breadcrumb container");
	}
}

function webkit_updateBreadcrumb() {
	var stateSign = $.param.fragment();
	if ( _breadcrumb.exists(stateSign) ) {
		if ( x_advancedDebug ) debug.log("Breadcrumb: truncating");
		_breadcrumb.truncate(_breadcrumb.getBySign(stateSign));
		return;
	} else {
		var stateObj = $.deparam.fragment();
		if ( stateObj.clickid ) {
			var $clicked = $('#'+stateObj.clickid);
			if ( $clicked.size() ) {
				var stateLabel = $clicked.find('h2').text();
				if ( stateLabel ) {
					if ( x_advancedDebug ) debug.log("Breadcrumb: state appended");
					_breadcrumb.append(stateLabel, stateSign);
					return;
				}
			} else {
				if ( x_advancedDebug ) debug.log("Position not found. Invalid breadcrumb state");
				_breadcrumb.append('????', '');
			}
		}
		if ( x_advancedDebug ) debug.log("Breadcrumb not updated");
	}
}

//function webkit_breadcrumbAppend(status, )

function webkit_checkVlcStreaming(data, autoRedirect) {
	if ( x_advancedDebug ) debug.log("Checking vlc status...");
	if ( data && typeof(data) == 'object' ) {
		if ( x_advancedDebug ) debug.log("...valid params");
		if ( data.controller == 'controls' && data.action == "control" ) {
			if ( x_advancedDebug ) debug.log("Vlc is streaming...");
			if ( autoRedirect ) {
				if ( x_advancedDebug ) debug.log('...autoredirect');
				var state = $.deparam.fragment();
				state.controller = 'webkit';
				state.action = 'ballot';
				
				window.location = $.param.fragment(window.location.toString(), state, 0);
				
				$().toastmessage('showNoticeToast', '<b>'+ X_Env.p_webkitrenderer_vlcstreaming +'</b><br />'+X_Env.p_webkitrenderer_vlcstreaming_desc );
				
			}
			return true;
		} else {
			if ( x_advancedDebug ) debug.log("Vlc is down");
		}
	} else {
		if ( x_advancedDebug ) debug.log("Invalid params");
	}
	return false;
}


function webkit_update_title(state) {
	if ( state.clickid && localStorage ) {
		var clickedHTML = localStorage.getItem(state.clickid);
		var $clicked = $('<div>'+clickedHTML+'</div>');
		
		var label = $clicked.find('blockquote h2').text().trim();
		
		if ( $clicked ) {
			$('#app-current-position').text('\u00A0'+label);
		} else {
			$('#app-current-position').text('\u00A0');
		}
		
	} else {
		$('#app-current-position').text('\u00A0');
	}
}

function webkit_browser( state ) {
	
	$('#screen-overlay').remove();
	
	webkit_update_title(state);
	
	var location = state.l ? state.l : "";
	var provider = state.p ? state.p : "";
	
	if ( !$('#browser-browsable').length ) {
		  $('#app-content').empty();
		  $('#tmplSectionBrowser').tmpl().appendTo('#app-content');
	} else {
		$('.browser-container .item').fadeOut(function () {
			$(this).remove();
		});
	}
	
	var url = "";
	
	if (provider.length) {
		url = baseUrl+"/browse/share/p/"+provider + (location.length ? "/l/"+location : "");
	} else {
		url = baseUrl+"/index/collections/webkit:json/1";
	}
	
	if ( x_advancedDebug ) debug.log("Fetching: " + url);
	
	$.getJSON(url, function (data) {
		
		if ( x_advancedDebug ) debug.log("Checking success: "+data.success);
		
		if ( x_advancedDebug ) debug.log(data);
		
		// check if vlc is streaming
		if ( webkit_checkVlcStreaming(data, true) )
			return;
		
		$(data.items).each(function (i, item) {
			
			
			item.key = item.key.replace(/[^a-zA-Z0-9-_]+/g, '_');
			
			item.href = $.param.fragment(window.location.toString(), item.link, 0 );
			
			item.href = $.param.fragment(item.href, {'clickid' : 'item_'+item.key }, 0);
			

			if ( $.inArray(item.key, ['updatenotifier-coreupdate', 'updatenotifier-pluginsupdate']) != -1 ) {
				// remove update notifier
				$().toastmessage('showNoticeToast', '<b>'+item.label+'</b><br />'+item.description );
				
				return;
			} else if ( item.key == 'core-cache-disable' ) {
				// cached msg
				$().toastmessage('showToast', {
				    text     : item.description+'<br /><a href="' + item.href +  '">'+item.label+'</a>',
				    sticky   : false,
				    type     : 'warning'
				});
				return;
			} else if ( item.key == 'emptylists' ) {
				
				// remove the container so we can change position
				$('.toast-container').remove();
				$().toastmessage('showToast', {
				    text     : item.label,
				    sticky   : true,
				    type     : 'error',
				    position : 'middle-center',
				    close    : function () {
				    	//// remove the container so we can change position back to default
				    	$('.toast-container').remove();
				    	history.back();
				    }
				});
				return;
			}
			
			
			if ( item.type == "container" ) {
				$('#tmplItemBrowsable').tmpl(item).hide().appendTo('#browser-browsable').fadeIn();
			} else if ( item.type == "element" ) {

				// check thumbs
				
				if ( !item.thumbnail && state.clickid && localStorage ) {
					var clickedHTML = localStorage.getItem(state.clickid);
					var $clicked = $('<div>'+clickedHTML+'</div>');
					if ( $clicked ) {
						item.thumbnail = $clicked.find('img').attr('src');
					}
				}
				
				//$('#tmplItemPlayable').tmpl(item).hide().appendTo('#browser-playable').fadeIn();
				$('#tmplItemPlayable').tmpl(item).hide().appendTo('#browser-browsable').fadeIn();
			}
			
		});
		
	});
	
}


function webkit_modechooser(state) {

	$('#screen-overlay').remove();
	
	if ( !$('#modechooser').length ) {
		// update the title
		webkit_update_title(state);
		//setup UI
		$('#app-content').empty();
		$('#tmplSectionModeChooser').tmpl().appendTo('#app-content');

		if ( state.clickid && localStorage ) {
			var clickedHTML = localStorage.getItem(state.clickid);
			var $clicked = $('<div>'+clickedHTML+'</div>');
			if ( $clicked ) {
				
				var clickedItem = {
					'thumbnail': $clicked.find('img').attr('src'),
					'description': $clicked.find('blockquote p').text(),
					'label': $clicked.find('blockquote h2').text()
				};
				
				if ( clickedItem.label ) {
					$('#tmplModeChooserItemDescriber').tmpl(clickedItem).appendTo('#modechooser');
				}
			}
		}
		
		$('#tmplSectionModeChooserMain').tmpl().appendTo('#modechooser');
		
	}
	
	if ( state.action == 'mode' ) {
		
		// hide old modes
		
		$('#modechooser-sidecolumn').fadeOut(function () {
			$('#modechooser-maincolumn').animate({width: '98%'}, 'slow');
		});
		
		$('#modechooser-specials .item, #modechooser-maincolumn .item, #modechooser-sidecolumn .item').fadeOut(function () {
			$(this).remove();
		});
		
		// populate modes
		var url = "";
		url = x_zend_buildroute(state, ['clickid', 'webkit_disabled', 'webkit:json', 'webkit', 'clearStorage']);
		
		if ( x_advancedDebug ) debug.log("Fetching: " + url);
		
		$.getJSON(url, function (data) {
			if ( x_advancedDebug ) debug.log("Checking success: "+data.success);
			if ( x_advancedDebug ) debug.log(data);
			
			// check if vlc is streaming
			if ( webkit_checkVlcStreaming(data, true) ) return;
			
			
			$(data.items).each(function (i, item) {

				item.key = item.key.replace(/[^a-zA-Z0-9-_]+/g, '_');
				
				var destination;
				var tmpl;
				
				if ( item.key == 'core-separator' ) {
					// ignore
					return;
				} else if ( $.inArray(item.key, ['core-directwatch', 'core-play']) != -1 ) {
					
					tmpl = '#tmplItemSpecial';
					destination = '#modechooser-specials';
					
					if ( !item.url ) {
						$(item.link).extend({clickid : "special_"+item.key});
					}
					
					if ( item.key == 'core-directwatch' || item.type == 'playable' ) {
						item.customargs = "rel='"+item.link+"'";
						item.link = {
							'controller': 'webkit',
							'action': 'ballot'
						};
						item.url = false;
					} 
					
					if ( item.key == 'core-play' && (!item.icon || item.icon == baseUrl) ) {
						item.icon = baseUrl+"/images/logo.png";
					}
					
				} else {
					
					tmpl = '#tmplItemOption';
					destination = '#modechooser-maincolumn';
					
					//$(item.link).extend({clickid : "option_"+item.key});
					
					item.onClick = "webkit_getPreferences('" + $.param.fragment(window.location.hash, item.link, 0 ) + "', this); return false;";
					item.link = "javascript:void(0);";
					item.url = true;
					
					
				}
				if ( !item.url ) {
					item.href = $.param.fragment(window.location.toString(), item.link, 0 );
				} else {
					item.href = item.link;
				}
				
				$(tmpl).tmpl(item).hide().appendTo(destination).fadeIn();
				
			});
		});
		
	} else if (state.action == 'selection' ) {
		
		$('#modechooser-sidecolumn .item').remove();
		
		$('#modechooser-maincolumn').animate({width: '48.5%'}, 'slow', function () {
			$('#modechooser-sidecolumn').fadeIn();
		});
		
		$('#modechooser-maincolumn .highlight').removeClass('highlight');
		
		var pid = $.deparam.fragment().pid;
		if ( pid ) {
			if ( x_advancedDebug ) debug.log('Pid found: '+pid);
			$('#modechooser-maincolumn #option_'+pid).addClass('highlight');
		} else {
			if ( x_advancedDebug ) debug.log('Pid not found');
		}

		var url = "";
		url = x_zend_buildroute(state, ['clickid', 'webkit_disabled', 'webkit:json', 'webkit', 'clearStorage']);
		
		if ( x_advancedDebug ) debug.log("Fetching: " + url);
		
		$.getJSON(url, function (data) {
			if ( x_advancedDebug ) debug.log("Checking success: "+data.success);
			if ( x_advancedDebug ) debug.log(data);
			
			$(data.items).each(function (i, item) {

				item.key = item.key.replace(/[^a-zA-Z0-9-_]+/g, '_');
				
				var destination;
				var tmpl;
				
				if ( $.inArray(item.key, ['core-back', 'core-separator']) != -1  ) {
					// ignore
					return;
				} else {
					
					tmpl = '#tmplItemOption';
					destination = '#modechooser-sidecolumn';
					
					$(item.link).extend({clickid : "option_"+item.key});
					
				}
				if ( !item.url ) {
					item.href = $.param.fragment(window.location.toString(), item.link, 0 );
				} else {
					item.href = item.link;
				}
				
				$(tmpl).tmpl(item).hide().appendTo(destination).fadeIn();
				
				
			});
			
			
			
		});
		
		
	}
	
}

function webkit_getPreferences(urlFragment, item) {
	
	if ( x_advancedDebug ) debug.log("Getting preferences");
	
	var $item = $(item).addClass('highlight');
	var $fragmentObj = $.deparam.fragment(urlFragment);

	var url = "";
	url = x_zend_buildroute($fragmentObj, x_zend_defaultBlacklistKey );

	try {
		// this try guard catch only borderline cases when  slideUp take too much
		$('#modechooser-preferences').slideUp('slow', function () {
			$item.parent().find('.highlight').not($item).removeClass('highlight');
			$(this).remove();
		});
	} catch (e) {
		if ( x_advancedDebug ) debug.log("Concurrency guard");
	}
		
	if ( x_advancedDebug ) debug.log("Fetching: " + url);
	
	$.getJSON(url, function (data) {
		if ( x_advancedDebug ) debug.log("Checking success: "+data.success);
		if ( x_advancedDebug ) debug.log(data);
		
		// we are sure that there is 1 #...-preferences only
		$('#modechooser-preferences').remove();
		
		$('#tmplSectionModeChooserPreferences').tmpl().insertAfter(item);
		
		$(data.items).each(function (i, item) {

			item.key = item.key.replace(/[^a-zA-Z0-9-_]+/g, '_');
			
			var destination;
			var tmpl;
			
			if ( $.inArray(item.key, ['core-back', 'core-separator']) != -1  ) {
				// ignore
				return;
			} else {
				
				tmpl = '#tmplItemOption';
				destination = '#modechooser-preferences';
				
				$(item.link).extend({clickid : "option_"+item.key});
				
			}
			if ( !item.url ) {
				item.href = $.param.fragment(window.location.toString(), item.link, 0 );
			} else {
				item.href = item.link;
			}
			
			$(tmpl).tmpl(item).appendTo(destination);
			
		});
		
		$('#modechooser-preferences').slideDown('slow');
	});	
}

function webkit_player(state) {
	if ( x_advancedDebug ) debug.log("Showing player...");

	var streamUrl = $.deparam.fragment().webkit_flash;
	
	streamUrl = decodeURIComponent(streamUrl);
	
	if ( !$('#screen-overlay').length ) {
		$('#tmplSectionOverlay').tmpl().appendTo('#app-content');
	}
	
	if ( x_advancedDebug ) debug.log('Stream URL: '+streamUrl);
	
	$('#ballot-inner').fadeOut();

	if ( !$('#player').length ) {
	
		$('#tmplSectionPlayer').tmpl({"url": streamUrl})./*hide().*/prependTo('#screen-overlay-inner')/*.fadeIn('slow', function () {*/;
		
		//go to player / player ballot screen
		$('#player-flash').flowplayer(baseUrl+'/swf/webkit/flowplayer-3.2.7.swf',	{
			canvas:  {
				// configure background properties
				background: '#000000 url( '+baseUrl+'/images/logo.png) no-repeat 82 15',
				// remove default canvas gradient
				backgroundGradient: 'none',
				// setup a light-blue border
				border:'2px solid #FF9900'
				
			},
			"clip" : {
				//live: true,
		        autoPlay: false,
		        autoBuffering: false
			},
			"screen" : {
				"height" : "100pct",
				"top" : 0
			},
			"plugins" : {
				"controls" : {
					"buttonOffColor" : "rgba(130,130,130,1)",
					"timeColor" : "#ffffff",
					"borderRadius" : "0px",
					"stop" : true,
					"bufferGradient" : "none",
					"zIndex" : 1,
					"sliderColor" : "#000000",
					"backgroundColor" : "rgba(0, 0, 0, 0)",
					"scrubberHeightRatio" : 0.5,
					"tooltipTextColor" : "#ffffff",
					"volumeSliderGradient" : "none",
					"spacing" : {
						"time" : 6,
						"volume" : 8,
						"all" : 2
					},
					"sliderGradient" : "none",
					"timeBorderRadius" : 20,
					"timeBgHeightRatio" : 0.8,
					"volumeSliderHeightRatio" : 0.6,
					"progressGradient" : "none",
					"height" : 26,
					"volumeColor" : "rgba(255, 155, 15, 1)",
					"tooltips" : {
						"marginBottom" : 5,
						"buttons" : false
					},
					"name" : "controls",
					"timeSeparator" : " ",
					"volumeBarHeightRatio" : 0.2,
					"opacity" : 1,
					"left" : "50pct",
					"timeFontSize" : 12,
					"tooltipColor" : "rgba(0, 0, 0, 0)",
					"bufferColor" : "#a3a3a3",
					"border" : "0px",
					"volumeSliderColor" : "#ffffff",
					"buttonColor" : "#ffffff",
					"durationColor" : "#b8d9ff",
					"autoHide" : {
						"enabled" : true,
						"hideDelay" : 500,
						"mouseOutDelay" : 500,
						"hideStyle" : "fade",
						"hideDuration" : 400,
						"fullscreenOnly" : true
					},
					"backgroundGradient" : "none",
					"width" : "100pct",
					"display" : "block",
					"sliderBorder" : "1px solid rgba(128, 128, 128, 0.7)",
					"buttonOverColor" : "#ffffff",
					"url" : "flowplayer.controls-3.2.5.swf",
					"timeBorder" : "0px solid rgba(0, 0, 0, 0.3)",
					"progressColor" : "rgba(255, 153, 0, 1)",
					"timeBgColor" : "rgb(0, 0, 0, 0)",
					"scrubberBarHeightRatio" : 0.3,
					"scrubber" : true,
					"bottom" : 0,
					"volumeBorder" : "1px solid rgba(128, 128, 128, 0.7)",
					"builtIn" : false,
					"margins" : [ 2, 12, 2, 12 ]
				}
			}
		});
	} else {
		
	}
}


function webkit_stream(state) {
	
	if ( x_advancedDebug ) debug.log("Checking vlc status...");
	
	// checking status
	var url;
	
	url = x_zend_buildroute({ controller: 'controls', action: 'control' }, []);
	
	$.getJSON(url, function (data) {
	
		// if stream is live
		// ignore stream wake up
		
		if ( data.controller != "controls" ) {
			
			var url;
			url = x_zend_buildroute(state, x_zend_defaultBlacklistKey);
			
			if ( x_advancedDebug ) debug.log("Launching stream: "+url);
			
			$.getJSON(url, function (datas) {
				
				if ( x_advancedDebug ) debug.log(data);
				
				if ( datas.success ) {
					
					// get the stream link
					/* //auto shutdown hack
					setTimeout(function () {
					
						alert("Press OK to stop the stream");
						var url;
						url = x_zend_buildroute({ controller: 'controls', action: 'execute', pid: 'controls', a: 'stop' }, []);
						
						// shutdown stream
						$.getJSON(url, function (datas) {
							
							if ( x_advancedDebug ) debug.log(datas);
							
							if ( datas.success ) {
								alert("Stream is down. Go check the truth");
								
								if ( x_advancedDebug ) debug.log("Stream is down");
								
								$('#player-overlay').fadeOut(function () {
									$(this).remove();
								});
								
								
								var currentState = $.deparam.fragment();
								currentState.action = 'mode';
								currentState.controller = 'browse';
								// invoke new window fase
								window.location = $.param.fragment(window.location.toString(), currentState, 0);
								
							}
							
							
						});
					}, '20000');
					*/
					
					if ( x_advancedDebug ) debug.log("Redirecting to ballot");
					
					// redirect to ballot
					var currentState = $.deparam.fragment();
					currentState.action = 'ballot';
					currentState.controller = 'webkit';
					// invoke new window fase
					window.location = $.param.fragment(window.location.toString(), currentState, 0);
					
				}
				
			});
			
		} else {
			
			var currentState = $.deparam.fragment();
			currentState.action = 'mode';
			currentState.controller = 'browse';
			// invoke new window fase
			window.location = $.param.fragment(window.location.toString(), currentState, 0);
			
		}
	
	});
	
}

function webkit_execCommand(trigger, href, request) {
	
	var param = "";
	if ( request ) {
		// require the param
		var desc = $(trigger).find('p').text().trim();
		param = prompt(desc);
		if ( param == null ) {
			param = "";
		}
		href = href.replace(/\:param\:/g, param);
	}

	if ( x_advancedDebug ) debug.log("Executing: "+href);	
	
	$.getJSON(href, function (data) {
		
		if ( x_advancedDebug ) debug.log(data);
		
		if ( data.success && data.items && data.items.length >= 1) {
			if ( data.items[0].customs && (data.items[0].customs.vlc_still_alive === true || data.items[0].customs.vlc_still_alive === false ) ) {
				// we can check
				if ( data.items[0].customs.vlc_still_alive == false ) {
					
					if ( x_advancedDebug ) debug.log("Redirecting to browse/mode");
					
					// vlc has been shutdown, redirect to state browse/mode
					var state = $.deparam.fragment();
					state.controller = 'browse';
					state.action = 'mode';
					
					// redirect to new state
					window.location = ($.param.fragment(window.location.toString(), state, 0));
				} else {
					
					var fakeState = $.deparam.fragment();
					fakeState.controller = 'controls';
					fakeState.action = 'control';
					
					url = x_zend_buildroute(fakeState, x_zend_defaultBlacklistKey);
					
					if ( x_advancedDebug ) debug.log("Fetching: "+url);
					
					$.getJSON(url, function (data) {

						if ( data.controller == 'controls' ) {
							webkit_populateControls(data);
						}
						
					});
					
				}
			}
		}
		
	});
	
	return false;
	
}

function webkit_populateControls(data) {
	
	if ( !$('#controls').length ) {
		
		if ( !$('#screen-overlay').length ) {
			$('#tmplSectionControls').tmpl().appendTo('#app-content');
		}
		
		$('#tmplSectionControls').tmpl().appendTo('#screen-overlay-inner');
		
	} else {
		$('#controls').empty();
	}
	
	var streamSourceUrl = false;
	
	// populate controls
	
	$(data.items).each(function (i, item) {
		
		if ( item.key == "core-separator" ) return;
		
		if ( item.type == 'playable' && item.url ) {
			if ( x_advancedDebug ) debug.log("Playable item found");
			if ( !streamSourceUrl ) {
				streamSourceUrl = item.link;
			}
		} else {

			if ( item.key.search(/^streaminfo\-/) !== -1 ) {
				
				item.customClass = "info";
				item.href = "javascript:void(0);";
				
			} else if ( item.link ) {
			
				if ( item.type == "request" ) {
					item.link.param = ":param:";
				}
				
				item.onClick = x_zend_buildroute(item.link, []);
				
				item.onClick = "webkit_execCommand(this, '"+item.onClick+"', " + (item.type == 'request' ? 'true' : 'false' ) + "); return false;";
				
				item.href = "javascript:void(0);";
				
				
			}
			$('#tmplItemControl').tmpl(item).appendTo('#controls');
		}
		
	});	
	
	return streamSourceUrl;
}

function webkit_ballot(state) {
	
	if ( !$('#screen-overlay').length ) {
		$('#tmplSectionOverlay').tmpl().appendTo('#app-content');
	} else {
		$('#screen-overlay-inner').empty();
	}
	
	//try to get controls first
	var is_vlc_stream = false;
	var url;
	
	var fakeState = $.extend({}, state);
	fakeState.controller = 'controls';
	fakeState.action = 'control';
	
	url = x_zend_buildroute(fakeState, x_zend_defaultBlacklistKey);
	
	if ( x_advancedDebug ) debug.log("Fetching: "+url);
	
	$.getJSON(url, function (data) {
		
		if ( x_advancedDebug ) debug.log(data);

		var streamSourceUrl = false;		
		
		// check over controller handle redirects
		if ( data.success && data.controller == "controls" ) {
			
			streamSourceUrl = webkit_populateControls(data);
			
		}
		
		if ( !streamSourceUrl  ) {
			
			// streamSource must be get from the directplay url
			
			if ( $('#special_core-directwatch').length ) {
				
				streamSourceUrl = $('#special_core-directwatch').attr('rel');
				
			} else {
				// redirect to the default state
				
				if ( x_advancedDebug ) debug.log("Error: redirect to browse/mode");
				
				var state = $.deparam.fragment();
				state.controller = 'browse';
				state.action = 'mode';
				
				// redirect to new state
				window.location = ($.param.fragment(window.location.toString(), state, 0));
				
				return;
			}
			
		}
		
		var flashHref;
		flashState = $.extend({}, state);
		flashState.controller = 'webkit';
		flashState.action = 'flash';
		flashState.webkit_flash = streamSourceUrl;
		flashHref = $.param.fragment(window.location.toString(), flashState, 0);
		
		var sharetoappHref = 'sharetoapp://'+encodeURIComponent(streamSourceUrl);
		
		var ballotInfo = {
				flash: {
					href: flashHref
				},
				sharetoapp: {
					href: sharetoappHref
				},	
				directurl: {
					href: streamSourceUrl
				}	
			};
			
		$('#tmplSectionBallot').tmpl(ballotInfo).prependTo('#screen-overlay-inner');
		
	});
	
}


$(function(){
	
	debug.log("Loading: app.js");

	
	$(window).bind( 'hashchange', function(e) {
		
		debug.log("hashchange: "+$.param.fragment());
		
		// process hash
		var state = $.deparam.fragment($.param.fragment());
		if ( x_advancedDebug ) debug.log(state);
		
		
		state.controller = state.controller ? state.controller : 'index';
		state.action = state.action ? state.action : 'collections';
		
		if ( state.clickid && localStorage ) {
			// something clicked (maybe)
			// store in localStorage
			var $clicked = $('#'+state.clickid);
			if ( $clicked.length ) {
				try {
					localStorage.setItem(state.clickid, $clicked.html());
				} catch (e) {
					if (e == QUOTA_EXCEEDED_ERR) {
						localStorage.clear();
						try {
							localStorage.setItem(state.clickid, $clicked.html());
						} catch (e) {
							debug.log("localStorage not working after clear()");
						}
					}
				}
			}
		}
		
		var stateSign = state.controller + '/' + state.action;
		
		switch ( stateSign ) {
		
			case 'index/collections':
			case 'browse/share':
				webkit_updateBreadcrumb();
				webkit_browser(state);
				break;
			
			case 'browse/mode':
				webkit_updateBreadcrumb();
			case 'browse/selection':
				webkit_modechooser(state);
				break;

			case 'browse/stream':
				webkit_stream(state);
				break;
				
			case 'webkit/ballot':
				webkit_ballot(state);
				break;
				
			case 'webkit/flash':
				webkit_player(state);
				break;
		}
		
		// proxy to function
		
		
	});
	
	$("#loading").ajaxStart(function(){
		$(this).show();
	}).ajaxStop(function(){
		$(this).hide();
    });	
	
	webkit_initBreadcrumb($('#app-breadcrumb ul'));
	
	$().toastmessage({sticky : true});
	
	if ( $.deparam.fragment().clearStorage == "true" ) {
		localStorage.clear();
		alert("Storage is empty");
	}
	
	if ( $.deparam.fragment().webkit_disabled == undefined ) {
		debug.log("Hashchange enabled");
		$(window).trigger( 'hashchange' );
	} else {
		debug.log("Hashchange disabled");
	}
	
	
});