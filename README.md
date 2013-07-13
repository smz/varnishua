varnishua
=========

Joomla plugin detects device from Varnish or PHP


The idea is to flip between Desktop, Tablet and Mobile devices and using the overrides of joomla set the content that the user will see on his/her browser depending on the device. 

The set up with Varnish is extremely efficient as the request comming from the user immidiately proccesed and a value X_UA_DEVICE is stored in the header that is passed to PHP and Joomla!. Joomla then running this plugin gets the value and stores it in the users session. By the way this plugin takes care of the nasty cookie problem that prevents Joomla co-exists with Varnish. Once the session got the info we just check with a simple case or if/else in the module and we set the data that joomla will render.

Its not fully tested, but so far no nasty bugs.

Of course if you don't have Varnish (that's a dedicated server) you can use plain PHP. The code for that comes from Rene Kreijveld @ https://github.com/renekreijveld/UserAgentDetector


