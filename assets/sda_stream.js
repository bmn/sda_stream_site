function sda_stream() {
  // Update the site status
  this.update_sda = function() {
    $.jsonp({
      'url':      'latest_update.php',
      'callback': 'latest_update',
      'timeout':  30000,
      'context':  this,
      'complete': function() { this.reset_timer(this.element.timer_sda, this.update_sda_timeout); },
      'error':    function(opts, status) {
        if (status == 'timeout') error = 'Auto-update timed out.';
        else if (status == 'error') error = 'Problem loading auto-update.';
      },
      'success':  function(json, status) {
      }
    });
  }

  // Update the stream status
  this.update_stream = function() {
    $.jsonp({
      'url':      'sda_stream2/sda_stream.php',
      'callback': 'sda_stream',
      'timeout':  15000,
      'context':  this,
      'complete': function() { this.reset_timer(this.element.timer, this.update_timeout); },
      'error':    function(opts, status) {
        if (status == 'timeout') error = 'Auto-update timed out.';
        else if (status == 'error') error = 'Problem loading auto-update.';
        this.element.debug_log.html('<p class="e512">' + error + 'Trying again in ' + this.update_timeout + ' seconds.</p>');
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
  
  // Set the width of the online area
  this.set_online_width = function(ct) {
    if (!ct) ct = 0;
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
    var i, ar, l, log, cls, error, online;
    log = '';
    
    // Update streams
    ar = json.results;
    if (typeof(ar) == 'object') {
      for (i = 0; i < ar.length; i++) {
        l = ar[i];
        cls = l.api + '_' + this.clean(l.channel_name);
        cls = l.api + '_' + l.channel_name;
        if (
          ($('#online').has('div.' + cls).length) &&
          (l.online == false)
        ) {
          $('#offline').prepend('<span class="entry ' + cls + '"><a href="' + l.channel_url + '" title="' + l.synopsis + '">' + l.user_name + '</a></span>');
          $('#online div.' + cls).remove();
          log = '<p class="e1024">' + l.user_name + ' has gone offline.</p>\n';
        }
        else if (
          ($('#offline').has('span.' + cls).length) &&
          (l.online == true)
        ) {
          $('#online').prepend('<div class="entry ' + cls + '"><h3><a href="' + l.channel_url + '">' + l.user_name + '</a> <a class="toggle" href="javascript:sda.toggle_embed(\'' + cls + '\')" title="Show/Hide Embed">&#10063;</a></h3>' + l.embed_stream + '<div class="synopsis">' + l.synopsis + '</div></div>');
          $('#offline span.' + cls).remove();
          log = '<p class="e1024">' + l.user_name + ' has come online.</p>';
        }
      }
      this.set_online_width($('#online > div').length);
    }
    
    // Update the log
    ar = json.log;
    if (typeof(ar) == 'object') {
      for (i = 0; i < ar.length; i++) {
        log = log+ '<p class="e' + ar[i].level + '">' + ar[i].message + '</p>\n';
      }
    }
    if (log == '') log = '<p class="e1024">No errors.</p>\n';
    this.element.debug_log.html('<p>Auto-Update:</p>\n' + log);
    this.element.debug.removeClass('error');
  };
  
  // Set us up
  this.update_timeout = 30;
  this.update_sda_timeout = 15*60;
  this.element = {
    'debug':      $('#debug'),
    'debug_log':  $('#debug div.full'),
    'timer':      $('#timer'),
    'timer_sda':  $('#timer_sda')
  };
  this.set_online_width($('#online > div').length);
  this.element.timer.countdown({until: +this.update_timeout, compact: true, format: 'MS', layout: '{snn}', onExpiry: $.proxy(this, 'update_stream')});
  //this.element.timer_sda.countdown({until: +this.update_sda_timeout, compact: true, format: 'MS', layout: '{mn}:{snn}', onExpiry: $.proxy(this, 'update_sda')});
  
}
