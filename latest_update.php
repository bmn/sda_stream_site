<?php

require_once 'sda_stream2/sda_stream.php';

class SDALatestUpdate {

  private $callback;
  public $results;
  
  public function get($callback = 'sda_latest_update') {
    
    // Return an instance if called statically
    if (!isset($this)) {
      $out = new self;
      return $out->get($callback);
    }
    $this->callback = $callback;
    
    // Try to read the cache (15 minutes)
    $data = SDAStream::read_cache($callback, 15, 'jsonp');
    if ($data) {
      $this->results = $data;
      return $this;
    }

    // Else go get
    $page = SDAStream::get_sock('http://speeddemosarchive.com/index.html');

    // Get the update
    if (!preg_match('/<p class="d"><b>\w+?, (\w+?) (\d{1,2}), (\d{4})<\/b> by (.+?)<\/p>/', $page, $latest)) die(); // error stuff here

    // Get the games
    if (!preg_match('/<div class="b">.*?<\/div>/s', $page, $content)) die(); // error stuff here
    if ( ($ct = preg_match_all('/<a href="(\w+?\.html)">(.+?)<\/a>/m', $content[0], $games)) === false ) die();
    $games2 = array();
    for ($i = 0; $i < $ct; $i++) $games2[] = array('path' => $games[1][$i], 'title' => $games[2][$i]);

    // Output
    $this->results = array(
      'date'    => strtotime("{$latest[2]} {$latest[1]} {$latest[3]} 12:00:00"),
      'author'  => $latest[4],
      'games'   => $games2,
    );
    SDAStream::write_cache($callback, $this->results);
    return $this;
  }
  
  public function output($format = 'jsonp') {
    return SDAStream::serialize($this->results, $format, $this->callback);
  }
  
}

if (reset(get_included_files()) == __FILE__) {
  print SDALatestUpdate::get()->output();
}
