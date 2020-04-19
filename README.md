# ABC Bank's Customer Support System
### This [customer support system](https://abcbank-support.herokuapp.com) is a prototype system for ABC Bank, a fictional institution, created as part of a two-semester project for my CIS 4800 & 5800 courses.

---
# Changelog
## Version 0.40 &nbsp;-&nbsp; (2020-04-18)
* **`index.php`** : Added markup for pagination
* **`css\home.css`** : Added selectors for pagination
* **`js\home.js`** : Added `emptyContainer(...)`, `initiliazePagination()`, `loadPage(...)`, `paginate(...)`, and related event listeners and global variables to support pagination
* **`js\home.js`** : Updated various variable names to make them more self-explanatory or shorter where applicable
* **`php\get-tickets.php`** : Updated module to support differentiation between Customers and Employees
* **`php\db-functions.php`** : Changed `getAllTickets(...)` to `getAccountTickets(...)` and added a new `getAlltickets()` that more appropriately matches the naming scheme
    * `getAccountTickets(...)` retains the same functionality of retrieving all tickets for a specific account, which is used in populating a customer's ticket table
    * The new `getAllTickets()` is used to get the 500 most recently modified tickets from all users for the purpose of populating the employee ticket queue
* **`sql\queries.sql`** : Added guidelines for how employee accounts are manually created
* **`sql\queries.sql`** : Updated `register(...)` query to include the `DateCreated` attribute in the `Account` table instead of the `Customer` table
* **`sql\queries.sql`** : Updated `getAccountTickets(...)` and `getAllTickets()` queries to coincide with aforementioned changes in `php\db-functions.php`

## Version 0.32 &nbsp;-&nbsp; (2020-04-14)
* **`php\create-ticket.php`** : Fixed bugs caused by incomplete / malformed returned ticket information
* **`php\restricted\db-functions.php`** : Fixed typo of variable name and returned string in `getFullName(...)`
* **`php\restricted\db-functions.php`** : Fixed typo with PDO causing fatal error on query execution in `getLoginInfo(...)`

## Version 0.31 &nbsp;-&nbsp; (2020-04-14)
* **`js\home.js`** : Removed testing related artifacts
* **`css\home.css`** : Fixed precedence bug with `.input-solid` class and its derivatives
* **`css\home.css`** : Fixed scaling on `textarea` elements

## Version 0.30 &nbsp;-&nbsp; (2020-04-14)
* **`js\home.js`** : Added `modalNewTicket()` as an interface for creating new tickets
* **`js\home.js`** : Added `modalOpenTicket(...)` as an interface for viewing existing tickets
* **`js\home.js`** : Added `createTickets(...)` to populate Tickets table with existing tickets
* **`js\home.js`** : Added `createTicket(...)` to support creation of individual ticket elements in `createTickets(...)`
* **`js\home.js`** : Added `createTicketMessage(...)` to support creation of individual message components of tickets in `modalOpenTicket(...)`
* **`js\home.js`** : Added `getTickets()` to support `createTickets(...)`
* **`js\home.js`** : Added `ticketsEmpty(...)` to check if the global `IdxGlobals.tickets` attribute is empty for miscellaneous support functions
* **`js\home.js`** : Added `uploadTicket()` to upload new tickets and create the elements for them in the Tickets table
* **`js\common.js`** : Added `printDate(...)` for pretty-printing SQL DateTime strings
* **`js\common.js`** : Added `leadZeros(...)` to maintain a consistent formatting for ticket IDs in the Tickets table
* **`js\common.js`** : Updated `isValid(...)` to support new `messageText` element and correct artifacts from migration of existing files
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