backend default {
    .host = "127.0.0.1";
    .port = "8080";
}

include "devicedetect.vcl";
sub vcl_recv {

    # Forward client's IP to backend
	remove req.http.X-Forwarded-For;
	set req.http.X-Forwarded-For = client.ip;

	call devicedetect;

	# Proxy (pass) any request that goes to the backend admin,
	# the banner component links or any post requests
    # You can add more pages or entire URL structure in the end of the "if"
	if(req.http.cookie ~ "userID" || req.url ~ "^/administrator" || req.url ~ "^/component/banners" || req.request == "POST") {
		return (pass);
	}
	
	# Check for the custom "x-logged-in" header to identify if the visitor is a guest,
	# then unset any cookie (including session cookies) provided it's not a POST request
	if(req.http.x-logged-in == "False" && req.request != "POST"){
		unset req.http.cookie;
	}

	# Properly handle different encoding types
	if (req.http.Accept-Encoding) {
	  if (req.url ~ "\.(jpg|jpeg|png|gif|gz|tgz|bz2|tbz|mp3|ogg|swf|ico)$") {
	    # No point in compressing these
	    remove req.http.Accept-Encoding;
	  } elsif (req.http.Accept-Encoding ~ "gzip") {
	    set req.http.Accept-Encoding = "gzip";
	  } elsif (req.http.Accept-Encoding ~ "deflate") {
	    set req.http.Accept-Encoding = "deflate";
	  } else {
	    # unknown algorithm (aka crappy browser)
	    remove req.http.Accept-Encoding;
	  }
	}

	# Cache files with these extensions
	if (req.url ~ "\.(js|css|jpg|jpeg|png|gif|gz|tgz|bz2|tbz|mp3|ogg|swf|ico)$") {
		return (lookup);
	}

	# Set how long Varnish will cache content depending on whether your backend is healthy or not
	if (req.backend.healthy) {
		set req.grace = 5m;
	} else {
		set req.grace = 1h;
	}

	return (lookup);
}

sub vcl_fetch {
	
	# Check for the custom "x-logged-in" header to identify if the visitor is a guest,
	# then unset any cookie (including session cookies) provided it's not a POST request
	if(req.request != "POST" && beresp.http.x-logged-in == "False") {
		unset beresp.http.Set-Cookie;
	}
	
	# Allow items to be stale if needed (this value should be the same as with "set req.grace"
	# inside the sub vcl_recv {…} block (the 2nd part of the if/else statement)
	set beresp.grace = 1h;
	
	# Serve pages from the cache should we get a sudden error and re-check in one minute
	if (beresp.status == 503 || beresp.status == 502 || beresp.status == 501 || beresp.status == 500) {
		set beresp.ttl = 0m;
	  #set beresp.grace = 60s;
	  return (restart);
	}
	if (beresp.status == 302) { 
		set beresp.ttl = 0m; 
		return (deliver); 
		}
	# Unset the "etag" header (suggested)
	unset beresp.http.etag;
	
	# This is Joomla! specific: fix stupid "no-cache" header sent by Joomla! even
	# when caching is on - make sure to replace 300 with the number of seconds that
	# you want the browser to cache content
	if(beresp.http.Cache-Control == "no-cache" || beresp.http.Cache-Control == ""){
		set beresp.http.Cache-Control = "max-age=300, public, must-revalidate";
	}
	
	# This is how long Varnish will cache content
	set beresp.ttl = 5m;
	
	    if (req.http.X-UA-Device) {
        if (!beresp.http.Vary) { # no Vary at all
            set beresp.http.Vary = "X-UA-Device";
        } elseif (beresp.http.Vary !~ "X-UA-Device") { # add to existing Vary
            set beresp.http.Vary = beresp.http.Vary + ", X-UA-Device";
        }
    }
    # comment this out if you don't want the client to know your classification
    set beresp.http.X-UA-Device = req.http.X-UA-Device;
    
	return (deliver);
}

sub vcl_deliver {
    if ((req.http.X-UA-Device) && (resp.http.Vary)) {
        set resp.http.Vary = regsub(resp.http.Vary, "X-UA-Device", "User-Agent");
    }
}

