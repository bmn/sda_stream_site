<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"> 
<html>
  <head>
    <title>Speed Demos Archive Live Streamers</title>
    <link href="assets/style.css" type="text/css" rel="stylesheet" media="screen"/>
  </head>
  <body>
    <?php
      require_once 'latest_update.php';
      SDAExceptions::set_error_level(E_USER_NOTICE);
      $update = SDALatestUpdate::get()->results;
    ?>
    <div id="latest"><a class="important" href="http://www.speeddemosarchive.com"><?php echo date('F jS', $update['date']); ?></a>: 
    <?php
      $games = array();
      foreach ($update['games'] as $g) {
        $games[] = "<a href=\"http://www.speeddemosarchive.com/{$g['path']}\">{$g['title']}</a>";
      }
      echo implode($games, ', ');
    ?>
    </div>
    
    <div id="wrapper">
    
      <h1><a href="http://www.speeddemosarchive.com">Speed Demos Archive</a> Ustreamers</h1>
      <!--
      <h2>New Year Poll &nbsp;&nbsp;&nbsp;<a href="javascript:$('#poll').toggle()">&#10063;</a></h2>
      <div id="poll">
        <p>The &#10063; boxes on this page show and hide the video stream embeds - the one at the top right affects all the streams on the page. How do you think it should work?</p>
        <?php /*
          require_once('poll/poll.php');
          show_vote_control('1');
          show_vote_control('2'); */
        ?>
      </div>
      -->
      <?php
        require_once 'config.php';
        if ( (!is_array($channels)) && (!is_array($apis)) )
          die('Config not provided by config.php');
        $streams = SDAStream::get(array(
          'channels'    => $channels,
          'apis'        => $apis,
          'ttl'         => $ttl,
          'callback'    => $callback,
          'include'     => $include,
          'add'         => $add,
          'default'     => $default,
          'api'         => $api,
          'default_api' => $default_api,
          'single'      => $single,
          'raw'         => $raw,
        ))
          ->set_embed_dimensions(320, 260)
          ->sort('return strcasecmp($a["user_name"], $b["user_name"])', true);
        $online = $streams->filter('return ($a["online"])');
        $offline = $streams->filter('return (!$a["online"])');
        $online_ct = count($online);
      ?>
      <h2>Streaming Now...</h2>
      <?php if (count($online) == 0): ?>
        No-one streaming right now.
      <?php endif ?>
      <div id="online"<?php if ($_COOKIE['hide_embed'] == 1) { ?> class="hidden"<?php } ?>>
        <?php
          foreach($online as $entry) {
            $entry['class'] = $entry['api'].'_'.str_replace("'", '-', $entry['channel_name']);
            print <<<HTML
        <div class="entry {$entry['class']}">
          <h3><a href="{$entry['channel_url']}">{$entry['user_name']}</a> <a class="toggle" href="javascript:sda.toggle_embed('{$entry['class']}')" title="Show/Hide Embed">&#10063;</a></h3>
          {$entry['embed_stream']}
          <div class="synopsis">{$entry['synopsis']}</div>
        </div>
HTML;
          }
        ?>
      </div>
      <h2>Lazy Bums...</h2>
      <div id="offline">
        <?php
          $content = array();
          foreach ($offline as $entry) {
            $entry['class'] = $entry['api'].'_'.str_replace("'", '-', $entry['channel_name']);
            print <<<HTML
            <span class="entry {$entry['class']}"><a href="{$entry['channel_url']}" title="{$entry['synopsis']}">{$entry['user_name']}</a></span>
HTML;
          }
        ?>
      </div>
      
    </div>
    
    <div id="toggle">
      <a href="javascript:sda.toggle_embed()" title="Show/Hide All Embeds">&#10063;</a>
    </div>

    <div id="about">
      <h1>More Info</h1>
      <div class="full">
        <p>This is a listing page that contains links to, and embedded movies within the page of, outside content that is outside of the control of the page maintainer.</p>
        <p>The streams shown on this page are run by members of <a href="http://www.speeddemosarchive.com">Speed Demos Archive</a> who have opted to be listed here. If you're an SDA member and want to be included, your synopsis changed, or even your stream removed from the list entirely, contact <a href="http://forum.speeddemosarchive.com/profile/bmn.html">bmn</a>.</p>
        <p>The username in bold on each stream is a link to its channel, which has a larger player and also includes a chatroom. Most streamers appreciate viewers chatting with them, so feel free to click on the username if you find a stream you like. There's no requirement that someone has to be speedrunning, so you may well see normal gameplay, a pause screen or something totally unrelated.</p>
        <p>The &#10063; boxes on the screen toggle between showing and hiding the Flash streams on this page. The one at the top corner affects all the streams, and the one above each stream affects that stream only. Your setting is saved in a cookie; you can use this if Flash slows your computer too much.</p>
        <p>The status updates every 30 seconds; this is shown at the bottom-right corner of the screen. If your Internet connection is lost, it may get stuck loading (the circular icon). If this happens, please refresh the page.</p>
        <p>Ustream.tv and Justin.tv are supported by this page. If you stream on another site, feel free to ask if it can be added. This depends on the site's API functionality, and how open it is, however, so it may not be possible.</p>
        <p>This page uses the <a href="https://github.com/bmn/sda_stream">sda_stream</a> library. Created by Ian "bmn" Bennett 2010-11. If you see "gogobmn" streaming, come say hi sometime...</p>
      </div>
    </div>
    <div id="debug">
      <h1>Debug (<span id="timer"></span>)</h1>
      <div class="full">
        <p>Main Page Load:</p>
        <?php foreach (SDAExceptions()->exceptions as $e): ?>
          <p class="e<?php echo $e->getCode() ?>"><?php echo $e->getMessage() ?></p>
        <?php endforeach ?>
      </div>
    </div>

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="assets/jquery.jsonp-2.1.4.min.js"></script>
    <script type="text/javascript" src="assets/jquery.countdown.pack.js"></script>
    <script type="text/javascript" src="assets/jquery.cookie.js"></script>
    <script type="text/javascript" src="assets/google-analytics.js"></script>
    <script type="text/javascript" src="assets/sda_stream.js"></script>
    <script type="text/javascript">
      sda = new sda_stream();
    </script>

  </body>
</html>
