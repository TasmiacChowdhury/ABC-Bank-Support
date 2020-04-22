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
        "id": "paginator-left",
        "eventType": "click",
        "function": function() {
            if (this.classList.contains("disabled")) { return false; }
            loadPage(Idx.curPage - 1);
            paginate(Idx.curPage - 1);
        }
    }, {
        "id": "paginator-right",
        "eventType": "click",
        "function": function() {
            if (this.classList.contains("disabled")) { return false; }
            loadPage(Idx.curPage + 1);
            paginate(Idx.curPage + 1);
        }
    }, {
        "id": "rt-f",
        "eventType": "submit",
        "function": replyToTicket
    }, {
        "id": "sidemenu",
        "eventType": "click",
        "function": toggleMenu
    }, {
        "dataListener": "acc-btn",
        "eventType": "click",
        "function": function(event) {
            Cmn.switchPanel(Idx.accCurPanel, event.target.dataset.panel);
            Idx.accCurPanel = event.target.dataset.panel;
            Cmn.switchTab(Idx.accCurTab, event.target.id);
            Idx.accCurTab = event.target.id;
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
        "dataListener": "paginate",
        "eventType": "click",
        "function": function() {
            loadPage(this.dataset.page);
            paginate(this.dataset.page);
        }
    }, {
        "domObject": document,
        "eventType": "click",
        "function": closeMenus
    }
];

const Idx = {
    accCurTab: "acc-tab-info",
    accCurPanel: "acc-panel-info",
    curPage: 1,
    openMenus: [],
    pagesContainer: "",
    pageLeft: "",
    pageRight: "",
    tickets: [],
    ticketsContainer: "",
    ticketsPerPage: 10,
    totalPages: 1
}

window.addEventListener("DOMContentLoaded", async function() {
    Idx.tickets = await getTickets();
    Idx.ticketsContainer = document.getElementById("tickets");
    Idx.pagesContainer = document.getElementById("pages");
    Idx.pageLeft = document.getElementById("paginator-left");
    Idx.pageRight = document.getElementById("paginator-right");

    if (!ticketsEmpty(Idx.tickets)) {
        Idx.totalPages = Math.ceil(Idx.tickets.length / Idx.ticketsPerPage);
        initializePagination();
    }

    Cmn.addListeners(eventListeners);
});

/******************************* GENERAL *******************************/
function closeMenus() {
    if (Idx.openMenus.length > 0) {
        Idx.openMenus.forEach(e => {
            e.classList.toggle("hidden");
            Idx.openMenus.shift();
        });
    }
}

