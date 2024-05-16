<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Travian Installer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://license.viserlab.com/external/install.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <style>
        #hide {
            display: none;
        }
    </style>
</head>
<body>
<div class="installation-section padding-bottom padding-top">
    <div class="container">
        <div class="installation-wrapper">
            <div class="install-content-area">
                <div class="installation-wrapper pt-md-5">
                    <ul class="installation-menu">
                        <li class="steps done">
                            <div class="thumb">
                                <i class="fas fa-server"></i>
                            </div>
                            <h5 class="content">Server<br>Requirements</h5>
                        </li>
                        <li class="steps done">
                            <div class="thumb">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <h5 class="content">File<br>Permissions</h5>
                        </li>
                        <li class="steps running">
                            <div class="thumb">
                                <i class="fas fa-database"></i>
                            </div>
                            <h5 class="content">Installation<br>Information</h5>
                        </li>
                        <li class="steps">
                            <div class="thumb">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h5 class="content">Complete<br>Installation</h5>
                        </li>
                    </ul>
                </div>
                <div class="installation-wrapper">
                    <div class="install-content-area">
                        <div class="install-item">
                            <h3 class="bg-warning title text-center">Game Server Settings</h3>
                            <div class="box-item">
                                <form action="/installer/config" method="post" class="information-form-area mb--20">
                                    <div class="info-item">
                                        <h5 class="font-weight-normal mb-2">Config Details</h5>
                                        <div class="row">
                                            <div class="information-form-group col-sm-6">
                                                <label for="servername">Server Name:</label>
                                                <input type="text" name="servername" id="servername" value="Travian" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="speed">Speed:</label>
                                                <input type="number" name="speed" id="speed" value="200" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="roundlenght">Round Lenght:</label>
                                                <input type="number" name="roundlenght" id="roundlenght" value="7" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="incspeed">Troop Speed:</label>
                                                <input type="number" name="incspeed" id="incspeed" value="50" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="heroattrspeed">Hero Power Speed:</label>
                                                <input type="number" name="heroattrspeed" id="heroattrspeed" value="2" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="itemattrspeed">Item Power Speed:</label>
                                                <input type="number" name="itemattrspeed" id="itemattrspeed" value="3" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="wmax">World Size:</label>
                                                <select class="form-control" name="world_max" id="wmax">
                                                    <option value="25" selected="selected">25x25</option>
                                                    <option value="50">50x50</option>
                                                    <option value="100">100x100</option>
                                                    <option value="250">250x250</option>
                                                    <option value="350">350x350</option>
                                                    <option value="400">400x400</option>
                                                    <option value="500">500x500</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="natars_max">Gray Area Size:</label>
                                                <input type="text" name="natars_max" id="natars_max" value="22.1" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="reg_open">Register Open/Close:</label>
                                                <select class="form-control" name="reg_open" id="reg_open">
                                                    <option value="0">Close</option>
                                                    <option value="1" selected="selected">Open</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="server_url">Server url:</label>
                                                <input type="text" name="server_url" id="server_url" value="<?= http_host(); ?>" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="storagemultiplier">Storage Multiplier:</label>
                                                <input type="text" name="storagemultiplier" id="storagemultiplier" value="1" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="minbeginner">Min Beginners Protection:</label>
                                                <select class="form-control" name="minbeginner" id="minbeginner">
                                                    <option value="0" selected="selected">None</option>
                                                    <option value="1200">20 min</option>
                                                    <option value="1800">30 min</option>
                                                    <option value="3600">1 hour</option>
                                                    <option value="10800">3 hours</option>
                                                    <option value="21600">6 hours</option>
                                                    <option value="43200">12 hours</option>
                                                    <option value="86400">1 day</option>
                                                    <option value="172800">2 days</option>
                                                    <option value="259200">3 days</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="maxbeginner">Max Beginners Protection:</label>
                                                <select class="form-control" name="maxbeginner" id="maxbeginner">
                                                    <option value="0" selected="selected">None</option>
                                                    <option value="1200">20 min</option>
                                                    <option value="1800">30 min</option>
                                                    <option value="3600">1 hour</option>
                                                    <option value="10800">3 hour</option>
                                                    <option value="21600">6 hours</option>
                                                    <option value="43200">12 hours</option>
                                                    <option value="86400">1 day</option>
                                                    <option value="172800">2 days</option>
                                                    <option value="259200">3 days</option>
                                                    <option value="604800">7 days</option>
                                                    <option value="1209600">14 days</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="plus_time">Plus Duration:</label>
                                                <select class="form-control" name="plus_time" id="plus_time">
                                                    <option value="43200" selected="selected">12 hours</option>
                                                    <option value="86400">1 day</option>
                                                    <option value="172800">2 days</option>
                                                    <option value="259200">3 days</option>
                                                    <option value="345600">4 days</option>
                                                    <option value="432000">5 days</option>
                                                    <option value="518400">6 days</option>
                                                    <option value="604800">7 days</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="plus_production">Plus Production Duration:</label>
                                                <select class="form-control" name="plus_production" id="plus_production">
                                                    <option value="43200" selected="selected">12 hours</option>
                                                    <option value="86400">1 day</option>
                                                    <option value="172800">2 days</option>
                                                    <option value="259200">3 days</option>
                                                    <option value="345600">4 days</option>
                                                    <option value="432000">5 days</option>
                                                    <option value="518400">6 days</option>
                                                    <option value="604800">7 days</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="auction_time">Auction Duration:</label>
                                                <select class="form-control" name="auction_time" id="auction_time">
                                                    <option value="1200">20 min</option>
                                                    <option value="1800">30 min</option>
                                                    <option value="3600" selected="selected">1 hour</option>
                                                    <option value="10800">3 hours</option>
                                                    <option value="21600">6 hours</option>
                                                    <option value="28800">8 hours</option>
                                                    <option value="43200">12 hours</option>
                                                    <option value="86400">24 hours</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="ts_threshold">Turn Threshold:</label>
                                                <input type="number" name="ts_threshold" id="ts_threshold" value="20" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="medalinterval">Medals interval:</label>
                                                <select class="form-control" name="medalinterval" id="medalinterval">
                                                    <option value="43200" selected="selected">12 hours</option>
                                                    <option value="86400">24 hours</option>
                                                    <option value="129600">36 hours</option>
                                                    <option value="172800">48 hours</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="great_wks">Great Workshop:</label>
                                                <select class="form-control" name="great_wks" id="great_wks">
                                                    <option value="0">No</option>
                                                    <option value="1" selected="selected">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="ww">WW Stats:</label>
                                                <select class="form-control" name="ww" id="ww">
                                                    <option value="0">No</option>
                                                    <option value="1" selected="selected">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="peace">Peace system:</label>
                                                <select class="form-control" name="peace" id="peace">
                                                    <option value="0" selected="selected">None</option>
                                                    <option value="1">Normal</option>
                                                    <option value="2">Christmas</option>
                                                    <option value="3">New Year</option>
                                                    <option value="4">Easter</option>
                                                </select>
                                            </div>

                                            <hr />

                                            <div class="information-form-group col-sm-6">
                                                <label for="box1">News Box 1:</label>
                                                <select class="form-control" name="box1" id="box1">
                                                    <option value="1">Enable</option>
                                                    <option value="0" selected="selected">Disable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="box2">News Box 2:</label>
                                                <select class="form-control" name="box2" id="box2">
                                                    <option value="1">Enable</option>
                                                    <option value="0" selected="selected">Disable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="box3">News Box 3:</label>
                                                <select class="form-control" name="box3" id="box3">
                                                    <option value="1">Enable</option>
                                                    <option value="0" selected="selected">Disable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_build">Log Building:</label>
                                                <select class="form-control" name="log_build" id="log_build">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_tech">Log Tech:</label>
                                                <select class="form-control" name="log_tech" id="log_tech">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_login">Log Login:</label>
                                                <select class="form-control" name="log_login" id="log_login">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_gold">Log Gold:</label>
                                                <select class="form-control" name="log_gold" id="log_gold">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_admin">Log Admin:</label>
                                                <select class="form-control" name="log_admin" id="log_admin">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_users">Log user:</label>
                                                <select class="form-control" name="log_users" id="log_users">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_war">Log War:</label>
                                                <select class="form-control" name="log_war" id="log_war">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_market">Log Market:</label>
                                                <select class="form-control" name="log_market" id="log_market">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="log_illegal">Log Illegal:</label>
                                                <select class="form-control" name="log_illegal" id="log_illegal">
                                                    <option value="0" selected="selected">No</option>
                                                    <option value="1">Yes</option>
                                                </select>
                                            </div>

                                            <hr />

                                            <div class="information-form-group col-sm-6">
                                                <label for="check_db">Check DB Time:</label>
                                                <input type="number" name="check_db" id="check_db" value="3600" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="stats">Stats Time:</label>
                                                <input type="number" name="stats" id="stats" value="21600" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="quest">Quests:</label>
                                                <select class="form-control" name="quest" id="quest">
                                                    <option value="0">Disable</option>
                                                    <option value="1" selected="selected">Enable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="activate">Activation Email:</label>
                                                <select class="form-control" name="activate" id="activate">
                                                    <option value="0" selected="selected">Disable</option>
                                                    <option value="1">Enable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="limit_mailbox">Limit Mailbox:</label>
                                                <select class="form-control" name="limit_mailbox" id="limit_mailbox">
                                                    <option value="0" selected="selected">Disable</option>
                                                    <option value="1">Enable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="max_mails">Max mails:</label>
                                                <input type="number" name="max_mails" id="max_mails" value="30" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="timeout">Time out:</label>
                                                <input type="number" name="timeout" id="timeout" value="30" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="autodel">Auto Delete:</label>
                                                <select class="form-control" name="autodel" id="autodel">
                                                    <option value="0" selected="selected">Disable</option>
                                                    <option value="1">Enable</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="autodeltime">Auto Delete Time:</label>
                                                <input type="number" name="autodeltime" id="autodeltime" value="864000" required>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="demolish">Level Required of Main building for Demolishng Other Building</label>
                                                <select class="form-control" name="demolish" id="demolish">
                                                    <option value="5">5</option>
                                                    <option value="10" selected="selected">10 - Default</option>
                                                    <option value="15">15</option>
                                                    <option value="20">20</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="village_expand">Village Expand:</label>
                                                <select class="form-control" name="village_expand" id="village_expand">
                                                    <option value="0" selected="selected">Slow</option>
                                                    <option value="1">Normal</option>
                                                    <option value="2">Fast</option>
                                                </select>
                                            </div>
                                            <div class="information-form-group col-sm-6">
                                                <label for="commence">Start Date: [<?= date('Y-m-d H:i:s'); ?>]</label>
                                                <input type="text" name="commence" id="commence" value="0" required> seconds.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="information-form-group text-right">
                                            <button type="submit" class="theme-button choto">Continue</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>
