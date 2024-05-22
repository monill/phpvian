<div id="header">
    <a id="logo" href="/" target="_blank" title="SERVER_NAME"></a>
    <ul id="navigation">
        <li id="n1" class="villageResources">
            <a class="active" href="/dorf1" accesskey="1" title="Resources"></a>
        </li>
        <li id="n2" class="villageBuildings">
            <a class="active" href="/dorf2" accesskey="2" title="Village"></a>
        </li>
        <li id="n3" class="map">
            <a class="" href="/karte" accesskey="3" title="Map"></a>
        </li>
        <li id="n4" class="statistics">
            <a class="" href="/statistiken" accesskey="4" title="Statistics"></a>
        </li>
        <?php
//        $countmsg = count($database->getMessage($session->uid, 12));
//        $unmsg = $countmsg >= 1 ? $countmsg : $countmsg;
//        $countnot = count($database->getNotice5($session->uid));
//        $unnotice = $countnot >= 1 ? $countnot : $countnot;
        ?>
        <li id="n5" class="reports">
            <a href="/berichte" accesskey="5" title="Reports 0"></a>
<!--            --><?php
//            if ($message->nunread) {
//                echo '<div class="speechBubbleContainer ">
//			<div class="speechBubbleBackground">
//				<div class="start">
//					<div class="end">
//						<div class="middle"></div>
//					</div>
//				</div>
//			</div>
//			<div class="speechBubbleContent">' . $unnotice . '</div>
//		</div>';
//            }
//            ?>
        </li>
        <li id="n6" class="messages">
            <a href="/nachrichten" accesskey="6" title="Messages 0"></a>
<!--            --><?php
//            if ($message->unread) {
//                echo '<div class="speechBubbleContainer ">
//			<div class="speechBubbleBackground">
//				<div class="start">
//					<div class="end">
//						<div class="middle"></div>
//					</div>
//				</div>
//			</div>
//			<div class="speechBubbleContent">' . $unmsg . '</div>
//		</div>';
//            }
//            ?>

        </li>
        <li id="n7" class="gold">
            <a href="#" title="Get Gold" accesskey="7" onclick="window.fireEvent('startPaymentWizard', {}); this.blur(); return false;" class=""></a>
        </li>
    </ul>
<!--    <script type="text/javascript">-->
<!--        window.addEvent('domready', function() {-->
<!--            Travian.Game.Layout.goldButtonAnimation();-->
<!--        });-->
<!--    </script>-->
    <div id="goldSilver">
        <div class="gold">
            <img src="/assets/images/x.gif" alt="Gold" title="Gold" class="gold" onclick="window.fireEvent('startPaymentWizard', {data:{activeTab: 'pros'}}); return false;" />
            <span class="ajaxReplaceableGoldAmount"><?php// echo $session->gold; ?></span>
        </div>
        <div class="silver">
            <a href="/hero_auction"><img src="/assets/images/x.gif" alt="Silver" title="Silver" class="silver" /></a>
            <span class="ajaxReplaceableSilverAmount"><?php// echo $session->silver; ?></span>
        </div>
    </div>
    <ul id="outOfGame" class="LTR">
        <li class="profile">
            <a href="/spieler?uid=<?php // echo $session->uid; ?>" title="Profile">
                <img src="/assets/images/x.gif" alt="Profile" />
            </a>
        </li>
        <li class="options">
            <a href="/options" title="Settings">
                <img src="/assets/images/x.gif" alt="Settings" />
            </a>
        </li>
        <li class="forum">
            <a target="_blank" href="#" title="Forum">
                <img src="/assets/images/x.gif" alt="Forum" />
            </a>
        </li>
        <li class="chat">
            <a target="_blank" href="#" title="Chat">
                <img src="/assets/images/x.gif" alt="Chat" />
            </a>
        </li>
        <li class="help">
            <a href="/help" title="Help">
                <img src="/assets/images/x.gif" alt="Help" />
            </a>
        </li>
        <li class="logout ">
            <a href="/logout" title="Logout">
                <img src="/assets/images/x.gif" alt="Logout" />
            </a>
        </li>
        <li class="clear"></li>
    </ul>

<!--    <script type="text/javascript">-->
<!--        $$('#outOfGame li.logout a').addEvent('click', function() {-->
<!--            Travian.WindowManager.getWindows().each(function($dialog) {-->
<!--                Travian.WindowManager.unregister($dialog);-->
<!--            });-->
<!--        });-->
<!--    </script>-->
</div>
