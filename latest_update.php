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
    if ($this->read_cache(15)) return $this;

    // Else go get
    $page = SDAStream::get_sock('http://speeddemosarchive.com/index.html') or $this->fail(true);

    // Get the update
    preg_match('/<p class="d"><b>\w+?, (\w+?) (\d{1,2}), (\d{4})<\/b> by (.+?)<\/p>/', $page, $latest) or $this->fail();

    // Get the games
    preg_match('/<div class="b">.*?<\/div>/s', $page, $content) or $this->fail(); 
    if ( ($ct = preg_match_all('/<a href="(?:http\:\/\/(?:www\.)?speeddemosarchive\.com)?\/?(\w+?\.html)">(.+?)<\/a>/m', $content[0], $games)) === false ) $this->fail();
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
  
  private function read_cache($timeout = null) {
    $data = SDAStream::read_cache($this->callback, $timeout, 'jsonp');
    if ($data) {
      $this->results = $data;
      return true;
    }
    return false;
  }
  
  private function fail($http = false) {
    // First attempt to read the cache again, with no limit on date
    $success = $this->read_cache();
    // Reset the timeout if the cache was read successfully, or if the issue was caused by HTTP failure
    if ($success || $http) SDAStream::write_cache($this->callback, null, 'jsonp', true);
    // Write a skeleton cache if the issue was caused by the markup on SDA
    else {
      $this->results = array(
        'date'    => 0,
        'author'  => 'No one',
        'games'   => array(),
      );
      SDAStream::write_cache($this->callback, $this->results);
    }
    return $this;
  }
  
}

if (reset(get_included_files()) == __FILE__) {
  print SDALatestUpdate::get()->output();
}
