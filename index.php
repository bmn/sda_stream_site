<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"> 
<html>
  <head>
    <title>Speed Demos Archive Live Streamers</title>
    <link rel="shortcut icon" href="/sda/favicon.ico" type="image/x-icon" />
    <link href="assets/style.css" type="text/css" rel="stylesheet" media="screen"/>
  </head>
  <body>
    <?php
      require_once 'config.php';
      foreach (SDAStream::$options as $var) if (!isset($$var)) $$var = null;
      if ( (!is_array($channels)) && (!is_array($apis)) )
        die('Config not provided by config.php');
      $streams = SDAStream::get(array(
        'channels'    => $channels,
        'apis'        => $apis,
        'ttl'         => $ttl,
        'callback'    => $callback,
        'include'     => $include,
        'api'         => $api,
        'default_api' => $default_api,
        'single'      => $single,
        'raw'         => $raw,
        'post'        => $post,
      ))
        ->sort('return strcasecmp((isset($a["screenname"]) ? $a["screenname"] : $a["user_name"]), (isset($b["screenname"]) ? $b["screenname"] : $b["user_name"]))', true);
      $online = $streams->filter('return ($a["online"])');
      $online_ct = count($online);
      $update = $output['sda'];
    ?>
    <div id="latest"><a class="important date" href="http://www.speeddemosarchive.com"><?php echo date('F jS', $update['date']); ?></a>: <span class="games">
    <?php
      $games = array();
      foreach ($update['games'] as $g) {
        $games[] = "<a href=\"http://www.speeddemosarchive.com/{$g['path']}\">{$g['title']}</a>";
      }
      echo implode($games, ', ');
    ?>
    </span></div>
    
    <div id="wrapper">
    
      <h1><a href="http://www.speeddemosarchive.com">Speed Demos Archive</a> Live Streams</h1>
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
      <h2>Streaming Now...</h2>
      <div id="no1here"<?php if ($online_ct > 0) echo ' class="hidden"'; ?>>
        No-one streaming right now.
      </div>
      <div id="online"<?php if (!empty($_COOKIE['hide_embed'])) { ?> class="hidden"<?php } ?>>
        <?php
          foreach($online as $entry) {
            if (empty($entry['screenname'])) $entry['screenname'] = $entry['user_name'];
            $entry['class'] = $entry['api'].'_'.str_replace("'", '-', $entry['channel_name']);
            $entry['embed_id'] = ($entry['api'] == 'ustream') ? $entry['channel_id'] : $entry['channel_name'];
            print <<<HTML
        <div class="entry {$entry['class']}">
          <h3><a href="{$entry['channel_url']}">{$entry['screenname']}</a> <a class="icon toggle" href="javascript:sda.toggle_embed('{$entry['class']}')" title="Show/Hide Embed"></a><a class="icon popout" href="javascript:sda.popout('{$entry['api']}', '{$entry['embed_id']}')" title="Popout Stream/Chat"></a></h3>
          {$entry['embed_stream']}
          <div class="synopsis">{$entry['synopsis']}</div>
        </div>
HTML;
          }
        ?>
      </div>
      <h2>Currently Offline...</h2>
      <div id="offline">
        <?php
          $content = $startup = array();
          foreach ($streams->results as $entry) {
            if (empty($entry['screenname'])) $entry['screenname'] = $entry['user_name'];
            $entry['class'] = $entry['api'].'_'.str_replace("'", '-', $entry['channel_name']);
            $hidden = ($entry['online']) ? ' hidden' : '';
            $startup[$entry['class']] = ($entry['online']);
            print <<<HTML
            <span class="entry {$entry['class']}{$hidden}"><a href="{$entry['channel_url']}" title="{$entry['synopsis']}">{$entry['screenname']}</a></span>
            
HTML;
          }
        ?>
      </div>
      
    </div>
    
    <div id="toggle">
      <a class="icon updates<?php if ($_COOKIE['no_updates'] == 1) { ?> disable<?php } ?>" href="javascript:sda.toggle_updates()" title="Enable/Disable Automatic Updates"></a>
      <a class="icon toggle" href="javascript:sda.toggle_embed()" title="Show/Hide All Embeds"></a>
    </div>

    <div id="about">
      <h1>More Info</h1>
      <div class="full">
        <p>The streams shown on this page are run by members of <a href="http://www.speeddemosarchive.com">Speed Demos Archive</a> who have opted to be listed here. Individual channels might not stream speedrunning content at all times; their content is controlled by the channel owner, and not SDA or the streaming service provider.</p>
        <p>Click on a username or the player itself to open the full page for that channel. This icon: <a class="icon popout black"></a> will open a popout with the stream and its chat - you can use F11 to make it fullscreen. This icon: <a class="icon toggle black"></a> toggles between showing and hiding the Flash stream - the one at the top right affects all streams on the page, and the current setting is stored between sessions.</p>
        <p>The status updates every 60 seconds; this is shown at the bottom-right corner of the screen. You can disable this behaviour by clicking this icon at the top right of the page: <a class="icon updates black"></a> so it is no longer green: <a class="icon updates disable black"></a>. In some cases an update can get stuck at 0 seconds - if this happens please refresh the page or click the above icon twice to reset the timer.</p>
        <p>The top left of the page shows a summary of the latest update on the main SDA site. Click the date to read the full update, or a game title to go to that game's page on SDA. This section updates every 15 minutes.</p>
        <p>This page supports the following providers: <a href="http://www.hitbox.tv">Hitbox</a>, <a href="http://www.twitch.tv">Twitch</a>, <a href="http://www.ustream.tv">Ustream</a>. We may add support for other providers if they provide suitable API functionality and there is demand from channel owners. As of July '12, <a href="http://www.livestream.com">Livestream</a> and <a href="http://www.own3d.tv">own3d</a> do not provide suitable APIs.</p>
        <p>If you're an SDA member and want to be included, your synopsis changed, or even your stream removed from the list entirely, <a href="http://forum.speeddemosarchive.com/post/live_stream_status__w00ty.com_updated_info_16_nov_11.html">full details are available in this forum topic</a>.</p>
        <p><a href="https://github.com/bmn/sda_stream_site">This page</a> uses the <a href="https://github.com/bmn/sda_stream2">sda_stream2</a> library. Copyright &copy; Ian "bmn" Bennett 2010-12 and provided under a <a href="http://creativecommons.org/licenses/by-sa/2.0/uk/">Creative Commons Attribution-Share Alike Licence</a>.</p>
      </div>
    </div>
    <div id="debug">
      <h1>Debug (<span id="timer"></span>)</h1>
      <div class="full">
        <p>Main Page Load:</p>
        <?php foreach (SDAExceptions()->exceptions as $e): ?>
          <p class="e<?php echo $e->getCode() ?>"><?php echo $e->getMessage() ?></p>
        <?php endforeach ?>
        <?php if ($streams->log): ?>
          <p>Cache Generation:</p>
          <?php foreach ($streams->log as $e): ?>
            <p class="e<?php echo $e['level'] ?>"><?php echo $e['message'] ?></p>
          <?php endforeach ?>
        <?php endif ?>
      </div>
    </div>

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="assets/jquery.jsonp-2.1.4.min.js"></script>
    <script type="text/javascript" src="assets/jquery.countdown.pack.js"></script>
    <script type="text/javascript" src="assets/jquery.cookie.js"></script>
    <script type="text/javascript" src="assets/jquery.visibility.min.js"></script>
    <script type="text/javascript" src="assets/google-analytics.js"></script>
    <script type="text/javascript" src="assets/sda_stream.js"></script>
    <script type="text/javascript">
      sda = new sda_stream(
        //{'url':'http://www.w00ty.com/sda/stream/sda_stream2/cache/sda_stream.js'}
      );
      sda.listed = <?php echo json_encode($startup); ?>;
      sda.update_sda_date = <?php echo $update['date']; ?>;
    </script>

  </body>
</html>
