<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= setting('server_name') ?></title>

    <link rel="stylesheet" href="/assets/css/compact.css">
    <link rel="stylesheet" href="/assets/css/hero.css">
    <link rel="stylesheet" href="/assets/css/chat.css">
    <link rel="stylesheet" href="/assets/css/lang.css">

<!--    <script src="/assets/js/crypt.js" type="text/javascript"></script>-->

    <script>
        window.ajaxToken = '<?= md5(time()); ?>';
    </script>
<!--    <script type="text/javascript">-->
<!--        window.addEvent('domready', function () {-->
<!--            Travian.Game.Chat.chatHeartbeatTime= 8000;-->
<!--            Travian.Game.Chat.requestHeartbeatTime= 10000;-->
<!--            Travian.Game.Chat.allychatHeartbeatTime= 5000;-->
<!--            Travian.Game.Chat.flHeartbeatTime= 10000;-->
<!--            Travian.Game.Chat.originalTitle = document.title;-->
<!--            Travian.Game.Chat.savedconfigs = 1;-->
<!--            Travian.Game.Chat.username = "test";-->
<!--            Travian.Game.Chat.alliance = "0";-->
<!--            Travian.Game.Chat.render("/assets/images/");-->
<!--        });-->
<!--    </script>-->

<!--    <script type="text/javascript">-->
<!--        Travian.Translation.add({-->
<!--            'tpw.prepare': 'prepare',-->
<!--            'tpw.whosonline': "Online friends in chat",-->
<!--            'tpw.friends': 'Friends',-->
<!--            'tpw.allychat': 'Ally chat',-->
<!--            'tpw.requests': 'Requests',-->
<!--            'tpw.onlinestatus': 'Online status',-->
<!--            'tpw.notifications': 'Notifications',-->
<!--            'tpw.youroffline': "You're offline from chat.",-->
<!--            'tpw.offline': 'Offline',-->
<!--            'tpw.invisible': 'Invisible',-->
<!--            'tpw.invistononally': 'Invisible to non-ally friends',-->
<!--            'tpw.invistoally': 'Invisible to my alliance members',-->
<!--            'tpw.visible': 'Visible',-->
<!--            'tpw.soundnotify': 'Sound notification',-->
<!--            'tpw.popupnotify': 'Popup notification',-->
<!--            'tpw.showprevchat': 'Show previous message',-->
<!--            'tpw.wait': 'wait',-->
<!--            'tpw.save': 'Save',-->
<!--            'allgemein.anleitung': 'Instructions',-->
<!--            'allgemein.cancel': 'cancel',-->
<!--            'allgemein.ok': 'OK',-->
<!--            'allgemein.close': 'close',-->
<!--            'cropfinder.keine_ergebnisse': 'No search results found.'-->
<!--        });-->
<!--        Travian.applicationId = 'T4.4 Game';-->
<!--        Travian.Game.version = '4.4';-->
<!--        //Travian.Game.worldId = '--><?////= setting('server_name') ?><!--//';-->
<!--        //Travian.Game.speed = --><?////= setting('speed') ?><!--//;-->
<!---->
<!--    //    Travian.templates = {};-->
<!--    //    Travian.templates.ButtonTemplate =-->
<!--    //        "<button >\n\t<div class=\"button-container addHoverClick\">\n\t\t<div class=\"button-background\">\n\t\t\t<div class=\"buttonStart\">\n\t\t\t\t<div class=\"buttonEnd\">\n\t\t\t\t\t<div class=\"buttonMiddle\"><\/div>\n\t\t\t\t<\/div>\n\t\t\t<\/div>\n\t\t<\/div>\n\t\t<div class=\"button-content\"><\/div>\n\t<\/div>\n<\/button>\n";-->
<!--    //-->
<!--    //    Travian.Game.eventJamHtml =-->
<!--    //        '&lt;a href=&quot;#&quot; onclick=&quot;document.location.reload();&quot; title=&quotRenewal&quot;&gt;&lt;img src=&quot;/assets/images/refresh.gif&quot;&gt;'-->
<!--    //            .unescapeHtml();-->
<!--    //-->
<!--    //    window.addEvent('domready', function() {-->
<!--    //        Travian.Form.UnloadHelper.message = 'Would you like to close the page?';-->
<!--    //    });-->
<!--    //</script>-->
    <style>
        body,
        input,
        a,
        button,
        div,
        span,
        ul,
        li {
            font-family: Tahoma;
            font-size: 11px;
        }
    </style>

</head>
