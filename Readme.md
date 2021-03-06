#API
Welcome to the new eZ Publish API, this code repository contains several layers of API, but this document will for the most part focus on the Public API.

##What is the Public API
The public API will give you an easy access to the content repository of eZ Publish. The content repository is the core component which manages content, locations (former Nodes), sections, content types (former Content Classes), user groups, users and roles. It also provides a new clear interface for plugging in custom field types (former Datatypes).

The public API is build on top of a layered architecture including a new persistence layer for abstracting the storage functionality. By using the public API your applications will be forward compatible to future releases based on enhanced, more scalable and more performant storage engines. Applications based on the public API are also fully backwards compatible by using the included storage engine based on the current kernel and database model.

## Alpha Notice
The API is still very much work in progress, and so is documentation, hence why this is currently labeled as a Developer Preview. But expect both parts to get into Beta shape as we close in on the launch of Annapurna (end of November), and fully stable by the time we release Etna (Q2 2012). But contribution is very open today, go for it !

##Directory Overview
* [design/](/ezsystems/ezp-next/tree/master/design/)	 *Early uml class diagrams*
* [doc/](/ezsystems/ezp-next/tree/master/doc/)  *Placeholder for bc doc and other doc that can not be on wiki or inline*
* [ezp/](/ezsystems/ezp-next/tree/master/ezp/)  *Main Namespace for eZ Publish API code*
* [ezp/Base](/ezsystems/ezp-next/tree/master/ezp/Base/)  *Base functionality that other modules depend on*
* [ezp/Content](/ezsystems/ezp-next/tree/master/ezp/Content/)  *Content related domain objects and services*
* [ezp/Io](/ezsystems/ezp-next/tree/master/ezp/Io/)  *Binaryfiles related objects, services and handlers*
* [ezp/Persistence](/ezsystems/ezp-next/tree/master/ezp/Persistence/)  *Persistence API (private api for now)*
* [ezp/Stubs](/ezsystems/ezp-next/tree/master/ezp/Stubs/)  *Code stubs for common code use*
* [ezp/User](/ezsystems/ezp-next/tree/master/ezp/User/)  *User related domain objects and services*
* config.php-RECOMMENDED  *Default config file (currently DEVELOPMENT config)*
* phpunit.xml  *PHPUnit 3.5+ xml configuration*
* Readme.md  *This text*
* testBootstrap.php  *Bootstrap for test system and testing*

##Requirements
* php: 5.3+
* Currently a good portion willingness to digg into the code

##Getting started
<insert link and/or explain it briefly>

##Bug tracker
Submitting bug reports is possible on http://issues.ez.no/ezpublish (pick the "ezp-next" component in the right column when reporting).

##Contributing
eZ Publish API is a fully open source, community-driven project. If you'd like to contribute, please have a look at the [related guidance page](http://share.ez.no/get-involved/develop). You will, amongst other, learn how to make pull-requests. More on this here : ["How to contribute to eZ Publish using GIT"](http://share.ez.no/learn/ez-publish/how-to-contribute-to-ez-publish-using-git).

##Discussing/Exchanging##
A dedicated forum has been set-up to discuss all PHP API-related topics : http://share.ez.no/forums/new-php-api

##Copyright and license
<insert>
