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
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Dynamics 365 Web API OData client.
 */
class Client {

    protected Settings $settings;

    /**
     * Guzzle HTTP Client.
     */
    protected ?HttpClient $httpClient = null;

    /**
     * OData service metadata storage.
     */
    protected ?Metadata $metadata = null;

    /**
     * Guzzle-compatible authentication middleware.
     */
    protected AuthMiddlewareInterface $authMiddleware;

    /**
     * Collection of Guzzle-compatible middleware.
     *
     * @var MiddlewareInterface[]
     */
    protected array $middlewares = [];

    /**
     * header to Web API request
     *
     * @var array
     */
    protected array $webApiHeaders = [];

    /**
     * Client constructor.
     *
     * @param Settings $settings
     * @param AuthMiddlewareInterface $authMiddleware
     * @param MiddlewareInterface[] $middlewares
     */
    function __construct( Settings $settings, AuthMiddlewareInterface $authMiddleware, MiddlewareInterface ...$middlewares ) {
        $this->settings = $settings;
        $this->authMiddleware = $authMiddleware;
        $this->middlewares = $middlewares;

        $settings->logger->debug( 'Initializing Dynamics Web API Toolkit', [
            'settings' => $settings,
            'authentication' => get_class( $authMiddleware ),
            'handlers' => array_map( function( $middleware ) {
                return get_class( $middleware );
            }, $middlewares ),
        ] );
    }

    /**
     * Returns the Guzzle HTTP client instance.
     */
    public function getHttpClient(): HttpClient {
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

        foreach ( $this->middlewares as $middleware ) {
            $handlerStack->push( $middleware->getMiddleware() );
        }

        $verify = $this->settings->caBundle;
        if ( $verify === null ) {
            $verify = $this->settings->tlsVerifyPeers;
            if ( $verify && $this->settings->caBundlePath !== null ) {
                $verify = $this->settings->caBundlePath;
            }
        }

        $this->httpClient = new HttpClient( [
            'headers' => $headers,
            'verify' => $verify,
            'handler' => $handlerStack,
            'cookies' => true,
        ] );

        return $this->httpClient;
    }

