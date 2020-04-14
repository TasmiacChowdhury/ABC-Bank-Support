# ABC Bank's Customer Support System
### This [customer support system](https://abcbank-support.herokuapp.com) is a prototype system for ABC Bank, a fictional institution, created as part of a two-semester project for my CIS 4800 & 5800 courses.

---
# Changelog
## Version 0.31 &nbsp;-&nbsp; (2020-04-14)
* **`js\home.js`** : Removed testing related artifacts
* **`css\home.css`** : Fixed precedence bug with `.input-solid` class and its derivatives
* **`css\home.css`** : Fixed scaling on `textarea` elements

## Version 0.30 &nbsp;-&nbsp; (2020-04-14)
* **`js\home.js`** : Added `modalNewTicket()` function as an interface for creating new tickets
* **`js\home.js`** : Added `modalOpenTicket(...)` function as an interface for viewing existing tickets
* **`js\home.js`** : Added `createTickets(...)` function to populate Tickets table with existing tickets
* **`js\home.js`** : Added `createTicket(...)` function to support creation of individual ticket elements in `createTickets(...)`
* **`js\home.js`** : Added `createTicketMessage(...)` function to support creation of individual message components of tickets in `modalOpenTicket(...)`
* **`js\home.js`** : Added `getTickets()` function to support `createTickets(...)`
* **`js\home.js`** : Added `ticketsEmpty(...)` function to check if the global `IdxGlobals.tickets` attribute is empty for miscellaneous support functions
* **`js\home.js`** : Added `uploadTicket()` function to upload new tickets and create the elements for them in the Tickets table
* **`js\common.js`** : Added `printDate(...)` function for pretty-printing SQL DateTime strings
* **`js\common.js`** : Added `leadZeros(...)` function to maintain a consistent formatting for ticket IDs in the Tickets table
* **`js\common.js`** : Updated `isValid(...)` function to support new `messageText` element and correct artifacts from migration of existing files
* **`php\login.php`** : Updated successful login to create either `cid` or `eid` session variable for reference to CustomerID and EmployeeID, respectively, depending on the `acctype` session variable
* **`php\restricted\db-functions.php`** : Added `getCustomerID(...)`, `getEmployeeID(...)`, `getFullName(...)`, `getAllTickets(...)`, and `uploadTicket(...)` functions
* **`.gitignore`**: Removed `sql/` entry to reveal database setup file and queries used in `php\db-functions.php`
* **`index.php`** : Added markup for Tickets table and modals
* Added **`create-ticket.php`** and **`get-tickets.php`** to support creation of new tickets and fetching of existing tickets for related functions in **`home.js`**
* Updated **`common.css`** and **`home.css`** to support new ticket elements and correct artifacts from migration of existing files
* Updated references to `userID` variable and `uid` session variable in **`index.php`** and **`login.php`** to use `accountID` and `accid`, respectively, to coincide with database naming scheme
* Updated images on login page to remove artifacts from migration of existing files

## Version 0.20 &nbsp;-&nbsp; (2020-03-30)
* Initial commits of existing files from [OneMark](https://github.com/msihly/OneMark-Public)
    * Extensive adaptations to current project