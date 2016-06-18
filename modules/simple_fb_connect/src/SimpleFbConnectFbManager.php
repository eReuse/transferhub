<?php

/**
 * @file
 * Contains \Drupal\simple_fb_connect\SimpleFbConnectFbManager.
 */

namespace Drupal\simple_fb_connect;

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\GraphObject;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Contains all Simple FB Connect logic that is related to Facebook interaction.
 */
class SimpleFbConnectFbManager {

  protected $configFactory;
  protected $loggerFactory;
  protected $session;
  protected $eventDispatcher;
  protected $moduleConfig;
  protected $pictureConfig;
  protected $appId;
  protected $appSecret;
  protected $scope;
  protected $returnUrl;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, SessionInterface $session, EventDispatcherInterface $event_dispatcher) {
    // Injected dependencies.
    $this->configFactory    = $config_factory;
    $this->loggerFactory    = $logger_factory->get('simple_fb_connect');
    $this->session          = $session;
    $this->eventDispatcher  = $event_dispatcher;

    // Config members for easier access.
    $this->moduleConfig     = $this->configFactory->get('simple_fb_connect.settings');
    $this->pictureConfig    = $this->configFactory->get('field.field.user.user.user_picture');

    // Other member variables.
    $this->appId            = $this->moduleConfig->get('app_id');
    $this->appSecret        = $this->moduleConfig->get('app_secret');
    $this->scope            = array('email');
    $this->returnUrl        = Url::fromRoute('simple_fb_connect.return_from_fb', array(), array('absolute' => TRUE))->toString();

    // Initialize Facebook App to Facebook SDK library.
    $this->initializeApp();
  }

  /**
   * Initalizes Facebook App to Facebook SDK library.
   *
   * This function checks that AppId and AppSecret have been defined in
   * SimpleFbConnect module settings. It then initializes the SDK
   * by calling FacebookSession::setDefaultApplication.
   *
   * @return bool
   *   True if AppId and AppSecret are defined.
   *   False otherwise.
   */
  public function initializeApp() {
    // Check that AppId and AppSecret have been set in SimpleFbConnect settings.
    if ($this->validateConfig()) {
      FacebookSession::setDefaultApplication($this->appId, $this->appSecret);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Checks that module is configured.
   *
   * @return bool
   *   True if module is configured
   *   False otherwise
   */
  public function validateConfig() {
    // Check that App ID and App Secret have been defined.
    if (!$this->appId || !$this->appSecret) {
      $this->loggerFactory->error('Define App ID and App Secret on module settings.');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Returns the Facebook login URL where user will be redirected.
   *
   * @return string
   *   Absolute Facebook login URL where user will be redirected
   */
  public function getFbLoginUrl() {
    $login_helper = new FacebookRedirectLoginHelper($this->returnUrl);
    $login_helper->disableSessionStatusCheck();

    // Dispatch an event so that other modules can modify the permission scope.
    // Set the scope twice on the event: as the main subject but also in the
    // list of arguments.
    $e = new GenericEvent($this->scope, ['scope' => $this->scope]);
    $event = $this->eventDispatcher->dispatch('simple_fb_connect.scope', $e);
    $final_scope = $event->getArgument('scope');

    return $login_helper->getLoginUrl($final_scope);
  }

  /**
   * Returns the URL where FB will return the user after FB authentication.
   *
   * @return string
   *   Absolute URL where Facebook will return the user
   */
  public function getReturnUrl() {
    return $this->returnUrl;
  }

  /**
   * Starts FacebookSession after FB has returned the user back to our site.
   *
   * Saves the user access token to Drupal session. This function must only be
   * called from route simple_fb_connect.return_from_fb because login helper
   * will use the URL parameters set by Facebook to start Facebook session.
   *
   * @param \Facebook\FacebookRedirectLoginHelper $login_helper
   *   FacebookRedirectLoginHelper object.
   *
   * @return bool
   *   True if we have a valid FB Session
   *   False otherwise
   */
  public function startFbSession(FacebookRedirectLoginHelper $login_helper) {
    try {
      $fb_session = $login_helper->getSessionFromRedirect();

      if ($fb_session) {
        $this->session->set('simple_fb_connect_user_token', $fb_session->getToken());
        return TRUE;
      }
      else {
        // If the user cancels the dialog or return URL is invalid,
        // Facebook will not throw an exception but will return NULL.
        $this->loggerFactory->error('Could not start Facebook session. User cancelled the dialog in Facebook or return URL was not valid.');
        return FALSE;
      }
    }
    catch (FacebookRequestException $ex) {
      $this->loggerFactory->error('Could not start Facebook session. FacebookRequestException: @message', array('@message' => json_encode($ex->getResponse())));
    }
    catch (\Exception $ex) {
      $this->loggerFactory->error('Could not start Facebook session. Exception: @message', array('@message' => ($ex->getMessage())));
    }

    return FALSE;
  }

  /**
   * Returns FacebookSession for the current user.
   *
   * Other modules can use this function to get FacebookSession object in
   * order to make their own requests to Facebook API.
   *
   * This function validates the session by checking that user's access token
   * (stored in Drupal session) is still valid and from our FB app.
   *
   * @return \Facebook\FacebookSession|false
   *   FacebookSession if the token was valid
   *   False otherwise
   */
  public function getFbSession() {
    try {
      // Get user access token from Drupal session.
      $user_token = $this->session->get('simple_fb_connect_user_token');
      $fb_session = new FacebookSession($user_token);

      // Validate the session (token is not expired and token is from our app).
      $fb_session->validate();
      return $fb_session;
    }
    catch (\Exception $ex) {
      $this->loggerFactory->error('FacebookSession validation failed. Exception: @message', array('@message' => ($ex->getMessage())));
    }
    return FALSE;
  }

  /**
   * Makes an API call to get user's Facebook profile.
   *
   * @param \Facebook\FacebookSession $fb_session
   *   FacebookSession object.
   *
   * @return \Facebook\GraphObject|false
   *   GraphObject representing the user
   *   False if exception was thrown
   */
  public function getFbProfile(FacebookSession $fb_session) {
    try {
      $request = new FacebookRequest($fb_session, 'GET', '/me?fields=id,name,email');
      $graph_object = $request->execute()->getGraphObject();
      return $graph_object;
    }
    catch (FacebookRequestException $ex) {
      $this->loggerFactory->error('Could not load Facebook user profile: FacebookRequestException. Error details: @message', array('@message' => json_encode($ex->getResponse())));
    }
    catch (\Exception $ex) {
      $this->loggerFactory->error('Could not load Facebook user profile: Unhandled exception. Error details: @message', array('@message' => ($ex->getMessage())));
    }

    // Something went wrong.
    drupal_set_message(t('Error loading Facebook profile. Contact site administrator.'), 'error');
    return FALSE;
  }

  /**
   * Makes an API call to get the URL of user's Facebook profile picture.
   *
   * @param \Facebook\FacebookSession $fb_session
   *   FacebookSession object.
   *
   * @return string|false
   *   Absolute URL of the profile picture
   *   False on errors
   */
  public function getFbProfilePicUrl(FacebookSession $fb_session) {
    // Determine preferred resolution for the profile picture.
    $resolution = $this->getPreferredResolution();

    // Generate FB API query.
    $query = '/me/picture?redirect=false';
    if (is_array($resolution)) {
      $query .= '&width=' . $resolution['width'] . '&height=' . $resolution['height'];
    }

    // Call Graph API to request profile picture.
    try {
      $request = new FacebookRequest($fb_session, 'GET', $query);
      $url = $request->execute()->getGraphObject()->getProperty('url');
      return $url;
    }
    catch (FacebookRequestException $ex) {
      $this->loggerFactory->error('Could not load Facebook profile picture URL. FacebookRequestException: @message', array('@message' => json_encode($ex->getResponse())));
    }
    catch (\Exception $ex) {
      $this->loggerFactory->error('Could not load Facebook profile picture URL. Unhandled exception. Error details: @message', array('@message' => ($ex->getMessage())));
    }

    // Something went wrong and the picture could not be loaded.
    return FALSE;
  }

  /**
   * Returns user's email address from Facebook profile.
   *
   * @param \Facebook\GraphObject $fb_profile
   *   User's Facebook profile.
   *
   * @return string|false
   *   User's email address if found
   *   False otherwise
   */
  public function getEmail(GraphObject $fb_profile) {
    if ($email = $fb_profile->getProperty('email')) {
      return $email;
    }

    // Email address was not found. Log error and return FALSE.
    $this->loggerFactory->error('No email address in Facebook user profile');
    return FALSE;
  }

  /**
   * Determines preferred profile pic resolution from account settings.
   *
   * Return order: max resolution, min resolution, FALSE.
   *
   * @return array|false
   *   Array of resolution, if defined in Drupal account settings
   *   False otherwise
   */
  protected function getPreferredResolution() {
    $max_resolution = $this->pictureConfig->get('settings.max_resolution');
    $min_resolution = $this->pictureConfig->get('settings.min_resolution');

    // Return order: max resolution, min resolution, FALSE.
    if ($max_resolution) {
      $resolution = $max_resolution;
    }
    elseif ($min_resolution) {
      $resolution = $min_resolution;
    }
    else {
      return FALSE;
    }
    $dimensions = explode('x', $resolution);
    return array('width' => $dimensions[0], 'height' => $dimensions[1]);
  }

}
