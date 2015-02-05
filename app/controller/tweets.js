var express = require('express');
module.exports = function(socket) {

    var twitter = require('ntwitter');
    var config = require('../config/settings.js');
    var twit = new twitter(config);

    var words = [
            'sanzvan',
            'Persiceto',
            'San Giovanni in Persiceto',
            '@San_Zvan',
            // 'sgp'
            'charlie',
            'igers',
            'instagram'
        ];
    twit.stream('statuses/filter', { track: words }, function(stream) {
        stream.on('data', function (data) {
            socket.volatile.emit('tweet', {
                author_name: data.user.name,
                screen_name: data.user.screen_name,
                avatar: data.user.profile_image_url,
                text: data.text,
                media: data.entities.media
            });

        });
    });
};
