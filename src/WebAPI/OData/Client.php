<?php

/**
 * This file contains substantial portions of Xrm.Tools.CRMWebAPI by David Yack.
 * https://github.com/davidyack/Xrm.Tools.CRMWebAPI/tree/e454cd2c9f0c1fc96421f06753bd804db19ce73a/php
 *
 * Copyright (c) 2016 David Yack
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace AlexaCRM\WebAPI\OData;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Dynamics 365 Web API OData client.
 */
class Client {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * Guzzle HTTP Client.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * OData service metadata storage.
     *
     * @var Metadata
     */
    protected $metadata;

    /**
     * Guzzle-compatible authentication middleware.
     *
     * @var AuthMiddlewareInterface
     */
    protected $authMiddleware;

    /**
     * Client constructor.
     *
     * @param Settings $settings
     * @param AuthMiddlewareInterface $authMiddleware
     */
    function __construct( Settings $settings, AuthMiddlewareInterface $authMiddleware ) {
        $this->settings = $settings;
        $this->authMiddleware = $authMiddleware;

        $settings->logger->debug( 'Initializing Dynamics Web API Toolkit', [ 'settings' => $settings, 'authentication' => get_class( $authMiddleware ) ] );
    }

    /**
     * Returns the Guzzle HTTP client instance.
     *
     * @return HttpClient
     */
    public function getHttpClient() {
        if ( $this->httpClient instanceof HttpClient ) {
            return $this->httpClient;
        }

        $headers = [
            'Accept' => 'application/json',
            'OData-MaxVersion' => '4.0',
            'OData-Version' => '4.0',
        ];

        $handlerStack = HandlerStack::create();
        $handlerStack->push( $this->authMiddleware->getMiddleware() );

        $this->httpClient = new HttpClient( [
            'headers' => $headers,
            'verify' => $this->settings->caBundle !== null? $this->settings->caBundle : true,
            'handler' => $handlerStack,
        ] );

        return $this->httpClient;
    }

