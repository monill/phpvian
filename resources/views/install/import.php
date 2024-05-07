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
                            <h3 class="bg-warning title text-center"><?= isset($connection) ? $connection : ''; ?></h3>
                            <div class="box-item">
                                <form action="/installer/import" method="post" class="information-form-area mb--20">
                                    <div class="info-item">
                                        <div class="information-form-group text-right">
                                            <button type="submit" class="theme-button choto">Import Database</button>
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