Android Manual Link Resolver
============================

HTTP API which resolves manual links for Android classes. Provides shortened URLs or Markdown-formatted messages for use in StackOverflow chat.

Usage
-----

The master branch is [deployed on heroku](http://android-manual.herokuapp.com/) or can easily be set up as a local instance. Requires PHP 5.3+.

The most basic usage is to visit `http://appdomain/<class name>` where `<class name>` is the name of the class being queried. The response will be a markdown-formatted string which can be used a message on StackOverflow chat. The API support two query string parameters to modify this response format:

 - `mode=markdown|url` When this parameter is supplied, the message will be returned in the specified format. The default format is `markdown`, `url` returns only the shortened URL which redirects to the manual page for the specified class.
 - `callback=*` When this parameter is supplied, the response is returned as JSONP with the specified callback name. The JSON object has a single property, `src`, which contains the response string.

When no class name is specified, the HTTP response has a 400 response code and the body contains a human readable error message. When the queried class name cannot be resolved the HTTP response has a 404 response code and the body contains a human readable error message.