    /**
     * Retrieves OData Service Metadata.
     *
     * @return Metadata
     * @throws AuthenticationException
     * @throws GuzzleException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function getMetadata(): Metadata {
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
     * Get header for Web Api Request
     *
     * @param array|null $headers
     * @param string|null $method
     *
     * @return array
     */
    private function getRequestHeaders( ?array $headers = [], ?string $method = 'GET' ): array {
        $headers = array_merge( $headers, [
            'Accept' => 'application/json',
            'OData-MaxVersion' => '4.0',
            'OData-Version' => '4.0',
        ] );

        if ( in_array( $method, [ 'POST', 'PATCH' ] ) ) {
            $headers = array_merge( [ 'Content-Type' => 'application/json' ], $headers );
        }

        //Odata/Client/Setting more important than the headers in the request
        if ( $this->settings->callerObjectId || $this->settings->callerID ) {
            $this->unsetWebApiHeaderByName( 'CallerObjectId' );
            $this->unsetWebApiHeaderByName( 'MSCRMCallerID' );
        }

        if ( $this->settings->callerObjectId !== null ) {
            $headers = array_merge( [ 'CallerObjectId' => $this->settings->callerObjectId ], $headers );
        } elseif ( $this->settings->callerID !== null ) {
            $headers = array_merge( [ 'MSCRMCallerID' => $this->settings->callerID ], $headers );
        }

        return $headers;
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array $headers
     *
     * @return ResponseInterface
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    private function doRequest( string $method, string $url, $data = null, array $headers = [] ): ResponseInterface {
        $headers = $this->getRequestHeaders( $headers, $method );

        try {
            $payload = [
                'headers' => $headers,
            ];
            if ( !isset( $headers['Content-Type'] ) || $headers['Content-Type'] === 'application/json' ) {
                $payload['json'] = $data;
            } else {
                $payload['body'] = $data;
            }

            $cache = $this->getCachePool()->getItem( 'msdynwebapi.cookiesjar' );
            if ( $cache->isHit() ) {
                $savedCookies = $cache->get();
                if ( $savedCookies instanceof CookieJar ) {
                    $payload['cookies'] = $savedCookies;
                    $this->getLogger()->debug( 'Found request cookies in cache' );
                }
            }

            // Start timing
            $startTime = microtime( true );
            $response = null;
            
            try {
				$this->getLogger()->debug( "Starting {$method} {$url}", [
                	'payload' => $payload,
                ] );
                
                $response = $this->getHttpClient()->request( $method, $url, $payload );

            } catch ( ClientException $e ) {
                if ( $this->authMiddleware instanceof OnPremiseAuthMiddleware && $e->getResponse()?->getStatusCode() === 401 ) {
                    $this->authMiddleware->refreshToken();
                    $response = $this->getHttpClient()->request( $method, $url, $payload );
                } else {
                    $response = $e->getResponse() ?? null;
                    $this->exceptionHandler( $e, $method, $url, $data );
                    throw new ODataException( $response?->getStatusCode(), $e );
                }
            } finally {
	            // Calculate timing
            	$endTime = microtime( true );
            	$duration = round( ( $endTime - $startTime ) * 1000, 2 ); // Convert to milliseconds

                $this->getLogger()->debug( "Completed {$method} {$url}", [
                    'responseHeaders' => $response?->getHeaders(),
                    'responseBody' => $response?->getBody()?->getContents(),
                    'timing' => [
                        'duration_ms' => $duration,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                    ],
                ] );
            }

            $responseCookie = $this->getHttpClient()->getConfig( 'cookies' );
            if ( !empty( $responseCookie ) && !$cache->isHit() ) {
                $this->getCachePool()->save( $cache->set( $responseCookie ) );
            }

            return $response;
        } catch ( RequestException $e ) {
            $this->exceptionHandler( $e, $method, $url, $data );
            throw new ODataException( $e->getResponse()->getStatusCode(), $e );
        } catch ( GuzzleException $e ) {
            $this->getLogger()->error( "Guzzle failed to process the request {$method} {$url}", [ 'message' => $e->getMessage() ] );
            throw new TransportException( $e->getMessage(), $e );
        }
    }

    /**
     * Builds the URL with request parameters specified in the query string.
     *
     * @param string $uri Request URI relative to the service endpoint.
     * @param array|null $queryOptions
     *
     * @return string
     */
    private function buildQueryURL( string $uri, ?array $queryOptions = null ): string {
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
            if ( isset( $queryOptions['ApiVersion'] ) ) {
                $queryParameters['api-version'] = $queryOptions['ApiVersion'];
            }

            $endpointURI .= '?' . http_build_query( $queryParameters );
        }

        return $endpointURI;
    }

    /**
     * Creates a collection of request headers from the query options.
     *
     * @param array|null $queryOptions
     *
     * @return array
     */
    private function buildQueryHeaders( ?array $queryOptions = null ): array {
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
     * @param string $uri
     * @param null $queryOptions
     *
     * @return ListResponse
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function getList( string $uri, $queryOptions = null ): ListResponse {
        $url = $this->buildQueryURL( $uri, $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );

        $data = json_decode( $res->getBody() );
        $result = new ListResponse();
        $result->List = $data->value;
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
                $data = json_decode( $res->getBody() );
                $result->List = array_merge( $result->List, $data->value );
                $result->Count = count( $result->List );

                $nextLink = $data->{Annotation::ODATA_NEXTLINK} ?? null;
            }

            unset( $result->SkipToken );
        }

