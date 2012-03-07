<?php

// Input
$api = $_GET['api'];
$channel = $_GET['channel'];
if (!is_string($api)) die('Invalid API input');
if (!is_string($channel)) die('Invalid Channel input');

// Load the API class
$path = dirname(__FILE__).'/sda_stream2/sda_stream_'.$api.'.php';
if (!is_readable($path)) die('Invalid API');
require_once($path);
$class = 'SDAStream'.ucfirst($api);

$embed_channel = call_user_func(array($class, 'embed_channel'), $channel);
$embed_chat = call_user_func(array($class, 'embed_chat'), $channel);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
  <head>
    <link rel="shortcut icon" href="/sda/favicon.ico" type="image/x-icon" />
    <link href="assets/watch.css" type="text/css" rel="stylesheet" media="screen"/>
  </head>
  <body class="<?php echo $api ?>">
    <table cellspacing="0">
      <tr>
        <td id="channel">
          <?php echo $embed_channel ?>
        </td>
        <td id="chat">
          <?php echo $embed_chat ?>
        </td>
      </tr>
    </table>
  </body>
</html>