# Dynamics Web API Toolkit

This library facilitates connection to Microsoft Dynamics 365 via Web API in PHP applications. Create, read, update and delete CRM records easily, as well as execute Web API actions and functions via the [IOrganizationService](https://msdn.microsoft.com/en-us/library/microsoft.xrm.sdk.iorganizationservice.aspx)-compatible interface.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

The main requirement is PHP 7.0 or later. cURL is recommended but is not required. [Composer](https://getcomposer.org/) is required to install the toolkit and its dependencies.

### Installing

```
$ composer require alexacrm/dynamics-webapi-toolkit
```

## Built With

* David Yack's [Xrm.Tools.CRMWebAPI](https://github.com/davidyack/Xrm.Tools.CRMWebAPI) -- some code was borrowed as OData helper code
* [Guzzle](https://github.com/guzzle/guzzle) -- an extensible PHP HTTP client

## Versioning

Currently the toolkit code is not tagged. Once the library is stable API-wise, we will use [SemVer](http://semver.org/) for versioning. 

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
