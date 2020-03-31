import * as Common from "./modules/common.js";

const eventListeners = [
    {
        "id": "sidemenu",
        "eventType": "click",
        "function": toggleMenu
    }, {
        "id": "account",
        "eventType": "click",
        "function": modalAccount
    }, {
        "id": "logout",
        "eventType": "click",
        "function": () => logout()
    }, {
        "id": "acc-pass-form",
        "eventType": "submit",
        "function": () => updatePassword()
    }, {
        "dataListener": "modalClose",
        "eventType": "mousedown",
        "function": () => modalClose()
    }, {
        "dataListener": "errorCheck",
        "eventType": "input",
        "function": Common.errorCheck
    }, {
        "dataListener": "acc-btn",
        "eventType": "click",
        "function": function(event) {
            Common.switchPanel(IdxGlobals.accCurPanel, event.target.dataset.panel);
            IdxGlobals.accCurPanel = event.target.dataset.panel;
            Common.switchTab(IdxGlobals.accCurTab, event.target.id);
            IdxGlobals.accCurTab = event.target.id;
        }
    }, {
        "domObject": document,
        "eventType": "click",
        "function": closeMenus
    }
];

const IdxGlobals = {
    accCurTab: "acc-tab-info",
    accCurPanel: "acc-panel-info",
    openMenus: []
}

const LazyObserver = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.src = e.target.dataset.src;
            e.target.classList.remove("lazy");
            LazyObserver.unobserve(e.target);
        }
    });
});

window.addEventListener("DOMContentLoaded", async function() {
    Common.addListeners(eventListeners);
});

/******************************* GENERAL *******************************/
function closeMenus() {
    if (IdxGlobals.openMenus.length > 0) {
        IdxGlobals.openMenus.forEach(e => {
            e.classList.toggle("hidden");
            IdxGlobals.openMenus.shift();
        });
    }
}

function toggleMenu() {
    event.stopPropagation();
    var ele = this || event.target,
        menu = document.getElementById(ele.dataset.menu);
    if (!IdxGlobals.openMenus.includes(menu)) {
        closeMenus();
        menu.classList.toggle("hidden");
        IdxGlobals.openMenus.push(menu);
    } else { closeMenus(); }
}

/******************************* ACCOUNT *******************************/
async function logout() {
    event.preventDefault();
    var response = await (await fetch("/php/logout.php")).json();
    if (response.Success) {
        Common.toast(response.Message, "success");
        setTimeout(() => window.location.href = "/login.php", 1000);
    } else {
        Common.toast(response.Message, "error");
    }
}

async function updatePassword() {
    event.preventDefault();
    if (!Common.checkErrors([...this.elements])) { return Common.toast("Errors in form fields", "error"); }

    var formData = new FormData(this),
        response = await (await fetch("/php/update-pass.php", {method: "POST", body: formData})).json();

    response.Success ? Common.toast(response.Message, "success") : Common.toast(response.Message, "error");
}

/******************************* MODAL *******************************/
async function modalAccount() {
    var [modal, username, email, firstName, lastName, dateCreated, accType] = document.querySelectorAll("#acc-modal, #acc-username, #acc-firstName, #acc-lastName, #acc-email, #acc-created, #acc-type");
    let response = await (await fetch("/php/account-info.php")).json();

    if (!response.Success) { return Common.toast(response.Message, "error"); }
    username.innerHTML = response.Info.Username;
    email.innerHTML = response.Info.Email;
    firstName.innerHTML = response.Info.FirstName;
    lastName.innerHTML = response.Info.LastName;
    dateCreated.innerHTML = response.Info.DateCreated;
    accType.innerHTML = (response.Info.AccountType == "c") ? "Customer" : "Employee";

    closeMenus();
	modal.classList.remove("hidden");
}

function modalClose(del = false) {
    event.stopPropagation();
	var modal = document.getElementById(event.target.dataset.modal);
	if (event.target == modal|| event.target.classList.contains("close")) { del ? modal.remove() : modal.classList.add("hidden"); }
}