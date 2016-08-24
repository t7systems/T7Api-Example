<?php

/**
 *
 * This File provides common default values.
 *
 * You should not edit this file.
 * Instead, you should edit 'custom.php' to provide your own preferences.
 *
 */

return array(

    'urls'             => array(
        'wsdl'    => 'https://content.777live.com/soap/1_4/777live.wsdl',
        'content' => 'https://content.777live.com/soap/1_4/getcontent.php',
    ),

    //Chat sessions are created with the following ttl in seconds (should be > 10 as a minimum)
    'seconds'          => '10',

    //Callback URL for ended chat sessions. Users will be redirected to this URL.
    'quitUrl'          => '//' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?chatExit',

);