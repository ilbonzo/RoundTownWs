RoundTownWs
===========

PHP, Silex and MongoDb Web Service for http://app.sanzvan.it


API 1.0 
---------


_/feeds_  
Will return list of feeds 

_/feeds?tag=:tag_  
Will return list of feeds with tag 

_/feeds/:id_  
Will return list of news of feed 

_/images_  
Will return list of images 

_/places_  
Will return list of places 

_/places/:id_  
Will return details of place 

_/places/:id/images_  
Will return list of images of the place

_/tweets_
Will return list of tweets

_/tweets?from=list_
Will return list of tweets from specific list

_/tweets?from=search_
Will return list of tweets from specific search query



### Add to Vhost

  SetEnv APPLICATION_ENV "dev" or SetEnv APPLICATION_ENV "prod"


Trello Board
------------

https://trello.com/b/T77KVRdx/sanzvan



License
------------
This software library is licensed under [GNU General Public License, Version 2.0](http://www.gnu.org/licenses/gpl-2.0.html)