        return $result;
    }

    /**
     * @param string $entityCollection
     * @param string $entityId
     * @param array|null $queryOptions
     *
     * @return object
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function getRecord( string $entityCollection, string $entityId, ?array $queryOptions = null ): object {
        $url = $this->buildQueryURL( sprintf( "%s(%s)", $entityCollection, $entityId ), $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );

        return json_decode( $res->getBody() );
    }

    /**
     * @param string $uri
     * @param array|null $queryOptions
     *
     * @return object
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function getCount( string $uri, ?array $queryOptions = null ): object {
        $url = $this->buildQueryURL( sprintf( '%s/$count', $uri ), $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );

        return json_decode( $res->getBody() );
    }

    /**
     * @param string $entityCollection
     * @param mixed $data
     *
     * @return string|null
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function create( string $entityCollection, $data ): ?string {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $entityCollection );
        $res = $this->doRequest( 'POST', $url, $data );

        return static::getEntityId( $res );
    }

    /**
     * @param string $entityCollection
     * @param string $key
     * @param mixed $data
     * @param bool $upsert
     *
     * @return string|null
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function update( string $entityCollection, string $key, $data, bool $upsert = false ): ?string {
        $url = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $key );
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
     * @param string $entityCollection
     * @param string $key
     * @param string $field
     * @param $value
     * @param string|null $filename
     *
     * @return void
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function upload( string $entityCollection, string $key, string $field, $value, ?string $filename = null ): void {
        $url = sprintf( '%s%s(%s)/%s', $this->settings->getEndpointURI(), $entityCollection, $key, $field );
        $headers = [ 'Content-Type' => 'application/octet-stream' ];
        if ( $filename !== null ) {
            $headers['x-ms-file-name'] = $filename;
        }
        $this->doRequest( 'PUT', $url, $value, $headers );
    }

    /**
     * @param string $entityName
     * @param string $entityId
     * @param string $filenameField
     * @param array $headers
     * @param bool $isImage
     *
     * @return void
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function downloadFile( string $entityName, string $entityId, string $filenameField, array $headers = [], bool $isImage = false ): void {
        $url = sprintf( '%s%s(%s)/%s/$value', $this->settings->getEndpointURI(), $entityName, $entityId, $filenameField );
        if ( $isImage ) {
            $url .= '?size=full';
        }
        $response = $this->doRequest( 'GET', $url, null, $headers );

        foreach ( $response->getHeaders() as $headerKey => $headerValue ) {
            header( "{$headerKey}: {$response->getHeaderLine($headerKey)}" );
        }
        echo $response->getBody()->getContents();
    }

    /**
     * @param string $entityName
     * @param string $entityId
     * @param string $filenameField
     * @param array $headers
     *
     * @return void
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function downloadImage( string $entityName, string $entityId, string $filenameField, array $headers = [] ): void {
        $headers = array_push( $headers, [ 'Content-Type' => 'application/octet-stream' ] );
        $this->downloadFile( $entityName, $entityId, $filenameField, (array)$headers, true );
    }

    /**
     * @param string $entityCollection
     * @param string $entityId
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function delete( string $entityCollection, string $entityId ): void {
        $url = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $entityId );
        $this->doRequest( 'DELETE', $url );
    }

    /**
     * @param string $fromEntityCollection
     * @param string $fromEntityId
     * @param string $navProperty
     * @param string $toEntityCollection
     * @param string $toEntityId
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function associate(
        string $fromEntityCollection,
        string $fromEntityId,
        string $navProperty,
        string $toEntityCollection,
        string $toEntityId,
    ): void {
        $url = sprintf( '%s%s(%s)/%s/$ref', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty );
        $data = [ Annotation::ODATA_ID => sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId ) ];
        $this->doRequest( 'POST', $url, $data );
    }

    /**
     * @param string $fromEntityCollection
     * @param string $fromEntityId
     * @param string $navProperty
     * @param string $toEntityCollection
     * @param string $toEntityId
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function disassociate(
        string $fromEntityCollection,
        string $fromEntityId,
        string $navProperty,
        string $toEntityCollection,
        string $toEntityId,
    ): void {
        $url = sprintf( '%s%s(%s)/%s/$ref?$id=%s%s(%s)', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty, $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId );
        $this->doRequest( 'DELETE', $url );
    }

    /**
     * @param string $uri
     * @param array|null $queryOptions
     *
     * @return object
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function executeWebRequest( string $uri, ?array $queryOptions = null ): object {
        $url = $this->buildQueryURL( $uri, $queryOptions );
        $res = $this->doRequest( 'GET', $url, null, $this->buildQueryHeaders( $queryOptions ) );

        return json_decode( $res->getBody() );
    }

    /**
     * Executes a Web API function.
     *
     * @param string $name Function name.
     * @param mixed|null $parameters Function parameters.
     * @param string|null $entityCollection For bound functions -- entity set name.
     * @param string|null $entityId For bound functions -- entity record ID.
     *
     * @return object|null
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function executeFunction( string $name, $parameters = null, ?string $entityCollection = null, ?string $entityId = null ): ?object {
        if ( $parameters !== null ) {
            $paramNames = [];
            $paramValues = [];

            $paramNo = 1;
            foreach ( $parameters as $key => $value ) {
                $paramNames[] = sprintf( "%s=@p%s", $key, $paramNo );
                $paramValues[] = sprintf( "@p%s=%s", $paramNo, $value );
                $paramNo++;
            }

            $url = sprintf( '%s%s(%s)?%s', $this->settings->getEndpointURI(), $name, implode( ',', $paramNames ), implode( '&', $paramValues ) );
            if ( $entityCollection != null ) {
                $url = sprintf( '%s%s(%s)/%s(%s)?%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $name, implode( ',', $paramNames ), implode( '&', $paramValues ) );
            }
        } else {
            $url = sprintf( '%s%s()', $this->settings->getEndpointURI(), $name );
            if ( $entityCollection !== null ) {
                $url = sprintf( '%s%s(%s)/%s()', $this->settings->getEndpointURI(), $entityCollection, $entityId, $name );
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
     * @param string $name Action name.
     * @param mixed|null $parameters Action parameters.
     * @param string|null $entityCollection For bound actions -- entity set name.
     * @param string|null $entityId For bound actions -- entity record ID.
     *
     * @return object|null
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function executeAction( string $name, $parameters = null, ?string $entityCollection = null, ?string $entityId = null ): ?object {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $name );
        if ( $entityCollection !== null ) {
            $url = sprintf( '%s%s(%s)%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $name );
        }

        $res = $this->doRequest( 'POST', $url, $parameters );
        $result = json_decode( $res->getBody() );
        unset( $result->{Annotation::ODATA_CONTEXT} );

        return $result;
    }

    public function getSettings(): Settings {
        return $this->settings;
    }

    public function getCachePool(): CacheItemPoolInterface {
        return $this->settings->cachePool;
    }

    public function getLogger(): LoggerInterface {
        return $this->settings->logger;
    }

    /**
     * @param string $search
     * @param array|null $searchParameters
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function query( string $search, ?array $searchParameters = null ) {
        return $this->search( 'query', $search, $searchParameters );
    }

    /**
     * @param string $search
     * @param array|null $searchParameters
     *
     * @return mixed
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function suggest( string $search, ?array $searchParameters = null ) {
        return $this->search( 'suggest', $search, $searchParameters );
    }

    /**
     * @param string $search
     * @param array|null $searchParameters
     *
     * @return mixed
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    public function autocomplete( string $search, ?array $searchParameters = null ) {
        return $this->search( 'autocomplete', $search, $searchParameters );
    }

    /**
     * Performs a search request with specified parameters.
     *
     * @param string $type
     * @param string $search
     * @param array|null $searchParameters
     *
     * @return mixed
     *
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     */
    protected function search( string $type, string $search, ?array $searchParameters = null ) {
        $url = $this->settings->instanceURI . '/api/search/v1.0/' . $type;

        $data = [
            'search' => $search,
        ];

        if ( !empty( $searchParameters ) ) {
            $data = array_merge( $searchParameters, $data );
        }

        $result = $this->doRequest( 'POST', $url, $data );

        return json_decode( $result->getBody() );
    }

    /**
     * @param RequestException $e
     * @param $method
     * @param $url
     * @param null $payload
     *
     * @return void
     * @throws AuthenticationException
     * @throws TransportException
     */
    protected function exceptionHandler( RequestException $e, $method, $url, $payload = null ): void {
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
            $this->getLogger()->error( "Failed {$method} {$url}", [
                'payload' => $payload,
                'responseHeaders' => $e->getResponse()->getHeaders(),
                'responseBody' => $response,
            ] );
        } else {
            $this->getLogger()->notice( "Not Found {$method} {$url}", [
                'payload' => $payload,
                'responseHeaders' => $e->getResponse()->getHeaders(),
                'responseBody' => $response,
            ] );
        }
    }

    /**
     * Retrieves the ID of the newly created/updated entity record from response headers.
     *
     * @param ResponseInterface $response
     *
     * @return string|null
     */
    protected static function getEntityId( ResponseInterface $response ): ?string {
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

    public function getWebApiHeaderByName( $headerName ): array {
        return $this?->webApiHeaders[ $headerName ];
    }

    public function unsetWebApiHeaderByName( $headerName ): void {
        unset( $this->webApiHeaders[ $headerName ] );
    }

    public function setWebApiHeaderByName( $headerName, $headerValue ): void {
        $this->webApiHeaders[ $headerName ] = $headerValue;
    }

    public function getWebApiHeaders(): array {
        return $this->webApiHeaders;
    }

}
