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
Will return list of image

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

Trello Board
------------

https://trello.com/b/T77KVRdx/sanzvan


Developer instructions
---------

* install VirtualBox
* install Vagrant
* add '192.168.140.12 api.roundtown.dev' to your /etc/hosts
* git clone [your forked repo]
* cd RoundTownWs
* $ git submodule update --init --recursive
* cd tools
* $ vagrant up
* $ vagrant ssh
* create db

    $ mongo

    $ [copy content of /tools/data/createdb.txt on mongo shell]

* create file www/config/settings.yml from settings.yml.sample
* create file www/silex.log and set permission to 777

* open browser at http://api.roundtownws.dev/
* /workspace/www is the working directory

* cd /workspace/app
* npm install


### Config Vhost

  For production change
  SetEnv APPLICATION_ENV "dev" to SetEnv APPLICATION_ENV "prod"


License
------------
This software library is licensed under [GNU General Public License, Version 2.0](http://www.gnu.org/licenses/gpl-2.0.html)
