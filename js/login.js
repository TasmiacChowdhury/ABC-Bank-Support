import * as Common from "./modules/common.js";

const eventListeners = [
    {
        "id": "login-form",
        "eventType": "submit",
        "function": () => login()
    }, {
        "id": "register-form",
        "eventType": "submit",
        "function": () => register()
    }, {
        "dataListener": "errorCheck",
        "eventType": "input",
        "function": Common.errorCheck
    }
];

window.addEventListener("DOMContentLoaded", async function() {
    document.getElementById("login-switch").addEventListener("click", () => {
        event.preventDefault();
        Common.switchPanel("register-panel", "login-panel");
    });
    document.getElementById("register-switch").addEventListener("click", () => {
        event.preventDefault();
        Common.switchPanel("login-panel", "register-panel");
    });

    Common.addListeners(eventListeners);
});

async function login() {
    event.preventDefault();

    var form = this || event.target;
    if (!Common.checkErrors([...form.elements])) { return Common.toast("Errors in form fields", "error"); }

    var formData = new FormData(form),
        response = await (await fetch("/php/login.php", {method: "POST", body: formData})).json();
    if (response.Success) {
        Common.insertInlineMessage("after", "login", response.Message, {type: "success"});
        setTimeout(() => window.location.href = "/index.php", 1000);
    } else {
        Common.insertInlineMessage("after", "login", response.Message, {type: "error"});
    }
}

async function register() {
    event.preventDefault();

    var form = this || event.target;
    if (!Common.checkErrors([...form.elements])) { return Common.toast("Errors in form fields", "error"); }

    var formData = new FormData(form),
        response = await (await fetch("/php/register.php", {method: "POST", body: formData})).json();
    if (response.Success) {
        Common.insertInlineMessage("after", "register", response.Message, {type: "success"});
        setTimeout(() => window.location.href = "/index.php", 1000);
    } else {
        Common.insertInlineMessage("after", "register", response.Message, {type: "error"});
    }
}