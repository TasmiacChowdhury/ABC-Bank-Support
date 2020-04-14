/************************** TABLES **************************/
CREATE TABLE IF NOT EXISTS Account (
    AccountID       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Username        VARCHAR(40) NOT NULL,
    PasswordHash    VARCHAR(255) NOT NULL,
    DateCreated     DATETIME,
    AccountType     CHAR(1) NOT NULL DEFAULT "c",
        PRIMARY KEY (AccountID)
);

CREATE TABLE IF NOT EXISTS LoginSession (
    LoginSessionID  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    IPAddress       VARCHAR(45),
    SystemName      VARCHAR(75),
    SystemVersion   VARCHAR(75),
    BrowserName     VARCHAR(75),
    BrowserVersion  VARCHAR(75),
    DateAccessed    DATETIME,
    AccountID       INT UNSIGNED NOT NULL,
        PRIMARY KEY (LoginSessionID)
);

CREATE TABLE IF NOT EXISTS Token (
    TokenID         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    Selector        CHAR(20) NOT NULL UNIQUE,
    ValidatorHash   CHAR(64) NOT NULL,
    ExpiryDate      DATETIME NOT NULL,
    AccountID       INT UNSIGNED NOT NULL,
        PRIMARY KEY (TokenID)
);

CREATE TABLE IF NOT EXISTS Customer (
    CustomerID      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    FirstName       VARCHAR(100),
    LastName        VARCHAR(100),
    Email           VARCHAR(255) NOT NULL,
    RoutingNumber   VARCHAR(50) NOT NULL,
    AccountNumber   VARCHAR(50) NOT NULL,
    Verified        TINYINT(1) DEFAULT 0,
    Active          TINYINT(1) DEFAULT 1,
    AccountID       INT UNSIGNED NOT NULL,
        PRIMARY KEY (CustomerID)
);

CREATE TABLE IF NOT EXISTS Employee (
    EmployeeID      INT UNSIGNED NOT NULL,
    FirstName       VARCHAR(100) NOT NULL,
    LastName        VARCHAR(100) NOT NULL,
    AccountID       INT UNSIGNED NOT NULL,
        PRIMARY KEY (EmployeeID)
);

CREATE TABLE IF NOT EXISTS Ticket (
    TicketID        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    TicketSubject   VARCHAR(255),
    DateCreated     DATETIME,
    DateModified    DATETIME,
    TicketStatus    VARCHAR(20) DEFAULT "Open",
    AccountID       INT UNSIGNED NOT NULL,
        PRIMARY KEY (TicketID)
);

CREATE TABLE IF NOT EXISTS TicketLog (
    TicketLogID     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ActionTaken     VARCHAR(255) NOT NULL,
    ActionTime      DATETIME,
    TicketID		INT UNSIGNED NOT NULL,
    AccountID       INT UNSIGNED NOT NULL,
        PRIMARY KEY (TicketLogID)
);

CREATE TABLE IF NOT EXISTS TicketMessage (
    TicketMessageID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    MessageSender   VARCHAR(200),
    MessageText     TEXT,
    MessageTime     DATETIME,
    TicketID        INT UNSIGNED NOT NULL,
        PRIMARY KEY (TicketMessageID)
);

CREATE TABLE IF NOT EXISTS Attachment (
    AttachmentID    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    FilePath        VARCHAR(94) NOT NULL,
    Title           VARCHAR(255),
    Size            INT,
    DateCreated     DATETIME,
    TicketMessageID INT UNSIGNED NOT NULL,
        PRIMARY KEY (AttachmentID)
);

/************************** FOREIGN KEYS **************************/
ALTER TABLE LoginSession
    ADD CONSTRAINT fk_loginsession_account
        FOREIGN KEY (AccountID) REFERENCES Account (AccountID);

ALTER TABLE Token
    ADD CONSTRAINT fk_token_account
        FOREIGN KEY (AccountID) REFERENCES Account (AccountID);

ALTER TABLE Customer
    ADD CONSTRAINT fk_customer_account
        FOREIGN KEY (AccountID) REFERENCES Account (AccountID);

ALTER TABLE Employee
    ADD CONSTRAINT fk_employee_account
        FOREIGN KEY (AccountID) REFERENCES Account (AccountID);

ALTER TABLE Ticket
    ADD CONSTRAINT fk_ticket_account
        FOREIGN KEY (AccountID) REFERENCES Account (AccountID);

ALTER TABLE TicketLog
    ADD CONSTRAINT fk_ticketlog_ticket
        FOREIGN KEY (TicketID) REFERENCES Ticket (TicketID),
    ADD CONSTRAINT fk_ticketlog_account
        FOREIGN KEY (AccountID) REFERENCES Account (AccountID);

ALTER TABLE TicketMessage
    ADD CONSTRAINT fk_ticketmessage_ticket
        FOREIGN KEY (TicketID) REFERENCES Ticket (TicketID);

ALTER TABLE Attachment
    ADD CONSTRAINT fk_attachment_ticketmessage
        FOREIGN KEY (TicketMessageID) REFERENCES TicketMessage (TicketMessageID);

/************************** TESTING **************************/
/*
INSERT INTO Ticket (TicketSubject, DateCreated, DateModified, TicketStatus, AccountID)
    VALUES ('Advance RMA for ticket#: 885668 (Original part CMK8GX4M2B3000C15 is OOS)', '2020-03-25 12:30:00', '2020-04-10 05:43:36', 'Open', 11);

INSERT INTO Ticket (TicketSubject, DateCreated, DateModified, TicketStatus, AccountID)
    VALUES ('Unclosed RMA', '2020-03-25 12:31:00', '2020-04-09 05:43:36', 'Closed', 41);

INSERT INTO TicketMessage (MessageText, TicketID)
    VALUES ('Hello Tester,

I will be creating a prepaid label for you to send in the first defective DRAM kit, once that has shipped back to us, we will cross ship you another DRAM kit. Please let us know when you\'ve shipped back the first defective kit So I can create another ticket to process the cross shipping. You would be sending the DRAM kit under RMA # 1176128.

Please let me know if you have any questions I can help assist you with.

Best,
Michael', 1);

INSERT INTO TicketMessage (MessageText, TicketID)
    VALUES ('Hello Tester,

We are processing your replacement. Please check out ticket 901570.

Best,
Michael', 1);

INSERT INTO TicketMessage (MessageText, TicketID)
    VALUES ('Hi Tester,

Thanks for returning your item. We have processed the refund for your Advanced Replacement charge. P

Refund Transaction ID: 927468350217889465923
Refund Amount: 125.00

Please feel free to reach out should you have any other questions.

Regards,
RAINEIR', 11);
*/