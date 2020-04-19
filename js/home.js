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
    var [modal, container] = document.querySelectorAll("#t-modal, #t-messages"),
        docFrag = document.createDocumentFragment();

    Object.keys(ticket.Messages).forEach(i => docFrag.appendChild(createTicketMessage(ticket.Messages[i])));

    container.innerHTML = "";
    container.insertAdjacentHTML("afterbegin", `<h2 class="message-subject">${ticket.TicketSubject}</h2>`);
    container.appendChild(docFrag);

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

function createTickets(container, tickets) {
    var	docFrag = document.createDocumentFragment();
    tickets.forEach(ticket => docFrag.appendChild(createTicket(ticket)));
    container.appendChild(docFrag);
}

async function getTickets() {
    let response = await (await fetch("/php/get-tickets.php")).json();
    if (!response.Success) { Cmn.toast("Error getting tickets", "error"); }
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