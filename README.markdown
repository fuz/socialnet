# SocialNet

This is a very early proof-of-concept for a social network that does not depend on a centralised server and instead uses third party services in order to communicate.

## Design Decisions
Early design decisions include:

* **Standardised Protocol** Nodes communicate with a standardised file format.
* **Protocol Agnostic** Nodes on the network can communicate by different methods. This is implemented by a pluggable **driver**.
* **Security & Privacy** Communication between nodes is encrypted and signed to improve data integrity and privacy.
* **Extensibility** Anyone should be able to write interfaces to new protocols or applications that use the infrastructure as a backbone.
* **Client Agnostic** The client should not depend on any particular technology. It could be web or desktop based and no client is more 'official' than any other.

## Development Approach
### Architecture

The core data structure for SocialNet is **Packets**. Other concepts are:

 * **User** a person with a SocialNet account
 * **Actions** the actions of a user.
 * **Node** a client on the network
 * **Client** the SocialNet software running on a user's machine
 * **Accounts** contain all your information, including:
   * **User Profile** Publicly viewable information for a User, including connection details needed by a driver **sending** to contract this user.
   * **Identity** Contains private information such as private keys and communication driver information for **receiving packets**
 * **Drivers** interfaces for communicating with other users

The SocialNet should be simple to explain and understand. SocialNet clients have two 'mailboxes' for communicating with other nodes:

 * **inpackets** For packets that have been received but have yet to be processed.
 * **outpackets** For packets that have been generated and need to be transmitted.

This is essentially like a mail client's inbox and outbox. The proof of concept currently implements this at the file system level: files that appear in either folder will be processed accordingly. 

Packets are metadata files that connect people to actions and to other people or their actions. The current packet implementation uses XML. Usually a packet corresponds to a single action.

Example packets include:

 * **Status Updates** When a user adjusts a status message.
 * **Comments** A comment on another action.
 * **Photographs** When a user uploads photographs.

### Client Implementation

The design of the client is completely up to the implementor. The proof of concept is implemented like so: 

 * user data is stored inside a SQLite database 
 * Other people's **User Profiles** are cached in a Contact table 
 * Packets are parsed and processed and inserted into a Feed table.
 * When accessed, the proof of concept queries the feed table and draws an interface.


## Drivers

Nodes negotiate via an arbitrary communication mechanism through **drivers**. A driver must do the following:
 
 * transmit a Packet to another node given the Packet to send and the recipient's **User Profile**
 * receive Packets from other nodes

Currently an Email and HTTP POST driver have been implemented. The Email driver requires IMAP and SMTP details to function and this is stored in the Account.

The **Identity** contains the private information for **receiving packets** such as IMAP/POP/SMTP passwords. The **User Profile** of a recipient contains details needed to contact a user, such as an **email address**.

## To Do


### Establish Stable Interface

 * The driver interface needs to be standardized. The communication data exposed by a User Profile should be flexible enough to support any mechanism of communication, be it IP addresses, Jabber IDs or DHT.
 * The packet formats need to be standardized and versioned. Clients may not support all features for a given protocol version. Need to establish behaviour of receiving a packet that is not understood.
 * Need to standardize packet choreographies so that all implementations handle packets in a consistent and interoperable way.
 

## Search & Discoverability

The decentralised design makes search a little harder because there are not any central servers that hold all information for all users. Instead, search is expected to be a distributed and collaborative. Users can help one another find users they are looking for. This must be secure so as not to leak any information that people do not wish to disclose.

This is difficult because:

 * you do not want everyone to know who you are looking for but you need to tell them for them to help you
 * you do not want to leak your information to everyone to help other people find you

One approach to implementing search is the following protocol:

 * users specify what information they wish to make available to be searched 
 * whenever a user is added to your contracts, all personal details received are hashed and inserted into a contact hashcache 
 * a user who wishes to search for someone hashes each field of the search and salts it with the current user token as the salt (the user token is regenerated after every transmission between two users)
 * the hash is transmitted to your contacts
 * contacts scan their contact hashes and when they detect a match, they transmit a 'search request' to the user being sought containing the search parameters

This way you know who is searching for you and they must disclose themselves. You could even designate a piece of information that seekers must provide in order to find you as this will be used as the salt for all other fields. This will also not be disclosed to ANY of your contacts.

## Libraries Used

 * **PHP Frontend**
  * [phamlp - PHP Haml Library][] - for the PHP frontend rendering
  * SQLite - to store the Account
  * openssl functions
 * **Java Frontend**
  * Bouncy Castle

[phamlp - PHP Haml Library]:http://code.google.com/p/phamlp/ 
