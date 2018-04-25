<?php

/**
 * Copyright (c) 2016 David Yack
 * Copyright (c) 2017 AlexaCRM
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
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;

/**
 * Dynamics 365 Web API OData client.
 */
class Client {

    /**
     * Web API version.
     */
    const API_VERSION = '8.2';

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
     * Client constructor.
     *
     * @param Settings $settings
     * @param AuthMiddlewareInterface $authMiddleware
     */
    function __construct( Settings $settings, AuthMiddlewareInterface $authMiddleware ) {
        $this->settings = $settings;

        $headers = [
            'Accept' => 'application/json',
            'OData-MaxVersion' => '4.0',
            'OData-Version' => '4.0',
        ];

        $handlerStack = HandlerStack::create();
        $handlerStack->push( $authMiddleware->getMiddleware() );

        $this->httpClient = new HttpClient( [
            'headers' => $headers,
            'verify' => false,
            'handler' => $handlerStack,
        ] );
    }

    /**
     * Retrieves OData Service Metadata.
     *
     * @return Metadata
     * @throws AuthenticationException
     * @throws ODataException
     */
    public function getMetadata() {
        if ( $this->metadata instanceof Metadata ) {
            return $this->metadata;
        }

        try {
            $resp = $this->httpClient->get( $this->settings->getEndpointURI() . '$metadata', [
                'headers' => [ 'Accept' => 'application/xml' ],
            ] );
        } catch ( RequestException $e ) {
            if ( $e->getResponse()->getStatusCode() === 401 ) {
                throw new AuthenticationException();
            }

            $response = json_decode( $e->getResponse()->getBody()->getContents() );
            throw new ODataException( $response->error, $e );
        }

        $metadataXML = $resp->getBody()->getContents();

        $this->metadata = Metadata::createFromXML( $metadataXML );

        return $this->metadata;
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @param array $headers
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws ODataException
     * @throws AuthenticationException
     */
    private function GetHttpRequest( $method, $url, $data = null, array $headers = [] ) {
        if ( $headers == null ) {
            $headers = [];
        }

        if ( in_array( $method, [ 'POST', 'PATCH' ] ) ) {
            $headers['Content-Type'] = 'application/json';
        }

        try {
            return $this->httpClient->request( $method, $url, [
                'headers' => $headers,
                'json' => $data,
            ] );
        } catch ( RequestException $e ) {
            if ( $e->getResponse()->getStatusCode() === 401 ) {
                throw new AuthenticationException();
            }

            $response = json_decode( $e->getResponse()->getBody()->getContents() );
            throw new ODataException( $response->error, $e );
        } catch ( GuzzleException $e ) {
            throw new ODataException( (object)[ 'message' => $e->getMessage() ], $e );
        }
    }

    /**
     * @param $uri
     * @param null $queryOptions
     *
     * @return string
     */
    private function BuildQueryURL( $uri, $queryOptions = null ) {
        $fullurl = $this->settings->getEndpointURI() . $uri;
        $qs      = [];
        if ( $queryOptions != null ) {
            if ( isset( $queryOptions['Select'] ) ) {
                $qs['$select'] = implode( ',', $queryOptions['Select'] );
            }
            if ( isset( $queryOptions['OrderBy'] ) ) {
                $qs['$orderby'] = implode( ',', $queryOptions['OrderBy'] );
            }
            if ( isset( $queryOptions['Filter'] ) ) {
                $qs['$filter'] = $queryOptions['Filter'];
            }
            if ( isset( $queryOptions['IncludeCount'] ) ) {
                $qs['$count'] = 'true';
            }
            if ( isset( $queryOptions['Skip'] ) ) {
                $qs['$skip'] = $queryOptions['Skip'];
            }
            if ( isset( $queryOptions['Top'] ) ) {
                $qs['$top'] = $queryOptions['Top'];
            }
            if ( isset( $queryOptions['SystemQuery'] ) ) {
                $qs['savedQuery'] = $queryOptions['SystemQuery'];
            }
            if ( isset( $queryOptions['UserQuery'] ) ) {
                $qs['userQuery'] = $queryOptions['UserQuery'];
            }
            if ( isset( $queryOptions['FetchXml'] ) ) {
                $qs['fetchXml'] = $queryOptions['FetchXml'];
            }
            $fullurl .= '?' . http_build_query( $qs );
        }

        return $fullurl;
    }

    private function BuildQueryHeaders( $queryOptions = null ) {
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
     * @return \stdClass
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function GetList( $uri, $queryOptions = null ) {
        $url = $this->BuildQueryURL( $uri, $queryOptions );
        $res = $this->GetHttpRequest( 'GET', $url, null, $this->BuildQueryHeaders( $queryOptions ) );

        $data          = json_decode( $res->getBody() );
        $result        = new \stdClass();
        $result->List  = $data->value;
        $result->Count = count( $data->value );

        $nl       = '@odata.nextLink';
        $nextLink = isset( $data->$nl )? $data->$nl : null;
        while ( $nextLink != null ) {
            $res = $this->GetHttpRequest( 'GET', $nextLink, null, $this->BuildQueryHeaders( $queryOptions ) );

            $nextLink = null;
            $data         = json_decode( $res->getBody() );
            $result->List = array_merge( $result->List, $data->value );
            $nextLink     = $data->$nl;

            $result->Count = count( $result->List );
        }

        return $result;
    }

    /**
     * @param $entityCollection
     * @param $entityId
     * @param null $queryOptions
     *
     * @return mixed
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function Get( $entityCollection, $entityId, $queryOptions = null ) {
        $url = $this->BuildQueryURL( sprintf( "%s(%s)", $entityCollection, $entityId ), $queryOptions );
        $res = $this->GetHttpRequest( 'GET', $url, null, $this->BuildQueryHeaders( $queryOptions ) );
        $result = json_decode( $res->getBody() );

        return $result;
    }

    /**
     * @param $uri
     * @param null $queryOptions
     *
     * @return mixed
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function GetCount( $uri, $queryOptions = null ) {
        $url = $this->BuildQueryURL( sprintf( '%s/$count', $uri ), $queryOptions );
        $res = $this->GetHttpRequest( 'GET', $url, null, $this->BuildQueryHeaders( $queryOptions ) );
        $result = json_decode( $res->getBody() );

        return $result;
    }

    /**
     * @param $entityCollection
     * @param $data
     *
     * @return mixed
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function Create( $entityCollection, $data ) {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $entityCollection );
        $res = $this->GetHttpRequest( 'POST', $url, $data );
        $id = $res->getHeader( 'OData-EntityId' )[0];
        $id = explode( '(', $id )[1];
        $id = str_replace( ')', '', $id );

        return $id;
    }

    /**
     * @param $entityCollection
     * @param $key
     * @param $data
     * @param bool $upsert
     *
     * @return \stdClass
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function Update( $entityCollection, $key, $data, $upsert = false ) {
        $url     = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $key );
        $headers = [];
        if ( $upsert ) {
            $headers['If-Match'] = '*';
        }
        $res = $this->GetHttpRequest( 'PATCH', $url, $data, $headers );
        $id = $res->getHeader( 'OData-EntityId' )[0];
        $id = explode( '(', $id )[1];
        $id = str_replace( ')', '', $id );
        $result = new \stdClass();
        $result->EntityId = $id;

        return $result;
    }

    /**
     * @param $entityCollection
     * @param $entityId
     *
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function Delete( $entityCollection, $entityId ) {
        $url = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $entityId );
        $this->GetHttpRequest( 'DELETE', $url );
    }

    /**
     * @param $fromEntityCollection
     * @param $fromEntityId
     * @param $navProperty
     * @param $toEntityCollection
     * @param $toEntityId
     *
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function Associate( $fromEntityCollection, $fromEntityId, $navProperty, $toEntityCollection, $toEntityId ) {
        $url  = sprintf( '%s%s(%s)/%s/$ref', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty );
        $data = [ '@odata.id' => sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId ) ];
        $this->GetHttpRequest( 'POST', $url, $data );
    }

    /**
     * @param $fromEntityCollection
     * @param $fromEntityId
     * @param $navProperty
     * @param $toEntityCollection
     * @param $toEntityId
     *
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function DeleteAssociation( $fromEntityCollection, $fromEntityId, $navProperty, $toEntityCollection, $toEntityId ) {
        $url = sprintf( '%s%s(%s)/%s/$ref?$id=%s%s(%s)', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty, $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId );
        $this->GetHttpRequest( 'DELETE', $url );
    }

    /**
     * @param $functionName
     * @param null $parameters
     * @param null $entityCollection
     * @param null $entityId
     *
     * @return mixed
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function ExecuteFunction( $functionName, $parameters = null, $entityCollection = null, $entityId = null ) {
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
                $url = sprintf( '%s%s(%s%s(%s))?%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $functionName, implode( ',', $paramvars ), implode( '&', $paramvalues ) );
            }
        } else {
            $url = sprintf( '%s%s()', $this->settings->getEndpointURI(), $functionName );
            if ( $entityCollection != null ) {
                $url = sprintf( '%s%s(%s%s())', $this->settings->getEndpointURI(), $entityCollection, $entityId, $functionName );
            }
        }

        $res = $this->GetHttpRequest( 'GET', $url );
        $result = json_decode( $res->getBody() );
        unset( $result->{'@odata.context'} );

        return $result;
    }

    /**
     * @param $actionName
     * @param null $data
     * @param null $entityCollection
     * @param null $entityId
     *
     * @return mixed
     * @throws ODataException
     * @throws AuthenticationException
     */
    public function ExecuteAction( $actionName, $data = null, $entityCollection = null, $entityId = null ) {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $actionName );
        if ( $entityCollection != null ) {
            $url = sprintf( '%s%s(%s)%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $actionName );
        }

        $res = $this->GetHttpRequest( 'POST', $url, $data );
        $result = json_decode( $res->getBody() );
        unset( $result->{'@odata.context'} );

        return $result;
    }

    /**
     * @return Settings
     */
    public function getSettings() {
        return $this->settings;
    }

}
