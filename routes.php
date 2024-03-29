<?php

return function(Application $app) {

    /**
     * One of these 'routes' will be executed at the bottom of this closure
     */

    $lang = function() use ($app) {
        switch ($_POST['lang']) {
            case 'en':
            case 'de':
                $_SESSION['lang'] = $_POST['lang'];
        }
        if (isset($_POST['redirect'])) {
            header('Location: ' . $_POST['redirect']);
        } else {
            header('Location: /');
        }
    };

    $livesnap = function() use ($app) {
        switch ($_POST['livesnap']) {
            case '0':
            case '1':
                $_SESSION['livesnap'] = $_POST['livesnap'];
        }
        if (isset($_POST['redirect'])) {
            header('Location: ' . $_POST['redirect']);
        } else {
            header('Location: /');
        }
    };

    $cams = function() use ($app) {
        $categories = $app->client()->getAllCategories($app['lang']);
        $category   = 0;
        if (isset($_GET['cat'])) {
            $category = $_GET['cat'];
        }
        $cams       = $app->client()->getOnlineCams($category, $app['lang']);

        $livePreviews = array();
        foreach ($cams as $cam) {
            $livePreviews[$cam->camID] = $app->client()->getLivePreviewPic($cam->camID);
        }

        require __DIR__ . '/views/index.php';
    };

    $chatOptions = function() use ($app) {

        $camId         = $_GET['chatOptions'];
        $nickname      = '';
        $voyeurMode    = false;
        $showCam2Cam   = false;
        $showSendSound = false;
        $sendSound     = false;
        if (isset($_SESSION['nickname'])) {
            $nickname      = $_SESSION['nickname'];
        }
        if (isset($_SESSION['voyeurMode'])) {
            $voyeurMode    = $_SESSION['voyeurMode'];
        }
        if (isset($_SESSION['showcam2cam'])) {
            $showCam2Cam   = $_SESSION['showcam2cam'];
        }
        if (isset($_SESSION['showsendsound'])) {
            $showSendSound = $_SESSION['showsendsound'];
        }
        if (isset($_SESSION['sendsound'])) {
            $sendSound     = $_SESSION['sendsound'];
        }

        require __DIR__ . '/views/index.php';
    };

    $chat = function() use ($app) {
        //TODO check if user is allowed to chat, prepare DB, ...

        //TODO Sanitize somehow!
        $camId = $_SESSION['chat_camid'] = $_GET['chat'];

        //nickname must not be empty and must not equal 'voyeur'
        if (isset($_GET['nickname']) && strtolower($_GET['nickname']) != 'voyeur') {
            $nickname      = $_SESSION['nickname']      = $_GET['nickname'];
        } else {
            //show error message... or just use a default nickname
            //again: don't use 'voyeur' -> this will break switching from voyeur mode to chat mode!
            $nickname      = $_SESSION['nickname']      = 'Anon';
        }
        if (isset($_GET['voyeurMode'])) {
            $voyeurMode    = $_SESSION['voyeurMode']    = (bool)$_GET['voyeurMode'];
        } else {
            $voyeurMode    = $_SESSION['voyeurMode']    = false;
        }
        if (isset($_GET['showcam2cam'])) {
            $showCam2Cam   = $_SESSION['showcam2cam']   = (bool)$_GET['showcam2cam'];
        } else {
            $showCam2Cam   = $_SESSION['showcam2cam']   = false;
        }
        if (isset($_GET['showsendsound'])) {
            $showSendSound = $_SESSION['showsendsound'] = (bool)$_GET['showsendsound'];
        } else {
            $showSendSound = $_SESSION['showsendsound'] = false;
        }
        if (isset($_GET['sendsound'])) {
            $sendSound     = $_SESSION['sendsound']     = (bool)$_GET['sendsound'];
        } else {
            $sendSound     = $_SESSION['sendsound']     = false;
        }

        if (isset($app['cfg']['testCamId']) && !empty($app['cfg']['testCamId'])) {
            $camId = $app['cfg']['testCamId'];
        }

        try {
            $chatInfo  = $app->client()->getChat($camId, $app['cfg']['seconds'], $nickname, $voyeurMode, $showCam2Cam, $showSendSound, $sendSound, $app['lang']);
            $chatUrl   = $chatInfo['url'];
            $sessionId = $chatInfo['sessionId'];

            $_SESSION['sessionId'] = $sessionId;

            $app['route'] = 'chat';

        } catch (\SoapFault $ex) {
            if (ini_get("display_errors")) {
              //rethrow while in development mode to show error message
              throw $ex;
            } else {
              //chances are, an error occurred, because show is offline
              //don't confuse users with too much details
              $app['route'] = 'offline';
            }
        }

        require __DIR__ . '/views/index.php';
    };

    $keepAlive = function() use ($app) {
        if (!empty($_SESSION['sessionId'])) {

            //TODO check if user is still allowed to chat, update DB, ...
            $app->client()->keepAliveChatSession($_SESSION['sessionId'], $app['cfg']['seconds']);
            echo 'ok';

        } else {
            echo 'nok';
        }
    };

    $endChat = function() use ($app) {
        $app->client()->endChatSession($_SESSION['sessionId']);
        echo 'ok';
    };

    $chatExit = function() use ($app) {

        if (isset($_SESSION['sessionId'])) {

            $chatStatus = $app->client()->getChatStatus($_SESSION['sessionId']);
            $start      = new DateTime();
            $stop       = new DateTime();

            $start->setTimestamp($chatStatus->startDate);
            $stop->setTimestamp($chatStatus->stopDate);

            $startTime    = $start->setTimezone(new DateTimeZone('Europe/Berlin'))->format('H:i:s');
            $stopTime     = $stop->setTimezone(new DateTimeZone('Europe/Berlin'))->format('H:i:s');

            $startTimeUTC = $start->setTimezone(new DateTimeZone('UTC'))->format('H:i:s');
            $stopTimeUTC  = $stop->setTimezone(new DateTimeZone('UTC'))->format('H:i:s');

            /**
             * TODO
             * Do something after session ended.
             *
             * Do not rely on this, since users may just close their browser.
             *
             * Or the network might fail, so you cannot query the session state from our API at that particular time.
             *
             * Whatever action is necessary, should additionally be performed by a cronjob to cleanup unexpectedly closed/lost sessions.
             *
             */
            unset($_SESSION['sessionId']);

        }

        require __DIR__ . '/views/exit.php';

    };

    $sedcard = function() use ($app) {
        $sedcard     = $app->client()->getSedcard($_GET['sedcard'], $app['lang']);
        unset($sedcard->ipAddress);
        $video       = $app->client()->getFreeVideo($_GET['sedcard']);
        if ($sedcard->previewFsk16) {
            $pics = $app->client()->getFreePictureGallery($_GET['sedcard'], 'l');
        } else {
            $pics = $app->client()->getFreePictureGalleryBlurred($_GET['sedcard'], 'l');
        }
        require __DIR__ . '/views/index.php';
    };

    if (isset(${$app['route']}) && get_class(${$app['route']}) == 'Closure') {
        //execute closure matching the route
        ${$app['route']}();
    } else {
        echo '404';
    }
};