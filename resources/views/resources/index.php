<?php render('layout/header'); ?>

<body class="v35 gecko chrome village1 perspectiveResources">

    <div id="background">
        <div id="headerBar"></div>
        <div id="bodyWrapper">
            <?php render('layout/navbar'); ?>
            <div id="center">
                <a id="ingameManual" href="/help">
                    <img class="question" alt="Help" src="/assets/images/x.gif">
                </a>

                <div id="sidebarBeforeContent" class="sidebar beforeContent">
                    <?php
    //                require('templates/heroSide.php');
    //                require('templates/Alliance.php');
    //                require('templates/infomsg.php');
    //                require('templates/links.php');
                    ?>
                    <div class="clear"></div>
                </div>
                <div id="contentOuterContainer">
                    <?php render('templates/resource'); ?>
                    <div class="contentTitle">
                        <a id="closeContentButton" class="contentTitleButton" href="dorf1.php"></a>
                    </div>
                    <div class="contentContainer">
                        <div id="content" class="village1">
                            <?php
                            render('templates/field');
    //                            if ($building->NewBuilding) {
    //                                require('templates/Building.php');
    //                            }
                            ?>
                            <div id="map_details">
                                <?php
    //                            require 'templates/movement.php';
    //                            require 'templates/production.php';
    //                            require 'templates/troops.php';
                                ?>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="sidebarAfterContent" class="sidebar afterContent">
                    <div id="sidebarBoxActiveVillage" class="sidebarBox ">
                        <div class="sidebarBoxBaseBox">
                            <div class="baseBox baseBoxTop">
                                <div class="baseBox baseBoxBottom">
                                    <div class="baseBox baseBoxCenter"></div>
                                </div>
                            </div>
                        </div>
    <!--                    --><?php //require 'templates/sideinfo.php'; ?>
                    </div>
                    <?php
    //                require 'templates/multivillage.php';
    //                require 'templates/quest.php';
                    ?>
                </div>
                <div class="clear"></div>
                <?php render('layout/footer'); ?>
            </div>
            <div id="ce"></div>
        </div>
    </div>

</body>

</html>
