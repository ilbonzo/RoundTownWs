<?php
$towns = array();
//San Giovanni in Persiceto
$towns[] = array(
    'id' => 0,
    'name' => 'San Giovanni in Persiceto'
);
//San Lazzaro di Savena
$towns[] = array(
    'id' => 1,
    'name' => 'San Lazzaro di Savena'
);


$feeds = array(
    array(
        'id' => 0,
        'url' => 'http://www.comunepersiceto.it/home-page/Plone/notizie/notizie/RSS',
        'town_id' => 0
    ),
    array(
        'id' => 1,
        'url' => 'http://www.ddpersiceto.it/rss.xml',
        'town_id' => 0
    ),
    array(
        'id' => 2,
        'url'=>'http://www.icpersiceto.it/joomla/index.php?format=feed&amp;type=rss',
        'town_id' => 0
    )
);