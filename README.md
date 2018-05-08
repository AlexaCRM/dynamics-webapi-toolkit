# Dynamics Web API Toolkit

This library facilitates connection to Microsoft Dynamics 365 via Web API in PHP applications. Create, read, update and delete CRM records easily via the [IOrganizationService](https://msdn.microsoft.com/en-us/library/microsoft.xrm.sdk.iorganizationservice.aspx)-compatible interface, as well as execute Web API actions and functions.

## Features & Limitations

The library lacks support of following features (supported features and scenarios are mentioned along the way):

- Authentication against IFD and On-Premises deployments. IFD is planned and will be delivered relatively soon, On-Premises deployments are under consideration.
- Although IOrganizationService interface is provided, Execute method is not supported yet. Means for executing functions and actions, both bound and unbound, are provided though.
- Batch requests are not supported yet. That means, associating/disassociating multiple records is executed in multiple separate requests. That may affect performance.
- User impersonation is not supported yet, will be delivered soon.
- Organization metadata (entities and attributes, global option sets, etc.) is not supported yet, although can be retrieved manually via the built-in OData helper client or via the HTTP client.
- Most of the record attribute values are returned as-is from Web API. That means, at this point you must expect integers in place of OptionSetValue objects for picklist values, Status/State attributes, booleans for "Two Options" attributes, and floats for decimal and Money attributes. Lookup attribute values are rendered as EntityReference objects. The same is true when you set values in the Entity, including EntityReferences. This is likely to change once organization metadata is integrated into the toolkit.

## Getting Started

### Prerequisites

The main requirement is PHP 7.0 or later. cURL is recommended but is not required. [Composer](https://getcomposer.org/) is required to install the toolkit and its dependencies.

### Installing

```
$ composer require alexacrm/dynamics-webapi-toolkit
```

### Consuming

See the [Tutorial](https://github.com/AlexaCRM/dynamics-webapi-toolkit/wiki/Tutorial) to learn how to consume the library.

## Built With

* David Yack's [Xrm.Tools.CRMWebAPI](https://github.com/davidyack/Xrm.Tools.CRMWebAPI) - some code was borrowed as OData helper code
* [Guzzle](https://github.com/guzzle/guzzle) - an extensible PHP HTTP client

## Versioning

Currently the toolkit code is not tagged. Once the library is stable API-wise, we will use [SemVer](http://semver.org/) for versioning. 

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