function toggleMenu() {
    event.stopPropagation();
    var ele = this || event.target,
        menu = document.getElementById(ele.dataset.menu);
    if (!Idx.openMenus.includes(menu)) {
        closeMenus();
        menu.classList.toggle("hidden");
        Idx.openMenus.push(menu);
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

function modalClose(del = false, modalID = null) {
    event.stopPropagation();
	var modal = document.getElementById(modalID || event.target.dataset.modal);
	if ((modalID && modal) || event.target == modal || event.target.classList.contains("close")) { del ? modal.remove() : modal.classList.add("hidden"); }
}

function modalNewTicket() {
    var [modal, formSubject, formMessageText] = document.querySelectorAll("#nt-modal, #nt-f-subject, #nt-f-messageText");

    formSubject.value = "";
    formMessageText.value = "";

    closeMenus();
	modal.classList.remove("hidden");
}

function modalOpenTicket(ticket) {
    var [modal, outerContainer] = document.querySelectorAll("#t-modal, #messages-outer"),
        docFrag = document.createDocumentFragment();

    outerContainer.innerHTML = `<h2 class="message-subject">${ticket.TicketSubject}</h2>
                                <div class="message-buttons">
                                    <span class="message-button${ticket.TicketStatus == "Closed" ? " disabled" : ""}" id="close-ticket">Close</span>
                                    <span class="message-button${ticket.TicketStatus == "Closed" ? " disabled" : ""}" id="reply-ticket">Reply</span>
                                </div>
                                <div id="messages-inner"></div>`;

    let alertOptions = {
        text: "Are you sure you want to mark this ticket as closed? You will not be able to make further updates.",
        buttons: [{
            btnText: "Confirm",
            fn: () => {
                closeTicket(ticket.TicketID);
                modalClose(true, event.target.dataset.modal);
                modalClose(false, "t-modal");
            }}, {
            btnText: "Cancel",
            fn: () => modalClose(true),
            class: "close"
        }]
    };

    if (ticket.TicketStatus == "Open") {
        document.getElementById("close-ticket").addEventListener("click", () => document.body.appendChild(Cmn.createAlert(alertOptions)));
        document.getElementById("reply-ticket").addEventListener("click", () => modalReplyTicket(ticket.TicketID));
    }

    ticket.Messages.forEach(i => docFrag.appendChild(createTicketMessage(i)));
    let innerContainer = document.getElementById("messages-inner");
    innerContainer.appendChild(docFrag);

    closeMenus();
    modal.classList.remove("hidden");
}

function modalReplyTicket(ticketID) {
    let replyModal = document.getElementById("rt-modal"),
        replyForm = document.getElementById("rt-f");
    replyForm.reset();
    replyForm.dataset.ticketID = ticketID;
    replyModal.classList.remove("hidden");
}

/******************************* TICKETS *******************************/
async function closeTicket(ticketID) {
    let formData = new FormData();
    formData.append("ticketID", ticketID);

    let response = await (await fetch("/php/close-ticket.php", {method: "POST", body: formData})).json();
    if (response.Success) {
        let ticket = Idx.tickets[Idx.tickets.findIndex(({TicketID}) => TicketID == ticketID)],
            ticketElement = document.getElementById(`t${ticketID}`),
            status = ticketElement.lastElementChild,
            dateModified = status.previousElementSibling;

        ticket.status = "Closed";
        ticket.DateModified = response.DateModified;

        status.classList.remove("open");
        status.innerHTML = "Closed";

        dateModified.title = `Date Modified: ${response.DateModified}`;
        dateModified.innerHTML = Cmn.printDate(response.DateModified);

        Cmn.toast(`Ticket #${ticketID} closed`, "success");
    } else {
        Cmn.toast(`Failed to close ticket #${ticketID}`, "error");
    }
    return response.Success;
}

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

function createTickets(container, tickets) {
    var	docFrag = document.createDocumentFragment();
    tickets.forEach(ticket => docFrag.appendChild(createTicket(ticket)));
    container.appendChild(docFrag);
}

async function getTickets() {
    let response = await (await fetch("/php/get-tickets.php")).json();
    if (!response.Success) { Cmn.toast("Error getting tickets", "error"); }
    response.Tickets.forEach(e => e.Messages = [...Object.values(e.Messages)]);
    return response.Tickets;
}

function ticketsEmpty(tickets) {
    if (tickets.length < 1) {
        Idx.ticketsContainer.classList.add("empty");
        return true;
    } else {
        Idx.ticketsContainer.classList.remove("empty");
        return false;
    }
}

async function uploadTicket() {
    event.preventDefault();
    if (!Cmn.checkErrors([...this.elements])) { return Cmn.toast("Errors in form fields", "error"); }

    var formData = new FormData(this),
        response = await (await fetch("/php/create-ticket.php", {method: "POST", body: formData})).json();
    if (response.Success) {
        if (Idx.tickets.length < 1) { Idx.ticketsContainer.classList.remove("empty"); }
        Idx.tickets.push(response.Ticket);
        Idx.ticketsContainer.prepend(createTicket(response.Ticket));
        Cmn.toast("Ticket created", "success");
    } else {
        Cmn.toast(response.Message, "error");
    }
}

async function replyToTicket() {
    event.preventDefault();
    if (!Cmn.checkErrors([...this.elements])) { return Cmn.toast("Errors in form fields", "error"); }

    let ticketID = this.dataset.ticketID,
        ticket = Idx.tickets[Idx.tickets.findIndex(({TicketID}) => TicketID == ticketID)],
        formData = new FormData(this);
    formData.append("ticketID", ticketID);
    let response = await (await fetch("/php/reply-ticket.php", {method: "POST", body: formData})).json();
    if (response.Success) {
        ticket.DateModified = response.DateModified;
        ticket.Messages.push(response.Message);

        document.getElementById("messages-inner").prepend(createTicketMessage(response.Message));

        Cmn.toast(`Ticket #${ticketID} updated`, "success");
    } else {
        Cmn.toast(response.Message, "error");
    }
}

/******************************* PAGINATION *******************************/
function emptyContainer(container) {
    while (container.firstChild) { container.removeChild(container.firstChild); }
}

function initializePagination() {
    for (let i = 1; i < Math.min(Idx.totalPages, 5) + 1; i++) {
        Idx.pagesContainer.insertAdjacentHTML("beforeend", `<span class="page-num" data-page=${i} data-listener="paginate">${i}</span>`);
    }
    Idx.pagesContainer.firstChild.classList.add("active");
    loadPage(Idx.curPage);
}

function loadPage(page) {
    var end =  page * Idx.ticketsPerPage,
        start = end - Idx.ticketsPerPage,
        displayedTickets = Idx.tickets.slice(start, end);
    emptyContainer(Idx.ticketsContainer);
    createTickets(Idx.ticketsContainer, displayedTickets);
}

function paginate(page) {
    page = +page;
    if (page == Idx.curPage) { return false; }
    else if (page < 1) { page = 1; }
    else if (page > Idx.totalPages) { page = Idx.totalPages; }

    let pages = [...Idx.pagesContainer.childNodes],
        startPage = page < 4 ? 1 : page - 2,
        endPage = startPage + 4;
    if (endPage > Idx.totalPages) {
        endPage = Idx.totalPages;
        startPage = endPage - 4 > 0 ? endPage - 4 : 1;
    }

    let pageNums = Array.from({length: endPage + 1 - startPage}, (_, i) => i + startPage);
    pages.forEach((p, i) => {
        p.dataset.page = pageNums[i];
        p.innerHTML = p.dataset.page;
    });

    let active = document.querySelector(".page-num.active"),
        leftClasses = Idx.pageLeft.classList,
        rightClasses = Idx.pageRight.classList;
    if (active) { active.classList.remove("active"); }
    pages[pages.findIndex(e => e.dataset.page == page)].classList.add("active");
    page > 1 ? leftClasses.remove("disabled") : leftClasses.add("disabled");
    page < Idx.totalPages ? rightClasses.remove("disabled") : rightClasses.add("disabled");

    Idx.curPage = page;
}