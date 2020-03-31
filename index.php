<?php
    session_start();
    if (!isset($_SESSION["accid"])) {
        if (isset($_COOKIE["authToken"])) {
            include_once("php/restricted/db-functions.php");
            $userID = validateToken($_COOKIE["authToken"]);
            if ($userID === false) {
                setcookie("authToken", "", 1);
                header("Location: /login.php");
                exit;
            } else {
                $_SESSION["uid"] = $userID;
            }
        } else {
            header("Location: /login.php");
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Home - Customer Support | ABC Bank</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" type="image/ico" href="images/favicon.ico">
        <link rel="stylesheet" href="css/common.css">
        <link rel="stylesheet" href="css/home.css">
		<script src="js/home.js" type="module"></script>
		<base target="_blank">
	</head>
	<body>
        <nav id="navbar">
            <div class="nav-menu">
                <div class="nav-btn" id="sidemenu" data-menu="sidemenu-content"></div>
                <div class="hidden" id="sidemenu-content">
                    <div class="sidemenu-btn" id="account">ACCOUNT</div>
                    <div class="sidemenu-btn" id="logout">LOGOUT</div>
                </div>
            </div>
        </nav>
        <div class="modal-container hidden" id="acc-modal" data-modal="acc-modal" data-listener="modalClose">
            <div class="modal-content acc-modal">
                <span class="close" id="close-acc-modal" data-modal="acc-modal" data-listener="modalClose">&times;</span>
                <div id="acc-left-panel">
                    <div id="acc-tab-info" class="acc-btn active" data-listener="acc-btn" data-panel="acc-panel-info">Account Info</div>
                    <div id="acc-tab-pass" class="acc-btn" data-listener="acc-btn" data-panel="acc-panel-pass">Password</div>
                </div>
                <div class="acc-right-panel" id="acc-panel-info">
                    <h4>Username</h4>
                    <p class="acc-text" id="acc-username"></p>
                    <h4>Email</h4>
                    <p class="acc-text" id="acc-email"></p>
                    <h4>First Name</h4>
                    <p class="acc-text" id="acc-firstName"></p>
                    <h4>Last Name</h4>
                    <p class="acc-text" id="acc-lastName"></p>
                    <h4>Date Created</h4>
                    <p class="acc-text" id="acc-created"></p>
                    <h4>Account Type</h4>
                    <p class="acc-text" id="acc-type"></p>
                </div>
                <div class="acc-right-panel hidden-panel hidden" id="acc-panel-pass">
                    <form id="acc-pass-form" enctype="multipart/form-data">
                        <div class="form-group no-rev no-mgn">
                            <label for="pass-cur">Current Password</label>
                            <input type="password" name="password" id="pass-cur" data-listener="errorCheck" required>
                            <label class="error-label invisible" id="pass-cur-error">Error</label>
                        </div>
                        <div class="form-group no-rev no-mgn">
                            <label for="pass-new">New Password</label>
                            <input type="password" name="password-new" id="pass-new" data-listener="errorCheck" required>
                            <label class="error-label invisible" id="pass-new-error">Error</label>
                        </div>
                        <div class="form-group no-rev no-mgn">
                            <label for="pass-conf">Confirm Password</label>
                            <input type="password" name="password-confirm" id="pass-new-confirm" data-listener="errorCheck" required>
                            <label class="error-label invisible" id="pass-new-confirm-error">Error</label>
                        </div>
                        <button type="submit" id="pass-submit">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
	</body>
</html>