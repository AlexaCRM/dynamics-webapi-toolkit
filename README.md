# Dynamics Web API Toolkit

The Dynamics Web API Toolkit provides an easy-to-use PHP wrapper for the [Dynamics 365 Customer Engagement Web API](https://docs.microsoft.com/en-au/dynamics365/customer-engagement/developer/use-microsoft-dynamics-365-web-api).

Create, read, update and delete CRM records easily via the [IOrganizationService](https://msdn.microsoft.com/en-us/library/microsoft.xrm.sdk.iorganizationservice.aspx) - compatible interface, as well as execute Web API actions and functions.

See [the tutorial](https://github.com/AlexaCRM/dynamics-webapi-toolkit/wiki/Tutorial) for the sample code to instantiate the connection, create, retrieve, update and delete records.

This toolkit supports **only** Dynamics 365 Web API. For PHP implementation of the Dynamics 365 SOAP interface, see [php-crm-toolkit project](https://github.com/AlexaCRM/php-crm-toolkit).

## Features & Limitations

The current release of the library does not support the following features (supported features and scenarios are mentioned along the way):

- Authentication support for IFD and On-Premises (AD) deployments. Support for IFD (Internet Facing Deployment) is on the [roadmap](https://github.com/AlexaCRM/dynamics-webapi-toolkit/projects/1), On-Premises deployments (using AD) are under consideration.
- Execute method of IOrganizationService interface is not supported yet. Means for executing functions and actions, both bound and unbound, are provided though.
- Batch requests are not supported yet. That means, associating/disassociating multiple records is executed in multiple separate requests which may affect the performance.
- Organization metadata (entities and attributes, global option sets, etc.) is not supported yet, although can be retrieved manually via the built-in OData helper client or via the HTTP client.
- Most of the record attribute values are returned as-is from Web API. That means, at this point you must expect integers in place of OptionSetValue objects for picklist values, Status/State attributes, booleans for "Two Options" attributes, and floats for decimal and Money attributes. Lookup attribute values are rendered as EntityReference objects. The same applies when you set values in the Entity, including EntityReferences. This is likely to change once organization metadata is integrated into the toolkit.

## Getting Started

### Prerequisites

The main requirement is PHP 7.4 or later. cURL is recommended but is not required. [Composer](https://getcomposer.org/) is required to install the toolkit and its dependencies.

### Installing

```
$ composer require alexacrm/dynamics-webapi-toolkit:dev-master
```

### Consuming

See the [Tutorial](https://github.com/AlexaCRM/dynamics-webapi-toolkit/wiki/Tutorial) to learn how to consume the library.

## Development

The version compatible with PHP 8.2 and above is now available as `v4.x-dev`. Please note that this version is still under development, and its use is at your own risk. You can install it with the following command:

```
$ composer require alexacrm/dynamics-webapi-toolkit:v4.x-dev
```

## Built With

* David Yack's [Xrm.Tools.CRMWebAPI](https://github.com/davidyack/Xrm.Tools.CRMWebAPI) - some code was borrowed as OData helper code
* [Guzzle](https://github.com/guzzle/guzzle) - an extensible PHP HTTP client

## Versioning

Toolkit uses [SemVer](http://semver.org/) for versioning.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
