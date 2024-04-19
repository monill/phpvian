<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travian Installation</title>

    <link href="<?= asset('assets/css/compact.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset('assets/css/lang.css') ?>" rel="stylesheet" type="text/css" />
</head>

<body class="v35 logout">
<div id="background">
    <div id="bodyWrapper">
        <div id="center">
            <div id="sidebarBeforeContent" class="sidebar beforeContent">
                <div id="sidebarBoxMenu" class="sidebarBox   ">
                    <div class="sidebarBoxBaseBox">
                        <div class="baseBox baseBoxTop">
                            <div class="baseBox baseBoxBottom">
                                <div class="baseBox baseBoxCenter"></div>
                            </div>
                        </div>
                    </div>
                    <div class="sidebarBoxInnerBox">
                        <div class="innerBox header noHeader">
                        </div>
                        <div class="innerBox content">
                            <ul>
                                <?php include("template/menu.php"); ?>
                            </ul>
                        </div>
                        <div class="innerBox footer">
                        </div>
                    </div>
                </div>
            </div>
            <div id="contentOuterContainer">
                <div class="contentTitle">&nbsp;</div>
                <div class="contentContainer">
                    <div id="content" class="statistics">
                        <h1 class="titleInHeader">Travian Installation Script</h1>
                        <?php
                        if (!isset($_GET['s'])) {
                            include("template/greet.php");
                        } else {
                            switch ($_GET['s']) {
                                case 1:
                                    include("template/database.php");
                                    break;
                                case 2:
                                    include("template/dataform.php");
                                    break;
                                case 3:
                                    include("template/field.php");
                                    break;
                                case 4:
                                    include("template/multihunter.php");
                                    break;
                                case 5:
                                    include("template/oasis.php");
                                    break;
                                case 6:
                                    include("template/end.php");
                                    break;
                            }
                        }
                        ?>
                        <div class="clear">&nbsp;</div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>
