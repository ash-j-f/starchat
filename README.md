# Starchat - Cybersecurity and MVC example application

A web application designed to demonstrate the Model-View-Controller (MVC) design pattern and cybersecurity concepts in practice.

## Installation

To install the application server on a local machine, install as per instructions in INSTALL-WINDOWS.txt or INSTALL-UBUNTU-LINUX.txt

## Directories and Files

NOTE: The ".htaccess" files in the top level, and also in the /app and /public directories define the Apache URL rewrite access path 
that enable the MVC system. They also protect the /app and /conf directories from direct public access.
```
/public - Main index.php file.
    /js - All JavaScript files.
    /css - All CSS files.
    /images - All image files.
/conf 
    config.ini - Application config file
    init.sql - SQL initialisation file (run automatically, do not apply it 
        manually to the database)
/app
    /views - All MVC Views
    /models - All MVC models.
    /games - Files for inbuilt game logic (the Blackjack game).
    /core - Core files that deliver the MVC framework, database connection, 
        Google ReCaptcha, Markdown Text Parsing, etc.
    /controllers - All MVC controllers.
```

## Key Features

* Starchat is a multi user chat program.

* Web Interface - Project is accessed via an HTTP/HTTPS web browser client that connects to the remote application server.

* MVC Framework - Project built on PHP is split into Models, Views and Controllers.

* Database Storage - Built on a PostgreSQL database storage system.

* Multi-User - Any number of users can create accounts in the application to chat together in real-time.

* Security Systems - This application employs numerous industry-best-practice web security measures. See the "Security Features" section below.

* Built in Game - The card game of Blackjack can be played together by all members of a chat channel. It uses simple point scoring rules and does not permit gambling. Type "/?" in chat channel to see list of commands.


## Additional Features

* Database is automatically set up when the first query is run on an empty database. This means only an empty database and user account to access it need to be set up manually. See the files (Starchat)/app/core/DB.php, (Starchat)/conf/init.sql and instructions in the installation documents.

* An internal framework allows the easy addition of new games, so that more than just the Blackjack game can be added in future.

* User text can be enhanced using the Markdown text formatting system. Users can set their text bold, italic or strikethrough and more. See options listed on the application's Help page.

* URLs detected in user text in chats are automatically converted to clickable links.

* The majority of user features are accessed through a text command system. Type "/?" in the chat to see a list of commands.

* Gamers can list their Steam https://store.steampowered.com/ and Twitch https://www.twitch.tv/ accounts in their profile for everyone to see (see your Profile page by clicking name in top right of screen once logged in). See other user's profiles by clicking their names in the chat window or members list to the right of the chat window.

* Gamers can list the game they are currently playing for all to see (using the text command "/playing" to update what game they are playing). The game listing appears beside the user's name in hte members list to the right of the chat window.

* Gamers can advertise game streams on the Twitch service by using the "/stream" text command. Streams are advertised at the time they post them, and again automaticslly after a delay the user sets.

* Gamer profiles can be viewed by clicking the user name in the member list on a chat channel page, which include a short biography text.

* Users can send each other private messages in the chat. Type "/pm username message" to send a private message to someone in the same chat.

* Users can view and edit their own profile (and change their password, email or account name), by clicking their name in the top bar.

* JavaScript AJAX calls are used to update page contents without reloading page (Chat page). https://en.wikipedia.org/wiki/Ajax_(programming)

* The user interface features a professionally made UI using correctly implementing HTML5, CSS3 and JavaScript.

* The site has its own "site icon" (which appears in the browser tab next to the site and page title).

* As users start typing in a chat channel, other users will see "(username) is typing..." at the bottom of their message lists.

* Users can be given various roles in a chat channel by the channel owner, including "admin" and "moderator" roles. Moderators can add and remove users from private chats, and ban users from chats. Admins can perform all moderator roles, but also edit channel details.

* Channel ownership can be changed to a different user. See the "/owner" command in chat.

* Users can be set as "superusers" by listing their names in the SuperUser setting in config.ini. Superusers can delete users from the whole system, update any account password, and access full account details.

* An error handler displays fatal site error messages to the user on a nicely formatted error page rather than just raw text on a blank white screen.

