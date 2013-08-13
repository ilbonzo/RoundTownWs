RoundTownWs
===========


API 1.0 


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


### Add to Vhost

  SetEnv APPLICATION_ENV "dev" or SetEnv APPLICATION_ENV "prod"