    /**
     * Retrieves OData Service Metadata.
     *
     * @return Metadata
     * @throws AuthenticationException
     * @throws TransportException
     */
    public function getMetadata() {
        if ( $this->metadata instanceof Metadata ) {
            return $this->metadata;
        }

        $cache = $this->getCachePool()->getItem( 'msdynwebapi.metadata' );
        if ( $cache->isHit() ) {
            $this->metadata = $cache->get();
            $this->getLogger()->debug( 'Loaded OData metadata from cache' );

            return $this->metadata;
        }

        $metadataURI = $this->settings->getEndpointURI() . '$metadata';
        try {
            $resp = $this->getHttpClient()->get( $metadataURI, [
                'headers' => [ 'Accept' => 'application/xml' ],
            ] );
            $this->getLogger()->debug( 'Retrieved OData metadata via ' . $metadataURI );
        } catch ( ConnectException $e ) {
            throw new TransportException( $e->getMessage(), $e );
        } catch ( RequestException $e ) {
            if ( $e->getResponse() === null ) {
                $this->getLogger()->error( "Guzzle failed to process the request GET {$metadataURI}", [ 'message' => $e->getMessage() ] );
                throw new TransportException( $e->getMessage(), $e );
            }

            $responseCode = $e->getResponse()->getStatusCode();
            if ( $responseCode === 401 ) {
                $this->getLogger()->error( 'Dynamics 365 rejected the access token', [ 'exception' => $e ] );
                $this->authMiddleware->discardToken();
                throw new AuthenticationException( 'Dynamics 365 rejected the access token', $e );
            }

            $this->getLogger()->error( 'Failed to retrieve OData metadata from ' . $metadataURI, [ 'responseCode' => $responseCode ] );
            throw new TransportException( 'Metadata request returned a ' . $responseCode . ' code', $e );
        }

        $metadataXML = $resp->getBody()->getContents();

        $this->metadata = Metadata::createFromXML( $metadataXML );
        $this->getCachePool()->save( $cache->set( $this->metadata ) );

        return $this->metadata;
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array $headers
     *
     * @return mixed|ResponseInterface
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    private function doRequest( $method, $url, $data = null, array $headers = [] ) {
        if ( $headers == null ) {
            $headers = [];
        }

        if ( in_array( $method, [ 'POST', 'PATCH' ] ) ) {
            $headers['Content-Type'] = 'application/json';
        }

        if ( $this->settings->callerID !== null ) {
            $headers['MSCRMCallerID'] = $this->settings->callerID;
        }

        try {
            $response = $this->getHttpClient()->request( $method, $url, [
                'headers' => $headers,
                'json' => $data,
            ] );
            $this->getLogger()->debug( "Completed {$method} {$url}", [ 'payload' => $data, 'responseHeaders' => $response->getHeaders(), 'responseBody' => $response->getBody()->getContents() ] );

            return $response;
        } catch ( RequestException $e ) {
            if ( $e->getResponse() === null ) {
                $this->getLogger()->error( "Guzzle failed to process the request {$method} {$url}", [ 'message' => $e->getMessage() ] );
                throw new TransportException( $e->getMessage(), $e );
            }

            $responseCode = $e->getResponse()->getStatusCode();
            if ( $responseCode === 401 ) {
                $this->getLogger()->error( 'Dynamics 365 rejected the access token', [ 'exception' => $e ] );
                $this->authMiddleware->discardToken();
                throw new AuthenticationException( 'Dynamics 365 rejected the access token', $e );
            }

            $response = json_decode( $e->getResponse()->getBody()->getContents() );
            if ( $responseCode !== 404 ) {
                $this->getLogger()->error( "Failed {$method} {$url}", [ 'payload' => $data, 'responseHeaders' => $e->getResponse()->getHeaders(), 'responseBody' => $response ] );
            } else {
                $this->getLogger()->notice( "Not Found {$method} {$url}", [ 'payload' => $data, 'responseHeaders' => $e->getResponse()->getHeaders(), 'responseBody' => $response ] );
            }

            throw new ODataException( $response->error, $e );
        } catch ( GuzzleException $e ) {
            $this->getLogger()->error( "Guzzle failed to process the request {$method} {$url}", [ 'message' => $e->getMessage() ] );
            throw new TransportException( $e->getMessage(), $e );
        }
    }

    /**
     * Builds the URL with request parameters specified in the query string.
     *
     * @param string $uri Request URI relative to the service endpoint.
     * @param array $queryOptions
     *
     * @return string
     */
    private function buildQueryURL( $uri, $queryOptions = null ) {
        $endpointURI = $this->settings->getEndpointURI() . $uri;
        $queryParameters = [];
        if ( $queryOptions != null ) {
            if ( isset( $queryOptions['Select'] ) && count( $queryOptions['Select'] ) ) {
                $queryParameters['$select'] = implode( ',', $queryOptions['Select'] );
            }
            if ( isset( $queryOptions['OrderBy'] ) && count( $queryOptions['OrderBy'] ) ) {
                $queryParameters['$orderby'] = implode( ',', $queryOptions['OrderBy'] );
            }
            if ( isset( $queryOptions['Filter'] ) ) {
                $queryParameters['$filter'] = $queryOptions['Filter'];
            }
            if ( isset( $queryOptions['Expand'] ) ) {
                $queryParameters['$expand'] = $queryOptions['Expand'];
            }
            if ( isset( $queryOptions['IncludeCount'] ) ) {
                $queryParameters['$count'] = 'true';
            }
            if ( isset( $queryOptions['Skip'] ) ) {
                $queryParameters['$skip'] = $queryOptions['Skip'];
            }
            if ( isset( $queryOptions['Top'] ) ) {
                $queryParameters['$top'] = $queryOptions['Top'];
            }
            if ( isset( $queryOptions['SkipToken'] ) ) {
                $queryParameters['$skiptoken'] = $queryOptions['SkipToken'];
            }
            if ( isset( $queryOptions['SystemQuery'] ) ) {
                $queryParameters['savedQuery'] = $queryOptions['SystemQuery'];
            }
            if ( isset( $queryOptions['UserQuery'] ) ) {
                $queryParameters['userQuery'] = $queryOptions['UserQuery'];
            }
            if ( isset( $queryOptions['FetchXml'] ) ) {
                $queryParameters['fetchXml'] = $queryOptions['FetchXml'];
            }
            $endpointURI .= '?' . http_build_query( $queryParameters );
        }

        return $endpointURI;
    }

    /**
     * Creates a collection of request headers from the query options.
     *
     * @param array $queryOptions
     *
     * @return array
     */
    private function buildQueryHeaders( $queryOptions = null ) {
        $headers = [];
        $prefer = [];

        if ( $queryOptions != null ) {
            if ( isset( $queryOptions['MaxPageSize'] ) ) {
                $prefer[] = 'odata.maxpagesize=' . $queryOptions['MaxPageSize'];
            }
        }

        $prefer[] = 'odata.include-annotations="*"';
        $headers['Prefer'] = implode( ',', $prefer );

        return $headers;
    }

    /**
     * @param $uri
     * @param null $queryOptions
     *
     * @return ListResponse
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function getList( $uri, $queryOptions = null ) {
        $url = $this->buildQueryURL( $uri, $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );

        $data          = json_decode( $res->getBody() );
        $result        = new ListResponse();
        $result->List  = $data->value;
        $result->Count = count( $data->value );

        $result->TotalRecordCount = -1;
        if ( isset( $data->{Annotation::CRM_TOTALRECORDCOUNT} ) ) {
            $result->TotalRecordCount = $data->{Annotation::CRM_TOTALRECORDCOUNT};
        }

        $result->TotalRecordCountLimitExceeded = false;
        if ( isset( $data->{Annotation::CRM_TOTALRECORDCOUNTLIMITEXCEEDED} ) ) {
            $result->TotalRecordCountLimitExceeded = $data->{Annotation::CRM_TOTALRECORDCOUNTLIMITEXCEEDED};
        }

        if ( isset( $data->{Annotation::ODATA_NEXTLINK} ) ) {
            $nlParts = parse_url( $data->{Annotation::ODATA_NEXTLINK} );
            $queryParts = [];
            parse_str( $nlParts['query'], $queryParts );
            $result->SkipToken = $queryParts['$skiptoken'];
        } elseif ( isset( $data->{Annotation::CRM_FETCHXMLPAGINGCOOKIE} ) ) {
            $result->SkipToken = $data->{Annotation::CRM_FETCHXMLPAGINGCOOKIE};
        }

        if ( !isset( $queryOptions['MaxPageSize'] ) && isset( $data->{Annotation::ODATA_NEXTLINK} ) ) {
            $nextLink = $data->{Annotation::ODATA_NEXTLINK};
            while ( $nextLink != null ) {
                $res = $this->doRequest( 'GET', $nextLink, null, $this->buildQueryHeaders( $queryOptions ) );

                $nextLink = null;
                $data         = json_decode( $res->getBody() );
                $result->List = array_merge( $result->List, $data->value );
                $result->Count = count( $result->List );

                $nextLink     = $data->{Annotation::ODATA_NEXTLINK} ?? null;
            }

            unset( $result->SkipToken );
        }

        return $result;
    }

    /**
     * @param $entityCollection
     * @param $entityId
     * @param null $queryOptions
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function getRecord( $entityCollection, $entityId, $queryOptions = null ) {
        $url = $this->buildQueryURL( sprintf( "%s(%s)", $entityCollection, $entityId ), $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );
        $result = json_decode( $res->getBody() );

        return $result;
    }

    /**
     * @param $uri
     * @param null $queryOptions
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function getCount( $uri, $queryOptions = null ) {
        $url = $this->buildQueryURL( sprintf( '%s/$count', $uri ), $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );
        $result = json_decode( $res->getBody() );

        return $result;
    }

    /**
     * @param $entityCollection
     * @param $data
     *
     * @return string|null
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function create( $entityCollection, $data ) {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $entityCollection );
        $res = $this->doRequest( 'POST', $url, $data );

        return static::getEntityId( $res );
    }

    /**
     * @param $entityCollection
     * @param $key
     * @param $data
     * @param bool $upsert
     *
     * @return string|null
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function update( $entityCollection, $key, $data, $upsert = false ) {
        $url     = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $key );
        $headers = [
            /*
             * Exploit the AutoDisassociate workaround to seamlessly disassociate lookup records
             * on record update.
             *
             * Utilizes the `AutoDisassociate: true` header with the corresponding read-only single-valued
             * navigation properties being set to NULL.
             *
             * Proven to work in Web API 9.0 and 9.1.
             *
             * {
             *   "_primarycontactid_value": null
             * }
             */
            'AutoDisassociate' => 'true',
        ];
        if ( $upsert ) {
            $headers['If-Match'] = '*';
        }

