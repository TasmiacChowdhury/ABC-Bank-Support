import * as Cmn from "./modules/common.js";

const eventListeners = [
    {
        "id": "account",
        "eventType": "click",
        "function": modalAccount
    }, {
        "id": "acc-pass-form",
        "eventType": "submit",
        "function": () => updatePassword()
    }, {
        "id": "create-ticket",
        "eventType": "click",
        "function": modalNewTicket
    }, {
        "id": "logout",
        "eventType": "click",
        "function": () => logout()
    }, {
        "id": "nt-f",
        "eventType": "submit",
        "function": uploadTicket
    }, {
        "id": "sidemenu",
        "eventType": "click",
        "function": toggleMenu
    }, {
        "dataListener": "acc-btn",
        "eventType": "click",
        "function": function(event) {
            Cmn.switchPanel(IdxGlobals.accCurPanel, event.target.dataset.panel);
            IdxGlobals.accCurPanel = event.target.dataset.panel;
            Cmn.switchTab(IdxGlobals.accCurTab, event.target.id);
            IdxGlobals.accCurTab = event.target.id;
        }
    }, {
        "dataListener": "errorCheck",
        "eventType": "input",
        "function": Cmn.errorCheck
    }, {
        "dataListener": "modalClose",
        "eventType": "mousedown",
        "function": () => modalClose()
    }, {
        "domObject": document,
        "eventType": "click",
        "function": closeMenus
    }
];

const IdxGlobals = {
    accCurTab: "acc-tab-info",
    accCurPanel: "acc-panel-info",
    container: "",
    openMenus: [],
    tickets: []
}

window.addEventListener("DOMContentLoaded", async function() {
    Cmn.addListeners(eventListeners);
    IdxGlobals.container = document.getElementById("tickets");
    IdxGlobals.tickets = await getTickets();
    if (!ticketsEmpty(IdxGlobals.tickets)) { createTickets(IdxGlobals.container, IdxGlobals.tickets); }
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
        Cmn.toast(response.Message, "success");
        setTimeout(() => window.location.href = "/login.php", 1000);
    } else {
        Cmn.toast(response.Message, "error");
    }
}

async function updatePassword() {
    event.preventDefault();
    if (!Cmn.checkErrors([...this.elements])) { return Cmn.toast("Errors in form fields", "error"); }

    var formData = new FormData(this),
        response = await (await fetch("/php/update-pass.php", {method: "POST", body: formData})).json();

    response.Success ? Cmn.toast(response.Message, "success") : Cmn.toast(response.Message, "error");
}

/******************************* MODAL *******************************/
async function modalAccount() {
    var [modal, username, email, firstName, lastName, dateCreated, accType] = document.querySelectorAll("#acc-modal, #acc-username, #acc-firstName, #acc-lastName, #acc-email, #acc-created, #acc-type");
    let response = await (await fetch("/php/account-info.php")).json(),
        info = response.Info;

    if (!response.Success) { return Cmn.toast(response.Message, "error"); }
    username.innerHTML = info.Username;
    email.innerHTML = info.Email;
    firstName.innerHTML = info.FirstName;
    lastName.innerHTML = info.LastName;
    dateCreated.innerHTML = Cmn.printDate(info.DateCreated);
    accType.innerHTML = (info.AccountType == "c") ? "Customer" : "Employee";

    closeMenus();
	modal.classList.remove("hidden");
}

function modalClose(del = false) {
    event.stopPropagation();
	var modal = document.getElementById(event.target.dataset.modal);
	if (event.target == modal|| event.target.classList.contains("close")) { del ? modal.remove() : modal.classList.add("hidden"); }
}

function modalNewTicket() {
    var [modal, formSubject, formMessageText] = document.querySelectorAll("#nt-modal, #nt-f-subject, #nt-f-messageText");

    formSubject.value = "";
    formMessageText.value = "";

    closeMenus();
	modal.classList.remove("hidden");
}

function modalOpenTicket(ticket) {
    var [modal, cntr] = document.querySelectorAll("#t-modal, #t-messages"),
        docFrag = document.createDocumentFragment();

    Object.keys(ticket.Messages).forEach(i => docFrag.appendChild(createTicketMessage(ticket.Messages[i])));

    cntr.innerHTML = "";
    cntr.insertAdjacentHTML("afterbegin", `<h2 class="message-subject">${ticket.TicketSubject}</h2>`);
    cntr.appendChild(docFrag);

    closeMenus();
    modal.classList.remove("hidden");
}

/******************************* TICKETS *******************************/
function createTicketMessage(message) {
    let html = `<div class="message">
                    <h3 class="message-sender">${message.MessageSender}</h3>
                    <h4 class="message-time">${Cmn.printDate(message.MessageTime, "dateTime")}</h4>
                    <p class="message-text">${message.MessageText}</p>
                </div>`;
    return document.createRange().createContextualFragment(html);
}

function createTicket(ticket) {
    var html = `<div id="t${ticket.TicketID}" class="ticket row">
                    <span class="t-subject" title="Subject: ${ticket.TicketSubject}">${ticket.TicketSubject}</span>
                    <span class="t-id" title="ID: ${Cmn.leadZeros(ticket.TicketID, 10)}">${Cmn.leadZeros(ticket.TicketID, 10)}</span>
                    <span class="t-date" title="Date Created: ${ticket.DateCreated}">${Cmn.printDate(ticket.DateCreated)}</span>
                    <span class="t-date" title="Date Modified: ${ticket.DateModified}">${Cmn.printDate(ticket.DateModified)}</span>
                    <span class="t-status${ticket.TicketStatus == "Open" ? " open" : ""}">${ticket.TicketStatus}</span>
                </div>`;

    var frag = document.createRange().createContextualFragment(html);
    frag.querySelector(`#t${ticket.TicketID}`).addEventListener("click", () => modalOpenTicket(ticket));
	return frag;
}

function createTickets(ctnr, tickets) {
    var	docFrag = document.createDocumentFragment();
    tickets.forEach(ticket => docFrag.appendChild(createTicket(ticket)));
    ctnr.appendChild(docFrag);
}

async function getTickets() {
    let response = await (await fetch("/php/get-tickets.php")).json();
    if (!response.Success) { Cmn.toast("Error getting tickets", "error"); }
    return response.Tickets;
}

function ticketsEmpty(tickets) {
    if (tickets.length < 1) {
        IdxGlobals.container.classList.add("empty");
        return true;
    } else {
        IdxGlobals.container.classList.remove("empty");
        return false;
    }
}

async function uploadTicket() {
    event.preventDefault();
    if (!Cmn.checkErrors([...this.elements])) { return Cmn.toast("Errors in form fields", "error"); }

    var formData = new FormData(this),
        response = await (await fetch("/php/create-ticket.php", {method: "POST", body: formData})).json();
    if (response.Success) {
        if (IdxGlobals.tickets.length < 1) { IdxGlobals.container.classList.remove("empty"); }
        IdxGlobals.tickets.push(response.Ticket);
        IdxGlobals.container.prepend(createTicket(response.Ticket));
        Cmn.toast("Ticket created", "success");
    } else {
        Cmn.toast(response.Message, "error");
    }
}