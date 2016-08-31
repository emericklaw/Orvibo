Orvibo S20 smart socket PHP API
=============

This code base started from [my project](https://github.com/pcp135/HomeAutomation) to control some [LimitlessLED Smart-Lights](http://limitlessled.com) bulbs through a web interface hosted on a Raspberrypi.  

Over time my project expanded to include also some [Orvibo S20](http://www.orvibo.com/en_products_view.asp?mid=15&pid=4&id=234) smart sockets for which I couldn't find any existing PHP API so wrote this one using the reverse engineered data available from various sources, particularly the [Ninja Blocks Java API](https://github.com/Grayda/ninja-allone).

When using the status() function due to the way the sockets communicate back their status a listen IP address must be specified when calling $o->status('192.168.241.252') this IP address must be accessable by the socket.

Please feel free to include this code in your own projects and further develop and expand to support more of the features of the Orvibo smart sockets if you like.

Usage:
=============
    $o = new Orvibo('AC:CF:23:9C:A0:D0', '192.168.241.252', 10000);
    $o->on();
	
	$o->status('192.168.241.100');