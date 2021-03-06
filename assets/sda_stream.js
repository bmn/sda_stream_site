function sda_stream(o) {

  var self = this;

  // Update the stream status
  this.update_stream = function() {
    if (this.blurred) {
      this.load_on_focus = true;
      return false;
    }
    $.jsonp({
      'url': this.url,
      'callback': 'sda_stream',
      'timeout':  15000,
      'context':  this,
      'complete': function() { this.reset_timer(this.element.timer, this.update_timeout); },
      'error':    function(opts, status) {
        if (status == 'timeout') error = 'Auto-update timed out.';
        else if (status == 'error') error = 'Problem loading auto-update.';
        this.element.debug_log.html('<p class="e512">' + error + ' Trying again in ' + this.update_timeout + ' seconds.</p>');
        this.element.debug.addClass('error');
      },
      'success':  this.callback.update_stream.success
    });
  };
  
  // Set the visibility of the embeds
  this.toggle_embed = function(id) {
    if (!id) {
      if ($.cookie('hide_embed') == 1) {
        $('#online').removeClass('hidden');
        $.cookie('hide_embed', 0, {expires: 9999});
      } else {
        $('#online').addClass('hidden');
        $.cookie('hide_embed', 1, {expires: 9999});
      }
      $('.entry').removeClass('alternate');
    }
    else $('.entry.'+id).toggleClass('alternate');
  };

  // Enable/disable autoupdates
  this.toggle_updates = function() {
    if ($.cookie('no_updates') == 1) {
      this.update_stream();
      this.element.timer.countdown('resume');
    } else {
      this.element.timer.countdown('pause');
    }
    $('#toggle a.updates').toggleClass('disable');
    $.cookie('no_updates', ($.cookie('no_updates') == 1) ? 0 : 1, {expires: 9999});
  };

  // Pop out stream and chat
  this.popout = function(api, channel) {
    if ( (!api) || (!channel) ) return false;
    var url = 'watch.php?api='+api+'&channel='+channel;
    var wname = 'sda_stream_popout_'+api+'_'+this.clean(channel);
    var opts = 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=854,height=480';
    window.open( url, wname, opts );
  }
  
  // Set the width of the online area
  this.set_online_width = function() {
    var ct = $('#online > div').length;
    this.entry_width = 332;
    this.window_width = self.innerHeight ? self.innerWidth : (document.documentElement && document.documentElement.clientHeight) ? document.documentElement.clientWidth : document.body.clientWidth;
    this.max_per_row = Math.floor(this.window_width / this.entry_width);
    $('#online').width( Math.min(ct, this.max_per_row) * this.entry_width);
  };
  
  // Reset the timer
  this.reset_timer = function(element, timer) {
    element.countdown('change', {until: +timer});
    return true;
  };

  // Clean up channel names
  this.clean = function(c) {
    return c.replace(/[^a-z0-9_-]/i, '-');
  };
  
  this.callback = {'update_stream': {}};
  // Stream update (success)
  this.callback.update_stream.success = function(json, status) {
    var i, ar, l, log, cls, error, online, on_exist, off_exist, upDate, games, embed_id;
    log = '';
    
    // Update streams
    ar = json.results;
    if (typeof(ar) == 'object') {
      for (i = 0; i < ar.length; i++) {
        l = ar[i];
        cls = l.api + '_' + this.clean(l.channel_name);
        was_online = this.listed[cls];
        if (!l['screenname']) l.screenname = l.user_name;
        if (typeof was_online == 'undefined') {
          $('#offline').prepend('<span class="entry new ' + cls + '"><a href="' + l.channel_url + '" title="' + l.synopsis + '">' + l.screenname + '</a></span>');
          was_online = false;
        }
        if ( (l.online == false) && (was_online == true) )  {
          $('#online div.' + cls).remove();
          $('#offline span.' + cls).removeClass('hidden');
          log = log + '<p class="e1024">' + l.user_name + ' has gone offline.</p>\n';
        }
        else if ( (l.online == true) && (was_online == false) )  {
          embed_id = (l.api == 'ustream') ? l.channel_id : l.channel_name;
          $('#online').prepend('<div class="entry ' + cls + '"><h3><a href="' + l.channel_url + '">' + l.screenname + '</a> <a class="icon toggle" href="javascript:sda.toggle_embed(\'' + cls + '\')" title="Show/Hide Embed"></a><a class="icon popout" href="javascript:sda.popout(\'' + l.api + '\', \'' + embed_id + '\')" title="Popout Stream/Chat"></a></h3>' + l.embed_stream + '<div class="synopsis">' + l.synopsis + '</div></div>');
          $('#offline span.' + cls).addClass('hidden');
          log = log + '<p class="e1024">' + l.user_name + ' has come online.</p>';
        }
        this.listed[cls] = l.online;
      }
      if ( $('#no1here').hasClass('hidden') != ($('#online > div').length > 0) ) { $('#no1here').toggleClass('hidden'); }
      this.set_online_width();
    }

    // Update SDA news
    ar = json.sda;
    if ( (typeof(ar) == 'object') && (ar.date > this.update_sda_date) ) {
      this.update_sda_ct = 0;
      upDate = new Date(ar.date * 1000);
      l = l % 10;
      if (l == 1) l = 'st';
      else if (l == 2) l = 'nd';
      else if (l == 3) l = 'rd';
      else l = 'th';
      $('#latest').addClass('new');
      $('#latest a.date').html(this.months[upDate.getMonth()] + ' ' + upDate.getDate() + l);
      games = [];
      for (i = 0; i < ar.games.length; i++) {
        l = ar.games[i];
        games.push('<a href="http://www.speeddemosarchive.com/' + l.path + '">' + l.title + '</a>');
      }
      $('#latest span.games').html(games.join(', '));
    }
    
    // Update the log
    ar = json['log'];
    if (typeof(ar) == 'object') {
      for (i = 0; i < ar.length; i++) {
        log = log+ '<p class="e' + ar[i].level + '">' + ar[i].message + '</p>\n';
      }
    }
    ar = json['cached_log'];
    if (typeof(ar) == 'object') {
      log = log + '<p>Cache Generation:</p>\n';
      for (i = 0; i < ar.length; i++) {
        log = log + '<p class="e' + ar[i].level + '">' + ar[i].message + '</p>\n';
      }
    }
    if (log == '') log = '<p class="e1024">No errors.</p>\n';
    this.element.debug_log.html('<p>Auto-Update:</p>\n' + log);
    this.element.debug.removeClass('error');
  };
  
  // Tab blur/focus handlers
  this.callback.onBlur = function(event) {
    self.blurred = true;
  };
  this.callback.onFocus = function(event) {
    self.blurred = false;
    if (self.load_on_focus == true) {
      self.update_stream();
      self.load_on_focus = false;
    }
  };
  
  // Set us up
  var o = (typeof o == 'object') ? o : {};
  this.url = o['url'] || 'sda_stream2/sda_stream.php';
  this.update_timeout = 60;
  this.months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  this.element = {
    'debug':      $('#debug'),
    'debug_log':  $('#debug div.full'),
    'timer':      $('#timer'),
    'timer_sda':  $('#timer_sda')
  };
  this.set_online_width();
  $(window).resize(this.set_online_width);
  this.element.timer.countdown({until: +this.update_timeout, compact: true, format: 'MS', layout: '{snn}', onExpiry: $.proxy(this, 'update_stream')});
  if ($.cookie('no_updates') == 1) this.element.timer.countdown('pause');
  //this.element.timer_sda.countdown({until: +this.update_sda_timeout, compact: true, format: 'MS', layout: '{mn}:{snn}', onExpiry: $.proxy(this, 'update_sda')});
  $.winFocus(this.callback.onBlur, this.callback.onFocus);
  
}
