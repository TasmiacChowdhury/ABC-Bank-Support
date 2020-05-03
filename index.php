<?php
    session_start();
    if (!isset($_SESSION["accid"])) {
        if (isset($_COOKIE["authToken"])) {
            include_once("php/restricted/db-functions.php");
            $accountID = validateToken($_COOKIE["authToken"]);
            if ($accountID === false) {
                setcookie("authToken", "", 1);
                header("Location: /login.php");
                exit;
            } else {
                $_SESSION["accid"] = $accountID;
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
            <div class="nav-btn" id="create-ticket"></div>
            <div class="nav-menu">
                <div class="nav-btn" id="sidemenu" data-menu="sidemenu-content"></div>
                <div class="hidden" id="sidemenu-content">
                    <div class="sidemenu-btn" id="account">ACCOUNT</div>
                    <div class="sidemenu-btn" id="logout">LOGOUT</div>
                </div>
            </div>
        </nav>
        <div id="t-cntr">
            <div id="t-headers" class="row">
                <span id="h-subject" class="t-subject">Subject</span>
                <span id="h-id" class="t-id">Ticket ID</span>
                <span id="h-created" class="t-date">Created</span>
                <span id="h-updated" class="t-date">Last Updated</span>
                <span id="h-status" class="t-status">Status</span>
            </div>
            <div id="tickets"></div>
            <div id="pagination">
                <span class="page-arrow disabled" id="paginator-left"><</span>
                <div id="pages"></div>
                <span class="page-arrow disabled" id="paginator-right">></span>
            </div>
        </div>
        <div class="modal-container hidden" id="t-modal" data-modal="t-modal" data-listener="modalClose">
            <div class="modal-content pad-ctn-2">
                <span class="close" id="close-t-modal" data-modal="t-modal" data-listener="modalClose">&times;</span>
                <div id="messages-outer"></div>
            </div>
        </div>
        <div class="modal-container hidden" id="nt-modal" data-modal="nt-modal" data-listener="modalClose">
            <div class="modal-content pad-ctn-2">
                <span class="close" id="close-t-modal" data-modal="nt-modal" data-listener="modalClose">&times;</span>
                <form id="nt-f" enctype="multipart/form-data">
                    <div class="form-group no-rev">
                        <label for="nt-f-subject" class="lb-title">Subject</label>
                        <input type="text" name="subject" id="nt-f-subject" class="input-solid" placeholder="Enter Subject" data-listener="errorCheck" required>
                        <label class="error-label invisible" id="nt-f-subject-error">Error</label>
                    </div>
                    <div class="form-group no-rev">
                        <label for="nt-f-messageText" class="lb-title">Message</label>
                        <textarea name="messageText" class="textarea" id="nt-f-messageText" placeholder="Enter your message here..." maxlength="65535" required></textarea>
                        <label class="error-label invisible" id="nt-f-messageText-error">Error</label>
                    </div>
                    <div class="row between">
                        <label for="file-upload" class="file-input-group">
                            <span class="file-input-name hidden" id="nt-file-name"></span>
                            <span class="file-input-btn disabled"><img src="images/Upload.png" class="file-input-icon"></span>
                        </label>
                        <input type="file" class="file-input" name="files" id="nt-file-upload" accept="image/png, image/jpeg, application/pdf" multiple>
                        <span class="file-input-btn del disabled hidden" id="nt-remove-upload"><img src="images/Delete.png" class="file-input-icon"></span>
                        <input type="hidden" name="removeFiles" id="nt-f-remove" value="false">
                    </div>
                    <button type="submit" class="btn-hollow submit" id="nt-submit">SUBMIT</button>
                </form>
            </div>
        </div>
        <div class="modal-container hidden" id="rt-modal" data-modal="rt-modal" data-listener="modalClose">
            <div class="modal-content pad-ctn-2">
                <span class="close" id="close-t-modal" data-modal="rt-modal" data-listener="modalClose">&times;</span>
                <form id="rt-f" enctype="multipart/form-data">
                    <div class="form-group no-rev">
                        <label for="rt-f-messageText" class="lb-title">Message</label>
                        <textarea name="messageText" class="textarea" id="rt-f-messageText" placeholder="Enter your message here..." maxlength="65535"></textarea>
                        <label class="error-label invisible" id="rt-f-messageText-error">Error</label>
                    </div>
                    <div class="row between">
                        <label for="file-upload" class="file-input-group">
                            <span class="file-input-name hidden" id="rt-file-name"></span>
                            <span class="file-input-btn disabled"><img src="images/Upload.png" class="file-input-icon"></span>
                        </label>
                        <input type="file" class="file-input" name="files" id="rt-file-upload" accept="image/png, image/jpeg, application/pdf" multiple>
                        <span class="file-input-btn del disabled hidden" id="rt-remove-upload"><img src="images/Delete.png" class="file-input-icon"></span>
                        <input type="hidden" name="removeFiles" id="rt-f-remove" value="false">
                    </div>
                    <button type="submit" class="btn-hollow submit" id="rt-submit">SUBMIT</button>
                </form>
            </div>
        </div>
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