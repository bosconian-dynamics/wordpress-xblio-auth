<?php
namespace BosconianDynamics\XblioAuth;

use BosconianDynamics\XblioAuth\IAuthStrategy;
use \DI\ContainerBuilder;

use function BosconianDynamics\XblioAuth\rrmdir;

/**
 * Main plugin interface.
 */
class Plugin {
  const CONTAINER_CACHE_DIR   = 'build/php-di';
  const REDUX_OPTION_KEY      = 'bd_xblio_auth';
  const USER_META_PROFILE_KEY = 'xblio_auth_profile';

  /**
   * Static plugin singleton instance.
   *
   * @var Plugin|null
   */
  protected static $instance = null;

  /**
   * PHP-DI dependency injection container.
   *
   * @var \DI\Container
   */
  protected $container;

  /**
   * Request router.
   *
   * @var \BosconianDynamics\XblioAuth\Router
   */
  protected $router = null;

  /**
   * Authentication controller.
   *
   * @var \BosconianDynamics\XblioAuth\AuthController|null
   */
  protected $auth = null;

  /**
   * Development mode, currently set at run-time based on WP_DEBUG. Stops
   * dependency container compilation.
   *
   * @var boolean
   */
  protected $dev_mode;

  /**
   * Protected constructor - called by static::get_instance();
   */
  protected function __construct() {
    $this->dev_mode = defined( 'WP_DEBUG' ) && WP_DEBUG;

    $container_cache_dir = __DIR__ . '/' . static::CONTAINER_CACHE_DIR;

    $builder = new ContainerBuilder();
    $builder->addDefinitions( __DIR__ . '/../config/dependencies.php' );

    if( ! $this->dev_mode ) {
      $builder->enableCompilation( $container_cache_dir );
      $builder->writeProxiesToFile( true, $container_cache_dir . '/proxies' );
    }
    elseif( file_exists( $container_cache_dir ) ) {
      rrmdir( $container_cache_dir );
    }

    $this->container = $builder->build();
    $this->router    = $this->container->get( 'auth.router' );

    $this->container
      ->get( 'route.auth_provider_action' )
      ->add_handler( [ $this, 'route_authentication' ] );
  }

  /**
   * Retrieve an option from the Redux option store
   *
   * @param string $key Option key.
   * @param mixed  $default Default value to return if the option is not found.
   * @return any
   */
  public static function get_option( string $key, $default = null ) {
    return \Redux::get_option( static::REDUX_OPTION_KEY, $key, $default );
  }

  /**
   * Retrieve the plugin instance singleton.
   *
   * @return Plugin
   */
  public static function get_instance() : Plugin {
    if( ! static::$instance )
      static::$instance = new static();

    return static::$instance;
  }

  /**
   * User meta helper to retrieve data from a user's XBL profile.
   *
   * @param string   $key Profile data key.
   * @param int|null $user_id WP user ID to query data for. If not set, defaults to the current user.
   * @return any
   *
   * @throws \WP_Error Throws error if no user specified and no user logged in.
   */
  public static function get_profile_field( string $key, $user_id = null ) {
    if( ! $user_id )
      $user_id = \get_current_user_id();

    if( ! $user_id )
      throw new \WP_Error( 'No user specified' );

    $profile = \get_user_meta( $user_id, static::USER_META_PROFILE_KEY, true );

    if( ! $profile )
      return false;

    return $profile[ $key ];
  }

  /**
   * Retrieve an XBL avatar data from user meta if the user has opted in to
   * using it, or the plugin is configured to use them for all users.
   *
   * Filters 'pre_get_avatar_data'
   *
   * @param array      $args Avatar arguments as specified by WordPress.
   * @param int|string $id_or_email A WP User ID/object or email address.
   * @return array
   */
  public function get_xbox_avatar_data( array $args, $id_or_email ) : array {
    if( ! is_numeric( $id_or_email ) )
      return $args;

    if(
      ! (bool) static::get_option( 'force_xbl_avatar' )
      && ! (bool) \get_user_meta( $id_or_email, 'xblio_auth_use_avatar', true )
    ) {
      return $args;
    }

    $args['url'] = static::get_profile_field( 'avatar', $id_or_email );
    // TODO: set other avatar data args.

    return $args;
  }

  /**
   * Register query tags not managed by the router
   */
  public function register_rewrite_tags() : void {
    \add_rewrite_tag( '%code%', '([^&])+' );
  }

  /**
   * Update a user's XBL profile user meta.
   *
   * @param \WP_User      $user The user to update.
   * @param array         $profile Profile data.
   * @param IAuthStrategy $strategy The strategy to update profile data for.
   * @return void
   */
  public function update_xblio_usermeta( \WP_User $user, array $profile, IAuthStrategy $strategy ) : void {
    if( $strategy->get_id() !== 'xblio' )
      return;

    $user_id = $user->ID;

    // TODO: Gravatar default image can trip this - need a different way to determine if the user has an avatar set.
    if( ! \get_avatar_url( $user_id ) ) {
      // Set meta to trigger get_avatar_data filter replacing gravatar.
      \update_user_meta( $user_id, 'xblio_auth_use_avatar', true );
    }

    \update_user_meta( $user_id, 'xblio_auth_app_key', $profile['app_key'] );
    \update_user_meta(
      $user_id,
      static::USER_META_PROFILE_KEY,
      [
        'avatar'       => $profile['avatar'],
        'gamertag'     => $profile['gamertag'],
        'xuid'         => $profile['xuid'],
        'level'        => $profile['level'],
        'gamerscore'   => $profile['gamerscore'],
        'last_updated' => time(),
      ]
    );
  }

  /**
   * Redirects users to the path specified in plugin options after successful
   * authentications.
   *
   * @return void
   */
  public function authentication_redirect() {
    \wp_redirect( static::get_option( 'auth_success_redirect', '/' ) );
    exit;
  }

  /**
   * Route authentication requests to the authentication controller.
   *
   * @param string    $provider Provider ID, extracted from query args.
   * @param string    $action Action name, extracted from query args.
   * @param \WP_Query $query The query which triggered the route.
   * @return void
   */
  public function route_authentication( string $provider, string $action, \WP_Query $query ) {
    $strategy_name = 'auth.strategy.' . $provider;

    // If there isn't a strategy for the specified provider, bail.
    if( ! $this->container->has( $strategy_name ) )
      return;

    $controller = $this->container->get( 'auth.controller' );
    $controller->use( $this->container->get( $strategy_name ) ); // Load up the appropriate provider strategy.

    if( $action === 'grant' || $action === 'callback' ) {
      $this->container->call(
        [
          $controller,
          'authenticate',
        ],
        [
          'strategy_id' => $provider,
          'query'       => $query,
        ]
      );
    }
  }
}
