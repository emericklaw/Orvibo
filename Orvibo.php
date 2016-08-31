<?php

/*
The MIT License (MIT)

Copyright (c) 2015 Phil Parsons <phil@parsons.uk.com>
              2016 Matthew Emerick-Law <matt@emericklaw.co.uk>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

class Orvibo
{
  
  private $host;
  private $port;
  private $mac;
  private $delay = 10000; //microseconds
  private $retryCount;
  private $subscribed=false;
  private $twenties = array(0x20,0x20,0x20,0x20,0x20,0x20);
  private $zeroes = array(0x00,0x00,0x00,0x00);
  
  public function __construct($host = '10.10.100.254', $port = 10000, $mac, $retryCount=5)
  {	 
   $this->host = $host;
   $this->port = $port;
   $this->retryCount = $retryCount;
   $this->mac = $mac;
   if ($this->subscribed === false) {
      $this->subscribe();
    }
   }

  public function getDelay()
  {
    return $this->delay;
  }

  private function subscribe() {
    $command = array(0x68,0x64,0x00,0x1e,0x63,0x6c);
    $command = array_merge($command, $this->mac, $this->twenties, array_reverse($this->mac),
			   $this->twenties);
    $this->sendCommand($command);
    $this->subscribed=true;
  }

  public function on() {
   if ($this->subscribed === false) {
      $this->subscribe();
    }
    $command = array(0x68,0x64,0x00,0x17,0x64,0x63);
    $command = array_merge($command, $this->mac, $this->twenties, $this->zeroes, array(0x01));
    $this->sendCommand($command);    
  }
  
  public function off() {
   if ($this->subscribed === false) {
      $this->subscribe();
    }
    $command = array(0x68,0x64,0x00,0x17,0x64,0x63);
    $command = array_merge($command, $this->mac, $this->twenties, $this->zeroes, array(0x00));
    $this->sendCommand($command);    
  }

  public function status($listen_host) {
   if ($this->subscribed === false) {
      $this->subscribe();
    }
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>1,"usec"=>0));
    socket_bind($socket, $listen_host, $this->port);
    $command = array(0x68,0x64,0x00,0x1e,0x63,0x6c);
    $command = array_merge($command, $this->mac, $this->twenties, array_reverse($this->mac), $this->twenties);
    $this->sendCommand($command);
    $from = '';
    socket_recvfrom($socket, $buf, 1000, 0, $from);
    return substr($this->ascii2hex($buf),-2,1);
  }
  
  public function sendCommand(Array $command)
  {
    $message = vsprintf(str_repeat('%c', count($command)), $command);
    for ($try=0;$try<$this->retryCount;$try++) {
      if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
        socket_sendto($socket, $message, strlen($message), 0, $this->host, $this->port);
        socket_close($socket);
        usleep($this->getDelay()); //wait 100ms before sending next command
      }
    }
  }

  public function ascii2hex($ascii) {
    $hex = '';
    for ($i = 0; $i < strlen($ascii); $i++) {
      $byte = strtoupper(dechex(ord($ascii{$i})));
      $byte = str_repeat('0', 2 - strlen($byte)).$byte;
      $hex.=$byte." ";
    }
    return $hex;
  }
}