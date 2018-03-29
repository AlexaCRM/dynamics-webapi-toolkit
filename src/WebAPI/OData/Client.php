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
use GuzzleHttp\HandlerStack;

/**
 * Dynamics 365 Web API client.
 */
class Client {

    const API_VERSION = '8.2';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
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

    public function getMetadata() {
        if ( $this->metadata instanceof Metadata ) {
            return $this->metadata;
        }

        $resp = $this->httpClient->get( $this->settings->getEndpointURI() . '$metadata', [
            'headers' => [ 'Accept' => 'application/xml' ],
        ] );
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
     */
    private function GetHttpRequest( $method, $url, $data = null, array $headers = [] ) {
        if ( $headers == null ) {
            $headers = [];
        }

        if ( in_array( $method, [ 'POST', 'PATCH' ] ) ) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->httpClient->request( $method, $url, [
            'headers' => $headers,
            'json' => $data,
        ] );
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
        if ( $queryOptions != null ) {
            if ( isset( $queryOptions['FormattedValues'] ) ) {
                $headers['odata.include-annotations'] = 'OData.Community.Display.V1.FormattedValue';
            }
            if ( isset( $queryOptions['MaxPageSize'] ) ) {
                $headers['Prefer'] = 'odata.maxpagesize=' . $queryOptions['MaxPageSize'];
            }
        }

        return $headers;
    }

    public function GetList( $uri, $queryOptions = null ) {
        $url = $this->BuildQueryURL( $uri, $queryOptions );
        $res = $this->GetHttpRequest( 'GET', $url, null, $this->BuildQueryHeaders( $queryOptions ) );

        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $data          = json_decode( $res->getBody() );
            $result        = new \stdClass();
            $result->List  = $data->value;
            $result->Count = count( $data->value );

            $nl       = '@odata.nextLink';
            $nextLink = isset( $data->$nl )? $data->$nl : null;
            while ( $nextLink != null ) {
                $res = $this->GetHttpRequest( 'GET', $nextLink, null, $this->BuildQueryHeaders( $queryOptions ) );

                $nextLink = null;
                if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
                    $data         = json_decode( $res->getBody() );
                    $result->List = array_merge( $result->List, $data->value );
                    $nextLink     = $data->$nl;

                    $result->Count = count( $result->List );
                }
            }

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function Get( $entityCollection, $entityId, $queryOptions = null ) {
        $url = $this->BuildQueryURL( sprintf( "%s(%s)", $entityCollection, $entityId ), $queryOptions );
        $res = $this->GetHttpRequest( 'GET', $url, null, $this->BuildQueryHeaders( $queryOptions ) );

        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = json_decode( $res->getBody() );

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function GetCount( $uri, $queryOptions = null ) {
        $url = $this->BuildQueryURL( sprintf( '%s/$count', $uri ), $queryOptions );
        $res = $this->GetHttpRequest( 'GET', $url, null, $this->BuildQueryHeaders( $queryOptions ) );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = json_decode( $res->getBody() );

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function Create( $entityCollection, $data ) {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $entityCollection );
        $res = $this->GetHttpRequest( 'POST', $url, $data );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $id     = $res->getHeader( 'OData-EntityId' )[0];
            $id     = explode( '(', $id )[1];
            $id     = str_replace( ')', '', $id );
            $result = $id;

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function Update( $entityCollection, $key, $data, $upsert = false ) {
        $url     = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $key );
        $headers = [];
        if ( $upsert ) {
            $headers['If-Match'] = '*';
        }
        $res = $this->GetHttpRequest( 'PATCH', $url, $data, $headers );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $id               = $res->getHeader( 'OData-EntityId' )[0];
            $id               = explode( '(', $id )[1];
            $id               = str_replace( ')', '', $id );
            $result           = new \stdClass();
            $result->EntityId = $id;

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function Delete( $entityCollection, $entityId ) {
        $url = sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $entityCollection, $entityId );
        $res = $this->GetHttpRequest( 'DELETE', $url );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = true;

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function Associate( $fromEntityCollection, $fromEntityId, $navProperty, $toEntityCollection, $toEntityId ) {
        $url  = sprintf( '%s%s(%s)/%s/$ref', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty );
        $data = [ '@odata.id' => sprintf( '%s%s(%s)', $this->settings->getEndpointURI(), $toEntityCollection, $toEntityId ) ];
        $res  = $this->GetHttpRequest( 'POST', $url, $data );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = true;

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function DeleteAssociation( $fromEntityCollection, $fromEntityId, $navProperty, $toEntityCollection, $toEntityId ) {
        $url = sprintf( '%s%s(%s)/%s/$ref?$id=%s%s(%s)', $this->settings->getEndpointURI(), $fromEntityCollection, $fromEntityId, $navProperty, $this->config['APIUrl'], $toEntityCollection, $toEntityId );
        $res = $this->GetHttpRequest( 'DELETE', $url );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = true;

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function ExecuteFunction( $functionName, $parameters = null, $entityCollection = null, $entityId = null ) {
        $paramvars   = [];
        $paramvalues = [];
        $paramcount  = 1;

        if ( $parameters != null ) {
            foreach ( $parameters as $key => $value ) {
                $paramvars[] = sprintf( "%s=@p%s", $key, $paramcount );
                if ( is_string( $value ) ) {
                    $paramvalues[] = sprintf( "@p%s='%s'", $paramcount, $value );
                } else {
                    $paramvalues[] = sprintf( "@p%s=%s", $paramcount, $value );
                }
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
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = json_decode( $res->getBody() );
            unset( $result->{'@odata.context'} );

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    public function ExecuteAction( $actionName, $data = null, $entityCollection = null, $entityId = null ) {
        $url = sprintf( '%s%s', $this->settings->getEndpointURI(), $actionName );
        if ( $entityCollection != null ) {
            $url = sprintf( '%s%s(%s)%s', $this->settings->getEndpointURI(), $entityCollection, $entityId, $actionName );
        }

        $res = $this->GetHttpRequest( 'POST', $url, $data );
        if ( ( $res->getStatusCode() >= 200 ) && ( $res->getStatusCode() < 300 ) ) {
            $result = json_decode( $res->getBody() );
            unset( $result->{'@odata.context'} );

            return $result;
        } else {
            throw new \Exception( $res->getBody(), $res->getStatusCode() );
        }
    }

    /**
     * @return Settings
     */
    public function getSettings() {
        return $this->settings;
    }

}