        $res = $this->doRequest( 'PATCH', $url, $data, $headers );

        return static::getEntityId( $res );
    }

    /**
     * @param $entityCollection
     * @param $entityId
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function delete( $entityCollection, $entityId ) {
        $url = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $entityId );
        $this->doRequest( 'DELETE', $url );
    }

    /**
     * @param $fromEntityCollection
     * @param $fromEntityId
     * @param $navProperty
     * @param $toEntityCollection
     * @param $toEntityId
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function associate( $fromEntityCollection, $fromEntityId, $navProperty, $toEntityCollection, $toEntityId ) {
        $url  = sprintf( '%s%s(%s)/%s/$ref', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty );
        $data = [ Annotation::ODATA_ID => sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId ) ];
        $this->doRequest( 'POST', $url, $data );
    }

    /**
     * @param $fromEntityCollection
     * @param $fromEntityId
     * @param $navProperty
     * @param $toEntityCollection
     * @param $toEntityId
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function disassociate( $fromEntityCollection, $fromEntityId, $navProperty, $toEntityCollection, $toEntityId ) {
        $url = sprintf( '%s%s(%s)/%s/$ref?$id=%s%s(%s)', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty, $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId );
        $this->doRequest( 'DELETE', $url );
    }

    /**
     * Executes a Web API function.
     *
     * @param string $functionName Function name.
     * @param array $parameters Function parameters.
     * @param string $entityCollection For bound functions -- entity set name.
     * @param string $entityId For bound functions -- entity record ID.
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function executeFunction( $functionName, $parameters = null, $entityCollection = null, $entityId = null ) {
        $paramvars   = [];
        $paramvalues = [];
        $paramcount  = 1;

        if ( $parameters != null ) {
            foreach ( $parameters as $key => $value ) {
                $paramvars[] = sprintf( "%s=@p%s", $key, $paramcount );
                $paramvalues[] = sprintf( "@p%s=%s", $paramcount, $value );
                $paramcount ++;
            }
            $url = sprintf( '%s%s(%s)?%s', $this->settings->getEndpointURI(), $functionName, implode( ',', $paramvars ), implode( '&', $paramvalues ) );
            if ( $entityCollection != null ) {
                $url = sprintf( '%s%s(%s)/%s(%s)?%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $functionName, implode( ',', $paramvars ), implode( '&', $paramvalues ) );
            }
        } else {
            $url = sprintf( '%s%s()', $this->settings->getEndpointURI(), $functionName );
            if ( $entityCollection != null ) {
                $url = sprintf( '%s%s(%s)/%s()', $this->settings->getEndpointURI(), $entityCollection, $entityId, $functionName );
            }
        }

        $res = $this->doRequest( 'GET', $url );
        $result = json_decode( $res->getBody() );
        unset( $result->{Annotation::ODATA_CONTEXT} );

        return $result;
    }

    /**
     * Executes a Web API action.
     *
     * @param string $actionName Action name.
     * @param array $parameters Action parameters.
     * @param string $entityCollection For bound actions -- entity set name.
     * @param string $entityId For bound actions -- entity record ID.
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function executeAction( $actionName, $parameters = null, $entityCollection = null, $entityId = null ) {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $actionName );
        if ( $entityCollection != null ) {
            $url = sprintf( '%s%s(%s)%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $actionName );
        }

        $res = $this->doRequest( 'POST', $url, $parameters );
        $result = json_decode( $res->getBody() );
        unset( $result->{Annotation::ODATA_CONTEXT} );

        return $result;
    }

    /**
     * Returns the Settings instance.
     *
     * @return Settings
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCachePool() {
        return $this->settings->cachePool;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() {
        return $this->settings->logger;
    }

    /**
     * Retrieves the ID of the newly created/updated entity record from response headers.
     *
     * @param ResponseInterface $response
     *
     * @return string|null
     */
    protected static function getEntityId( ResponseInterface $response ) {
        $entityId = $response->getHeader( 'OData-EntityId' );
        if ( count( $entityId ) === 0 ) {
            return null;
        }

        // Textual UUID representation is 36 characters long.
        $id = substr( $entityId[0], strrpos( $entityId[0], '(' ) + 1, 36 );
        if ( $id === false ) {
            return null;
        }

        return $id;
    }

}
