<?php
namespace BosconianDynamics\XblioAuth\Xblio;

use BosconianDynamics\XblioAuth\Xblio\User;

class APIClient {
  const OPTION_KEY_RATELIMIT    = 'xblio-auth-ratelimit-remaining';

  private $public_key;

  protected $rate_available = null;
  protected $app_key     = null;
  protected $api_url;
  protected $token_url;
  protected $auth_url;
  protected $user;

  public function __construct( string $public_key, array $urls ) {
    $this->public_key = $public_key;
    $this->api_url    = $urls['api'];
    $this->token_url  = $urls['token'];
    $this->auth_url   = $urls['auth'];
  }

  public function authenticate() : void {
    \wp_redirect( static::$auth_url . '/' . self::$public_key );
    exit;
  }

  public function get_user() {
    if( $this->user instanceof User )
      return $this->user;
    
    if( \is_user_logged_in() ) {
      $wp_user_id = \get_current_user_id();

      $xuid    = \get_user_meta( $wp_user_id, 'xblio-auth-xuid', true );
      $app_key = \get_user_meta( $wp_user_id, 'xblio-auth-app_key', true );
      
      if( isset($xuid) && isset($app_key) ) {
        $this->user = new User( $xuid, $app_key, [] );
      }
    }
    else {

    }
  }

  public function request_user_auth_token( string $code ) {
    $response = $this->request(
      $this->token_url,
      'POST',
      [
        'app_key' => $this->public_key,
        'code'    => $code
      ],
      [
        'headers' => [
          'X-Contract'   => '2',
          'Content-Type' => 'application/json'
        ]
      ]
    );

    if( \is_wp_error( $response ) )
      return $response;
    
    return $response;

    // TODO: this. better.
  }

  public function request( string $endpoint, string $method, array $data, array $options ) {
    $url = strpos($endpoint, 'http') === false
      ? static::$api_url . '/' . $endpoint
      : $endpoint;
    
    $options = wp_parse_args(
      $options,
      [
        'url'      => $url,
        'timeout'  => 25,   //TODO: tune this
        'blocking' => true,
        'method'   => $method,
        'headers'  => [
          'X-Authorization' => isset( $options['app_key'] ) ? $options['app_key'] : $this->public_key,
          'X-Contract'      => '100',
          'Content-Type'    => 'application/json'
        ]
      ]
    );

    // TODO: look a little more closely at WP_Http::request - it might handle some of this on it's own
    if( $data ) {
      if( $options['method'] === 'GET' ) {
        // TODO: is add_query_arg recursive? Does it need to be?
        $options['url'] = \add_query_arg( $data, $options['url'] );
      }
      elseif( $options['headers']['Content-Type'] === 'application/json' ) {
        $options['body'] = json_encode( $data );
      }
      else {
        $options['body'] = $data;
      }
    }

    $response = \wp_remote_request( $options['url'], $options );
    
    if( \is_wp_error( $response ) ) {
        // TODO: handle transmission errors
        return $response;
    }

    return $this->parse_response( $response );
  }

  protected function set_rate_remaining( int $remaining ) {
    \set_transient( static::OPTION_KEY_RATELIMIT, $remaining, HOUR_IN_SECONDS );
    $this->rate_available = $remaining;
  }

  protected function get_rate_remaining() {
    if( $this->rate_available === null ) {
      $remaining = \get_transient( static::OPTION_KEY_RATELIMIT );
  
      $this->rate_available === false ? 500 : $remaining;
    }

    return $this->rate_available;
  }

  protected function parse_response( array $response ) {
    $headers  = $response['headers']->getAll();
    $body     = $response['body'];
    /*$response = $response['response'];

    if( $response['code'] !== '200' ) {
      // TODO: handle API errors
      //\wp_die( 'OpenXBL request returned with status code ' . $response['code'] . ': ' . $response['message'] );
    }*/

    if( isset( $headers['x-ratelimit-remaining'] ) ) {
      $this->set_rate_remaining( (int) $headers['x-ratelimit-remaining'] );
    }

    if( isset( $headers['content-type'] ) ) {
      if( strpos( $headers['content-type'], 'application/json' ) !== false ) {
        $body = json_decode( $body );
      }
    }

    return [
      'status'  => [
        'code'    => (int) $response['response']['code'],
        'message' => $response['response']['message']
      ],
      'headers' => $headers,
      'data'    => $body
    ];
  }
}