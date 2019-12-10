/*
 * Astero WordPress Weather Plugin
 * http://archtheme.com/astero
 *
 * A WordPress plugin using openweathermap.org to display
 * current weather conditions and weather forecasts
 */
;(function ( $, window, document, undefined ) {
        
        var pluginName = "astero_fc",
            settings = {
                current: 'https://api.darksky.net/forecast/' + astero_fc_vars.fc_api + '/',
                loading_class: 'astero-loading',
                more_class: 'astero-more',
                close_class: 'astero-close',
                closeform_class: 'astero-closeform',
                search_class: 'astero-search',
                more_placeholder_class: 'astero-more-placeholder',
                temp_class: 'astero-temperature',
                icon_class: 'astero-code',
                unit_class: 'astero-unit',
                condition_class: 'astero-condition',
                location_class: 'astero-location',
                hi_temp_class: 'astero-hi-temp',
                lo_temp_class: 'astero-lo-temp',
                humidity_class: 'astero-humidity-text',
                wind_class: 'astero-wind-text',
                cloudiness_class: 'astero-cloud-text',
                sunrise_class: 'astero-sunrise-text',
                sunset_class: 'astero-sunset-text',
                fc_hi_temp_class: 'astero-fc-hi-temp',
                fc_lo_temp_class: 'astero-fc-lo-temp',
                fc_icon_class: 'astero-fc-icon',
                fc_condition_class: 'astero-fc-condition',
                fc_cloud_class: 'astero-fc-cloud',
                fc_humidity_class: 'astero-fc-humidity',
                iframe_ratio: 16/9,
                type: 'geolocation',
            },
            weather_default = {
                lat: '',
                lon: '',
                units: '', // us (default) | si
                exclude: 'minutely,alerts,flags',
            },
            forecast_default = {
                cnt: '7' // number of forecast days
            },
            default_imgs = {
                'clear-day': 'astero-i-sun',
                'clear-night': 'astero-i-night',
                'rain': 'astero-i-rain',
                'snow': 'astero-i-snow',
                'sleet': 'astero-i-snow',
                'wind': 'astero-i-fog',
                'fog': 'astero-i-fog',
                'cloudy': 'astero-i-clouds',
                'partly-cloudy-day': 'astero-i-clouds',
                'partly-cloudy-night': 'astero-i-night',
                'hail': 'astero-i-snow',
                'thunderstorm': 'astero-i-thunderstorm',
                'tornado': 'astero-i-thunderstorm'
            },
            codes = {
                'clear-day': 'B',
                'clear-night': 'C',
                'rain': 'R',
                'snow': 'W',
                'sleet': 'X',
                'wind': 'F',
                'fog': 'M',
                'cloudy': 'Y',
                'partly-cloudy-day': 'N',
                'partly-cloudy-night': 'I',
                'hail': 'U',
                'thunderstorm': 'P',
                'tornado': 'M'
            };
            
        // The actual plugin constructor
        function Plugin( element, options, objD ) {
                var that = this;
                
                this.element = element;
                this.$element = $(element);
                this.weather_options = $.extend( {}, weather_default, options, objD );
                this.forecast_options = $.extend( {}, this.weather_options, forecast_default );
                this._settings = $.extend( {}, settings, objD );
                this.codes = codes;
                this._default_imgs = default_imgs;
                this._hasVideo = this.$element.find('video').length > 0;
                this._hasIframe = this.$element.find('.astero-yt').length > 0;
                this._hasImage = this.$element.hasClass('astero-img');
                this.$form = this.$element.find('form');
                this._isFull = this.$element.find('.astero-full').length > 0;
                this.$background = this.$element.find('.astero-background');
                this.$large = this.$element.find('.astero-large');
                this._startInit = false;
                
                // if geolocation, use geolocation
                if ( this.weather_options.location == 'ip' ) {
                	ip_geo_fallback();
                } else if ( (this.weather_options.lat).length == 0 || (this.weather_options.lon).length == 0 ) {
                        
                        if (navigator.geolocation) {
                                // timeout for if user does not respond
                                var location_timeout = setTimeout(ip_geo_fallback, 5000);

                                navigator.geolocation.getCurrentPosition(function(position) {
                                        clearTimeout(location_timeout);
                                        that.weather_options.lat = position.coords.latitude;
                                        that.weather_options.lon = position.coords.longitude;
                                        that.init(that, 'first');
                                },
                                ip_geo_fallback,
                                { enableHighAccuracy: false, timeout: 5000, maximumAge: 10000 }); 
                        }else {
                                ip_geo_fallback();
                        }
                }  else {
                        this._settings.type = 'location';
                        this._startInit = true;
                }
                
                function ip_geo_fallback() {
                        clearTimeout(location_timeout);
                        
                        $.ajax({
                                type: 'post',
                                url : astero_fc_vars.ajaxurl,
                                dataType: 'json',
                                data : { 'action': 'astero_fc_geoip' },
                                error: function(jqXHR, textStatus, errorThrown){
                                        that.error();
                                },
                                success: function(response){
                                        if( response.success ) {
                                                that.weather_options.city = response.success.city;
                                                that.weather_options.lat = response.success.lat;
                                                that.weather_options.lon = response.success.lon;
                                        } else {
                                                that.error();
                                        }

                                        that.init(that, 'first');
                                }
                        });
                }
                
                this.$element.find('.' + this._settings.more_class).click(function(){
                        that.enlarge(that);
                });
                
                this.$element.find('.' + this._settings.close_class).click(function(){
                        that.close(that);
                });
                
                this.$element.find('.' + this._settings.search_class).click(function() {
                        that.openform(that);
                })
                
                this.$element.find('.' + this._settings.closeform_class).click(function() {
                        that.closeform(that);
                })
                
                if ( this._hasIframe ) {
                        this.resizeVideo(that);
                        
                        var resizeTimer;
                        
                        $(window).resize(function(){
                                clearTimeout(resizeTimer);
                                resizeTimer = setTimeout(function() {
                                        that.resizeVideo(that);
                                }, 200);
                        });
                }
                
                if ( this._isFull ) {
                        that.setEQ(that);
                        
                        var resizeTimer;
                        
                        $(window).resize(function(){
                                clearTimeout(resizeTimer);
                                resizeTimer = setTimeout(function() {
                                        that.setEQ(that);
                                }, 200);
                        });  
                }
                
                this.$form.submit( function(e) {
                        e.preventDefault();
                        
                        var loc = $('input:text[name="location"]', $(this)),
                            units = $('select[name="units"]', $(this));
                        
                        if ( loc.val().length > 0 ) {
                                
                                var geocoder = new google.maps.Geocoder();
                        
                                if (geocoder) {
                                        geocoder.geocode({ 'address': loc.val() }, function (results, status) {
                                                if (status == "OK" && typeof( results[0].geometry.location.lat() ) != 'undefined' &&
                                                    typeof( results[0].geometry.location.lng() ) != 'undefined' ) {
  
                                                        if( results[0].formatted_address.length > 0) {
                                                                that.weather_options.city = results[0].formatted_address;
                                                        }
                                                        that.weather_options.lat = results[0].geometry.location.lat();
                                                        that.weather_options.lon = results[0].geometry.location.lng();
                                                        that.weather_options.units = units.val() == 'imperial' ? 'us' : 'si';
                                                        that._settings.type = 'search';
                                                        
                                                        that.loading();
                                                        that._newCall = true;
                                                        that.init(that);
                                                } else {
                                                        that.error();
                                                }
                                        });
                                } else {
                                        that.error();
                                }                        }
                        that.closeform(that);
                });
                
                if( this._startInit ) {
                        this.init(that, 'first');
                }
        }
        //http://stackoverflow.com/questions/17104265/caching-a-jquery-ajax-response-in-javascript-browser
        Plugin.prototype = {
                init: function(that, call) {
                        
                        var storage = (typeof(sessionStorage) == undefined) ?
                                (typeof(localStorage) == undefined) ? {
                                    getItem: function(key){
                                        return this.store[key];
                                    },
                                    setItem: function(key, value){
                                        this.store[key] = value;
                                    },
                                    removeItem: function(key){
                                        delete this.store[key];
                                    },
                                    clear: function(){
                                        for (var key in this.store)
                                        {
                                            if (this.store.hasOwnProperty(key)) delete this.store[key];
                                        }
                                    },
                                    store:{}
                        } : localStorage : sessionStorage,
                            url = this._settings.current + this.weather_options.lat + ',' + this.weather_options.lon + '?' + $.param(this.weather_options);
                        
                        // if there's a TTL that's expired, flush this item
                        var ttl = storage.getItem(url + 'cachettl');

                        if ( ttl && ttl < +new Date() ){
                                storage.removeItem( url );
                                storage.removeItem( url + 'cachettl' );
                                ttl = 'expired';
                        }
                         
                        var value = storage.getItem( url );
                        
                        if( value ) {
                                doForecast( JSON.parse( value ) );
                        } else {
                                $.ajax({
                                        url: url,
                                        type: 'GET',
                                        dataType: 'jsonp',
                                        crossDomain: true,
                                        cache: true,
                                        success: function ( response ) {
                                                                
                                                strdata = JSON.stringify( response );
                                                
                                                if( response.error ) {
                                                        that.error();
                                                } else {
                                                        // Save the data to storage catching exceptions (possibly QUOTA_EXCEEDED_ERR)
                                                        try {
                                                                storage.setItem( url, strdata );
                                                                storage.setItem( url + 'cachettl', +new Date() + 1000 * 60 * 60 );
                                                                
                                                        } catch (e) {
                                                                // Remove any incomplete data that may have been saved before the exception was caught
                                                                storage.removeItem( url );
                                                                storage.removeItem( url + 'cachettl' );
                                                                console.log('Cache Error:'+e, url, strdata );
                                                        }
                                                        
                                                        doForecast( response );
                                                }
                                        },
                                        error: function() {
                                                that.error();
                                        }
                                });
                        }
                        
                        function doForecast( forecast ) {
                                        
                                if ( that._newCall != true || call != 'first' ) {
                                       that.success(forecast, function() {
                                                if ( that._hasIframe ) {
                                                        that.resizeVideo(that);       
                                                }
                                        }, call);
                               }
                        }
                },
                success: function(forecast, callback, call) {
                        
                        var that = this,
                            degree = this.weather_options['units'] == 'us' ? 'f' : 'c',
                            temp = forecast.currently.hasOwnProperty('temperature') ? forecast.currently.temperature : '';
                            icon = forecast.currently.hasOwnProperty('icon') ? forecast.currently.icon : 'clear-day',
                            condition = forecast.currently.hasOwnProperty('summary') ? forecast.currently.summary : '',
                            hi_temp = forecast.daily.data[0].hasOwnProperty('temperatureMax') ? Math.round(forecast.daily.data[0].temperatureMax + 0.00001) * 100 / 100 : '',
                            lo_temp = forecast.daily.data[0].hasOwnProperty('temperatureMin') ? Math.round(forecast.daily.data[0].temperatureMin + 0.00001) * 100 / 100 : '',
                            humidity = forecast.currently.hasOwnProperty('humidity') ? Math.round(forecast.currently.humidity * 100) : '',
                            wind = forecast.currently.hasOwnProperty('windSpeed') ? this._getWind(forecast.currently.windBearing) + ' ' + Math.round((forecast.daily.data[0].windSpeed + 0.00001) * 100) / 100 : '',
                            cloudiness = forecast.currently.hasOwnProperty('cloudCover') ? Math.round(forecast.currently.cloudCover * 100) : '0',
                            sunrise = forecast.daily.data[0].hasOwnProperty('sunriseTime') ? this._getTime(forecast.daily.data[0].sunriseTime) : '',
                            sunset = forecast.daily.data[0].hasOwnProperty('sunsetTime') ? this._getTime(forecast.daily.data[0].sunsetTime) : '';

                        if( call == 'first' && that._settings.type == 'location' && typeof( that._settings.heading ) != 'undefined' ) {
                                var location = that._settings.heading;
                        } else if ( typeof( that.weather_options.city ) != 'undefined' && (that.weather_options.city).length > 0 ) {
                                var location = that.weather_options.city;
                        } else {
                                that._geocode(function( address ) {
                                        var location = address == 'error' ? '' : address;
                                        that._loadCurrent({location_class: location});
                                });
                        }
                        
                        if( typeof( location ) != 'undefined' ) {
                                that._loadCurrent({location_class: location});
                        }
                        
                        this._loadCurrent({
                                temp_class: Math.round( temp ),
                                icon_class: this._getCode(icon),
                                condition_class: condition,
                                hi_temp_class: hi_temp,
                                lo_temp_class: lo_temp,
                                humidity_class: humidity,
                                wind_class: wind,
                                cloudiness_class: cloudiness,
                                sunrise_class: sunrise,
                                sunset_class: sunset,
                                unit_class: this.weather_options['unit_' + degree]
                        });
                        
                        this._loadForecast(forecast.daily.data, {
                                fc_hi_temp_class: 'temperatureMax',
                                fc_lo_temp_class: 'temperatureMin',
                                fc_condition_class: 'summary',
                                fc_cloud_class: 'cloudCover',
                                fc_humidity_class: 'humidity'
                        });
                        this.$element.find('.' + this._settings.loading_class + ', .' + this._settings.more_placeholder_class).hide();
                        this.$element.find('.' + this._settings.more_class).removeClass('hide');
                        this.$element.find('.' + this._settings.icon_class).addClass('asterofont');
                        if ( this._hasImage ){
                                this.$background.removeClass (function (index, css) {
                                        return (css.match (/(^|\s)astero-i-\S+/g) || []).join(' ');
                                }).addClass(this._getImage(icon));
                        }
                        
                        if (typeof callback == 'function') { // make sure the callback is a function
                                callback.call(this); // brings the scope to the callback
                        }
                        
                },
                error: function() {
                        this._loadCurrent({
                                temp_class: '',
                                icon_class: astero_fc_vars.na,
                                condition_class: '___',
                                location_class: '______',
                                hi_temp_class: '___',
                                lo_temp_class: '___',
                                humidity_class: '',
                                wind_class: '',
                                cloudiness_class: '',
                                sunrise_class: '',
                                sunset_class: ''
                        });
                        this.$element.find('.' + this._settings.loading_class).hide();
                        this.$element.find('.' + this._settings.more_placeholder_class).show();
                        this.$element.find('.' + this._settings.more_class).addClass('hide');
                        this.$element.find('.' + this._settings.icon_class).removeClass('asterofont');
                },
                loading: function() {
                        this._loadCurrent({
                                temp_class: '',
                                icon_class: '',
                                condition_class: '___',
                                location_class: '______',
                                hi_temp_class: '___',
                                lo_temp_class: '___',
                                humidity_class: '',
                                wind_class: '',
                                cloudiness_class: '',
                                sunrise_class: '',
                                sunset_class: '',
                                unit_class: '&deg;'
                        });
                        
                        var fc_classes = {
                                fc_hi_temp_class: '',
                                fc_lo_temp_class: '',
                                fc_condition_class: '',
                                fc_cloud_class: '',
                                fc_humidity_class: '',
                        }
                        for ( var i = 0; i < this.forecast_options.cnt; i++ ) {
                                for ( cl in fc_classes ) {
                                       this.$element.find('.' + this._settings[cl] + i).html(fc_classes[cl]); 
                                }
                        }
                        
                        this.$element.find('.' + this._settings.loading_class).show();
                        this.$element.find('.' + this._settings.more_placeholder_class).show();
                        this.$element.find('.' + this._settings.more_class).addClass('hide');
                        this.$element.find('.' + this._settings.icon_class).removeClass('asterofont');
                },
                enlarge: function(that){
                        that.$element.addClass('open');
                        that.$element.parents().css('z-index', '999');
                        
                        if ( that._hasIframe ) {
                                that.resizeVideo(that);       
                        }
                        that._disableScroll();
                        
                },
                close: function(that) {
                        that.$element.removeClass('open');
                        that.$element.parents().css('z-index', '');
                        
                        if ( that._hasIframe ) {
                                that.resizeVideo(that);       
                        }
                        that._enableScroll();
                },
                openform: function(that) {
                        that.$element.addClass('astero-openform');
                },
                closeform: function(that) {
                        that.$element.removeClass('astero-openform');
                },
                resizeVideo: function (that) {
                        
                        // reset video container first
                        that.$background.css('height','100%');
                        
                        var iframe = that.$element.find('.astero-yt'),
                            width = that.$element.outerWidth(),
                            ratio = parseFloat(that._settings.iframe_ratio);
                            
                        if ( that.$element.hasClass('open') ) {
                                width = that.$large.outerWidth();
                                height = that.$large.outerHeight();
                                
                                 that.$background.css('height',height);

                        } else {
                                var height = that.$element.outerHeight();
                        }

                        if ( width / height <= ratio ) {
                                iframe.css({height: height, width: height * ratio});
                        } else {
                                iframe.css({width: width, height: width / ratio});
                        }
                },
                setEQ: function(that) {
                        var width = that.$element.outerWidth() / parseFloat(that.$element.css('font-size'));
                        that.$large.removeClass('astero-eq-large astero-eq-small astero-eq-medium astero-eq-xsmall');
                        
                        if ( width >= 59.077 ) {
                                that.$large.addClass('astero-eq-large');
                        } else if ( width < 59.077 && width >= 34.615 ) {
                                that.$large.addClass('astero-eq-medium');
                        } else if ( width < 34.615 && width >= 15.385 ) {
                                that.$large.addClass('astero-eq-small');
                        } else if ( width < 15.385 ) {
                                that.$large.addClass('astero-eq-xsmall');
                        }
                },
                _disableScroll: function() {
                        $('html').addClass('astero-open');   
                        if ($(document).height() > $(window).height()) {
                                $('html').css('top',-$( window ).scrollTop()).addClass('astero-noscroll');         
                        }
                },
                _enableScroll: function() {
                        var scrollTop = parseInt($('html').css('top'));
                        $('html').removeClass('astero-noscroll astero-open');
                        $('html,body').scrollTop(-scrollTop);
                },
                _getCode: function(icon) {
                        if ( this.codes.hasOwnProperty(icon) ) {
                                return this.codes[icon];
                        }
                        return this.codes['clear-day'];
                },
                _getImage: function(weather) {
                        if ( this._default_imgs.hasOwnProperty(weather) ) {
                                return this._default_imgs[weather];
                        }
                        
                        return this._default_imgs['clear-day'];
                },
                _loadCurrent: function( data ) {
                        for (var el in data) {
                                this.$element.find('.' + this._settings[el]).html(data[el]);
                        }
                },
                _loadForecast: function( forecasts, data ) {
                        var k = 0;
                        
                        for ( var i = 1; i < this.forecast_options.cnt; i++ ) {
                                
                                for (var el in data) {
                                        var prop = forecasts[i][data[el]];
                                        
                                        if( el == 'fc_cloud_class' || el == 'fc_humidity_class' ) {
                                                prop = prop * 100;
                                        }
                                        
                                        if ( typeof prop == 'number' ) {
                                                prop = Math.round((prop + 0.00001)) * 100 / 100;
                                        }

                                        this.$element.find('.' + this._settings[el] + k).html(prop);
                                }
                                
                                //load icons
                                this.$element.find('.' + this._settings.fc_icon_class + k).html(this._getCode(forecasts[i]['icon']));
                                k++;
                        }
                        
                },
                _getWind: function(degrees) {
                        var wind = [astero_fc_vars.n, astero_fc_vars.nne, astero_fc_vars.ne, astero_fc_vars.ene, astero_fc_vars.e, astero_fc_vars.ese, astero_fc_vars.se, astero_fc_vars.sse,
                                    astero_fc_vars.s, astero_fc_vars.ssw, astero_fc_vars.sw, astero_fc_vars.wsw, astero_fc_vars.w, astero_fc_vars.wnw, astero_fc_vars.nw, astero_fc_vars.nnw];
                        return wind[ parseInt((degrees/22.5)+.5) % 16 ];
                },
                _getTime: function(unix) {
                        var d = new Date(unix*1000);
                        
                        var h = d.getHours(),
                            m = ('0' + d.getMinutes()).slice(-2),
                            ampm = astero_fc_vars.am;
                            
                        if (h > 12) {
                                h = h - 12;
                                ampm = astero_fc_vars.pm;
                        } else if (h == 0) {
                                h = 12;
                        }
                        
                        return h + ':' + m + ampm;
                },
                _geocode: function(callback) {
                        var geocoder = new google.maps.Geocoder(),
                            latlng = new google.maps.LatLng(this.weather_options.lat,this.weather_options.lon);

                        if (geocoder) {
                                geocoder.geocode({ 'location': latlng }, function (results, status) {
                                        if (status == "OK") {
                                                if (results.length > 1) {
							result = results[1];
						} else {
							result = results[0];
						}
                                                callback( result['formatted_address'] );
                                        } else {
                                                callback( 'error' );
                                        }
                                });
                        } else {
                               callback( 'error' );
                        }
                }
        };
        
        $.fn[pluginName] = function ( options ) {

                return this.each(function () {
                        if (!$.data(this, "plugin_" + pluginName)) {
                                var objD = $(this).data(pluginName);
                                
                                $.data(this, "plugin_" + pluginName,
                                new Plugin( this, options, objD ));
                        }
                });
        };

	$(document).ready(function() {
		$('.astero-forecast').astero_fc();
	});
        
})( jQuery, window, document );

var playerlist = document.querySelectorAll(".astero-yt");

if( playerlist.length > 0 ) {
        var tag = document.createElement('script');

        tag.src = "//www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
}

function onYouTubeIframeAPIReady() {

        for(var i = 0; i < playerlist.length; i++) {
                var curplayer = createPlayer(playerlist[i].getAttribute('id'), playerlist[i].getAttribute('data-videoid'));
        }   
}

function createPlayer(id, videoid) {
        return new YT.Player(id, {
                playerVars: {
                        autoplay: 1,
                        showinfo: 0,
                        controls: 0,
                        modestbranding: 1,
                        rel: 0,
                        loop: 1,
                        div_load_policy: 3,
                        playlist: videoid,
                        wmode: 'transparent',
                        origin: document.location.origin
                },
                allowfullscreen: 0,
                videoId: videoid,
                events: {
                    'onReady': onPlayerReady
                }
        });
}

function onPlayerReady(event) {
        event.target.mute();
        event.target.playVideo();
}