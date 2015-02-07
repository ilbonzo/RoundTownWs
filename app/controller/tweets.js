var express = require('express');
module.exports = function(socket) {

    var twitter = require('ntwitter');
    var config = require('../config/settings.js');
    var twit = new twitter(config);

    //Twitter Parsers
    String.prototype.parseURL = function() {
        return this.replace(/[A-Za-z]+:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9-_:%&~?\/.=]+/g, function(url) {
            return url.link(url);
        });
    };
    String.prototype.parseUsername = function() {
        return this.replace(/[@]+[A-Za-z0-9-_]+/g, function(u) {
            var username = u.replace("@","");
            return u.link("https://twitter.com/"+username);
        });
    };
    String.prototype.parseHashtag = function() {
        return this.replace(/[#]+[A-Za-z0-9-_]+/g, function(t) {
            var tag = t.replace("#","%23");
            return t.link("https://twitter.com/search?q="+tag+"&src=hash");
        });
    };

    var words = [
            'sanzvan',
            'Persiceto',
            'San Giovanni in Persiceto',
            '@San_Zvan',
            'rebertoldo',
            'sgp',
            'sangio',
            'borgo rotondo',
            'carnevalepersiceto',
            'charlie'
        ];
    twit.stream('statuses/filter', { track: words }, function(stream) {
        stream.on('data', function (data) {
            var etext = data.text.parseURL().parseUsername().parseHashtag();
            socket.volatile.emit('tweet', {
                author_name: data.user.name,
                screen_name: data.user.screen_name,
                avatar: data.user.profile_image_url,
                text: etext,
                media: data.entities.media
            });

        });
    });

};