* Errors that occur as the result of sending messages or commands on the chat page are displayed in chat as red text, providing users with error feedback during AJAX requests rather than failing silently.

* Professionally commented code base.


## Security Features

* Per-user authentication using the PHP Session ID and Session cookies system.

* Sessions have a short timeout period, meaning an inactive account will be logged out quickly.

* Passwords must be at least 8 characters long.

* Session cookies are marked "secure" which means they can be transmitted via HTTPS only.

* Session cookies are set to be readable by the browser only, and not by Javascript, which helps protect against XSS attacks that attempt to steal the session ID.

* Session cookies are set to "strict" mode, which means only session IDs generated by the server are accepted, not new session IDs defined by the client.

* Session ID is always regenerated on login and logout, which protects against Session Fixation attacks. https://www.owasp.org/index.php/Session_fixation

* Site can be hosted with end-to-end encryption in HTTPS/TLS/SSL mode (See the online hosted demo site at https://starchat.ajflynn.io/)

* HTTPS configuration uses latest Ciphers that support Perfect Forward Secrecy https://en.wikipedia.org/wiki/Forward_secrecy

* The HSTS header is used when hosting in HTTPS mode. This informs the browser to only ever connect to the site in HTTPS mode, protecting against man-in-the-middle attacks that strip the SSL layer. https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security

* The HTTPS system is confiured with OCSP stapling, which helps speed up the verification of certificates and certificate authorities. https://en.wikipedia.org/wiki/OCSP_stapling

* User passwords are hashed using the "bcrypt algorithm" (based on the Blowfish algorithm). This method is resistant to brute force cracking using GPU farms. https://www.php.net/manual/en/function.password-hash.php

* The site uses a Content Security Policy, which is a header that prevents the execution of malicious inline Javascript injected by an XSS attack. https://en.wikipedia.org/wiki/Content_Security_Policy This is defined in the file at (starchat)/app/views/static/header.php in the <head> section.

* HTML output is sanitised using the PHP "htmlentities" function which helps prevent XSS attacks through HTML and script injection.

* Null bytes are removed from user data to protect againts "Null Byte Injection" attacks https://resources.infosecinstitute.com/null-byte-injection-php/

* SQL Injection is prevented by using parametrised queries (also known as prepared queries) that separate the SQL from the input data. https://www.php.net/manual/en/function.pg-query-params.php

* POST method is used for all forms when submitting user data that updates the application's database or state. This helps prevent XSS attacks that can be made when input from the GET method is used instead.

* Tokens are used on all input forms. Tokens are unique codes that validate the form originated from the correct site. Tokens are not the same thing as a Session ID.

* All form submissions have the referring site in the request checked. If the referrer does not match the expected address of the host server then the form input is rejected.

* Time-constant string comparisons are used to compare sensitive strings such as password hashes. This protects against timing attacks. https://paragonie.com/blog/2015/11/preventing-timing-attacks-on-string-comparison-with-double-hmac-strategy

* Login rate is limited by a pause during each login check, as well as a table row lock during login. This means only one username/password check can be performed at a time per account no matter how many different computers try to log into the same account at once. This helps prevent large scale login attacks by Botnets, or password guessing.

* The Google ReCaptcha challenge system is used for login and signup. This helps prevent bots from attemting to log into accounts or to sign up to create spam accounts.

* The X-Frame-Deny header is set, which prevents the site from being embedded in another site's frames.

* The application's config.ini file has been made inaccessible via the public web. This is achieved by setting rules in the .htaccess files, as well as inserting a special code at the top of the config.ini file that causes it to exit immediately if it is read directly by the Apache webserver.

* Limits on the max number of new messages each user can create per minute prevent malicious users or bots flooding the server with messages and causing a denial of service or denial of database resource attack.


## References

* MVC framework based on ideas by:
	* https://github.com/panique and https://github.com/JaoNoctus and their simple MVC framework "MINI3" https://github.com/panique/mini3
	* An excellent MVC tutorial by https://codecourse.com/ at https://www.youtube.com/watch?v=OsCTzGASImQ&list=PLfdtiltiRHWGXVHXX09fxXDi-DqInchFD

## License

This project is licensed under the GNU General Public License v3.0. See the file "LICENSE" for more details.