<?php if (isset($_GET['c']) && $_GET['c'] == 1): ?>
    <div class="headline">
        <span class="f10 c5">Error creating database SQL.</span>
    </div>
    <br>
<?php endif; ?>

<form action="/installer/process" method="post" id="dataform">
    <div id="statLeft" class="top10Wrapper">
        <h4 class="round small spacer top top10_defs">Database Connection Settings</h4>
        <p>Create Database and Import SQL file.</p>
        <table cellpadding="1" cellspacing="1" id="top10_defs" class="top10 row_table_data">
            <tr class="hover">
                <td>Hostname:</td>
                <td><input name="sserver" class="text" type="text" id="sserver" value="localhost"></td>
            </tr>
            <tr class="hover">
                <td>Port:</td>
                <td><input type="text" class="text" name="sport" id="sport" value="3306"></td>
            </tr>
            <tr class="hover">
                <td>Username:</td>
                <td><input name="suser" class="text" type="text" id="suser" value=""></td>
            </tr>
            <tr class="hover">
                <td>Password:</td>
                <td><input type="text" class="text" name="spass" id="spass"></td>
            </tr>
            <tr class="hover">
                <td>DB name:</td>
                <td><input type="text" class="text" name="sdb" id="sdb"></td>
            </tr>
        </table>
        <br>

        <div align="left">
            <button type="submit" value="submit" name="submit" id="btn_ok" class="green ">
                <div class="button-container addHoverClick">
                    <div class="button-background">
                        <div class="buttonStart">
                            <div class="buttonEnd">
                                <div class="buttonMiddle"></div>
                            </div>
                        </div>
                    </div>

                    <div class="button-content">Continue</div>
                </div>
            </button>
        </div>
    </div>
</form>
