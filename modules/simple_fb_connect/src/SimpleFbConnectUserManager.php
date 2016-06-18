<?php

/**
 * @file
 * Contains \Drupal\simple_fb_connect\SimpleFbConnectUserManager.
 */

namespace Drupal\simple_fb_connect;

use Drupal\user\Entity\User;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Component\Render\PlainTextOutput;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Contains all logic that is related to Drupal user management.
 */
class SimpleFbConnectUserManager {

  protected $configFactory;
  protected $loggerFactory;
  protected $session;
  protected $eventDispatcher;
  protected $requestContext;
  protected $pathValidator;
  protected $pictureConfig;
  protected $moduleConfig;
  protected $userConfig;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, SessionInterface $session, EventDispatcherInterface $event_dispatcher, RequestContext $request_context, PathValidatorInterface $path_validator) {
    // Injected dependencies.
    $this->configFactory    = $config_factory;
    $this->loggerFactory    = $logger_factory->get('simple_fb_connect');
    $this->session          = $session;
    $this->eventDispatcher  = $event_dispatcher;
    $this->requestContext   = $request_context;
    $this->pathValidator    = $path_validator;

    // Config members for easier access.
    $this->pictureConfig    = $this->configFactory->get('field.field.user.user.user_picture');
    $this->moduleConfig     = $this->configFactory->get('simple_fb_connect.settings');
    $this->userConfig       = $this->configFactory->get('user.settings');
  }

  /**
   * Returns Drupal user account by email.
   *
   * @param string $email
   *   email address returned by Facebook.
   *
   * @return \Drupal\user\Entity\User|false
   *   Drupal user account if found
   *   False otherwise
   */
  public function getUserByEmail($email) {
    return user_load_by_mail($email);
  }

  /**
   * Create a new user account.
   *
   * @param string $name
   *   User's name on Facebook.
   * @param string $email
   *   User's email address.
   * @param string $fbid
   *   User's Facebook ID.
   * @param string $picture_url
   *   User's Facebook profile picture URL.
   *
   * @return \Drupal\user\Entity\User|false
   *   Drupal user account if user was created
   *   False otherwise
   */
  public function createUser($name, $email, $fbid, $picture_url) {
    // Make sure we have everyting we need.
    if (!$name || !$email || !$fbid) {
      $this->loggerFactory->error('Failed to create user. Name: @name, email: @email, fbid: @fbid.', array(
        '@name' => $name,
        '@email' => $email,
        '@fbid' => $fbid,
      ));
      drupal_set_message(t("Error while creating user account. Please contact site administrator."), 'error');
      return FALSE;
    }

    // Check if site configuration allows new users to register.
    if ($this->registrationBlocked()) {
      $this->loggerFactory->warning('Failed to create user. User registration is disabled either in Drupal account settings or Simple FB Connect settings. Name: @name, email: @email, fbid: @fbid.', array(
        '@name' => $name,
        '@email' => $email,
        '@fbid' => $fbid,
      ));
      drupal_set_message(t('Only existing users can log in with Facebook. Contact system administrator.'), 'error');
      return FALSE;
    }

    // Set up the user fields.
    // - Username will be user's name on Facebook.
    // - Password can be very long since the user doesn't see this.
    $fields = array(
      'name' => $this->getUniqueUsername($name),
      'mail' => $email,
      'init' => $email,
      'pass' => user_password(32),
      'status' => $this->getNewUserStatus(),
    );

    // Try to download the profile picture and add it to user fields.
    if (user_picture_enabled() && $picture_url) {
      if ($file = $this->downloadProfilePic($picture_url, $fbid)) {
        $fields['user_picture'] = $file->id();
      }
    }

    // Create new user account.
    $new_user = User::create($fields);
    $new_user->save();

    // Log notice if new user was succesfully created.
    if ($new_user->id()) {
      $this->loggerFactory->notice('New user created. Username @username, UID: @uid', array(
        '@username' => $new_user->getUsername(),
        '@uid' => $new_user->id(),
      ));

      // Dispatch an event so that other modules can react to the user creation.
      // Set the account twice on the event: as the main subject but also in the
      // list of arguments.
      $event = new GenericEvent($new_user, ['account' => $new_user]);
      $this->eventDispatcher->dispatch('simple_fb_connect.user_created', $event);

      return $new_user;
    }

    // Something went wrong.
    drupal_set_message(t('Creation of user account failed. Please contact site administrator.'), 'error');
    $this->loggerFactory->error('Could not create new user.');
    return FALSE;
  }

  /**
   * Logs the user in.
   *
   * @todo Add Boost integtraion when Boost is available for D8
   *   https://www.drupal.org/node/2524372
   *
   * @param \Drupal\user\Entity\User $drupal_user
   *   User object.
   *
   * @return bool
   *   True if login was successful
   *   False if the login was blocked
   */
  public function loginUser(User $drupal_user) {
    // Prevent admin login if defined in module settings.
    if ($this->loginDisabledForAdmin($drupal_user)) {
      drupal_set_message(t('Facebook login is disabled for site administrator. Login with your local user account.'), 'error');
      return FALSE;
    }

    // Prevent login if user has one of the roles defined in module settings.
    if ($this->loginDisabledByRole($drupal_user)) {
      drupal_set_message(t('Facebook login is disabled for your role. Please login with your local user account.'), 'error');
      return FALSE;
    }

    // Check that the account is active and log the user in.
    if ($drupal_user->isActive()) {
      user_login_finalize($drupal_user);

      // Dispatch an event so that other modules can react to the user login.
      // Set the account twice on the event: as the main subject but also in the
      // list of arguments.
      $event = new GenericEvent($drupal_user, ['account' => $drupal_user]);
      $this->eventDispatcher->dispatch('simple_fb_connect.user_login', $event);

      // TODO: Add Boost cookie if Boost module is enabled
      // https://www.drupal.org/node/2524372
      drupal_set_message(t('You are now logged in as @username.', array('@username' => $drupal_user->getUsername())));
      return TRUE;
    }

    // If we are still here, account is blocked.
    drupal_set_message(t('You could not be logged in because your user account @username is not active.', array('@username' => $drupal_user->getUsername())), 'warning');
    $this->loggerFactory->warning('Facebook login for user @user prevented. Account is blocked.', array('@user' => $drupal_user->getUsername()));
    return FALSE;
  }

  /**
   * Saves post login path to session if postLoginPath query parameter is set.
   */
  public function setPostLoginPathFromRequest() {
    // Parse query string to an array.
    if ($query_string = $this->requestContext->getQueryString()) {
      parse_str($query_string, $query_params);
      // If postLoginPath is found, save the value to session.
      if (array_key_exists('postLoginPath', $query_params)) {
        $post_login_path = $query_params['postLoginPath'];
        $this->session->set('simple_fb_connect_post_login_path', $post_login_path);
      }
    }
  }

  /**
   * Returns the path the user should be redirected after a successful login.
   *
   * Return order:
   * 1. Path from query string, if set, valid and not external.
   * 2. Path from module settings, if valid and not external.
   * 3. User page.
   *
   * @return string
   *   URL where the user should be redirected after FB login.
   */
  public function getPostLoginPath() {
    // 1. Read the post login path from session.
    $post_login_path = $this->session->get('simple_fb_connect_post_login_path');
    if ($post_login_path) {
      $url = $this->pathValidator->getUrlIfValid($post_login_path);
      if ($url !== FALSE && $url->isExternal() === FALSE) {
        return $url->toString();
      }
    }

    // 2. Use post login path from module settings.
    $post_login_path = $this->moduleConfig->get('post_login_path');
    $url = $this->pathValidator->getUrlIfValid($post_login_path);
    if ($url !== FALSE && $url->isExternal() === FALSE) {
      return $url->toString();
    }

    // 3. Use user page.
    $post_login_path = 'user';
    $url = $this->pathValidator->getUrlIfValid($post_login_path);
    return $url->toString();
  }

  /**
   * Ensures that Drupal username will be unique.
   *
   * @param string $fb_name
   *   User's full name on Facebook.
   */
  protected function getUniqueUsername($fb_name) {
    $base = trim($fb_name);
    $i = 1;
    $candidate = $base;
    while (is_object(user_load_by_name($candidate))) {
      $i++;
      $candidate = $base . " " . $i;
    }
    return $candidate;
  }

  /**
   * Downloads the profile picture to Drupal filesystem.
   *
   * @param string $picture_url
   *   Absolute URL where to download the profile picture.
   * @param string $fbid
   *   Facebook ID of the user.
   *
   * @return \Drupal\file\FileInterface|false
   *   FileInterface object if file was succesfully downloaded
   *   False otherwise
   */
  protected function downloadProfilePic($picture_url, $fbid) {
    // Make sure that we have everything we need.
    if (!$picture_url || !$fbid) {
      return FALSE;
    }

    // Determine target directory. Replace tokens and convert to plain text.
    $directory = file_default_scheme() . '://' . $this->pictureConfig->get('settings.file_directory');
    $directory = PlainTextOutput::renderFromHtml(\Drupal::token()->replace($directory));
    if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {
      $this->loggerFactory->error('Could not save FB profile picture. Directory is not writeable: @directory', array('@directory' => $directory));
      return FALSE;
    }

    // Generate filename and ensure it's plain text. FB API always serves JPG.
    $filename = PlainTextOutput::renderFromHtml($fbid) . '.jpg';
    $destination = $directory . '/' . $filename;

    // Download the picture to local filesystem.
    if (!$file = system_retrieve_file($picture_url, $destination, TRUE, FILE_EXISTS_REPLACE)) {
      $this->loggerFactory->error('Could not download Facebook profile picture from url: @url', array('@url' => $picture_url));
      return FALSE;
    }

    return $file;
  }

  /**
   * Checks if current user is admin and admin login via FB is disabled.
   *
   * @param \Drupal\user\Entity\User $drupal_user
   *   User object.
   *
   * @return bool
   *   True if current user is admin and admin login via fB is disabled.
   *   False otherwise.
   */
  protected function loginDisabledForAdmin(User $drupal_user) {
    // Check if current user is admin.
    if ($drupal_user->id() == 1) {
      // Check if admin FB login is disabled.
      $disable_admin_login = $this->moduleConfig->get('disable_admin_login');
      if ($disable_admin_login) {
        $this->loggerFactory->warning('Facebook login for user @user prevented. Facebook login for site administrator (user 1) is disabled in module settings.', array('@user' => $drupal_user->getUsername()));
        return TRUE;
      }
    }

    // User is not admin or admin login is not disabled.
    return FALSE;
  }

  /**
   * Checks if user registration is blocked.
   *
   * Registration of new users can be blocked by Simple FB Connect settings or
   * Drupal account settings.
   *
   * @return bool
   *   True if registration is blocked
   *   False if registration is not blocked
   */
  protected function registrationBlocked() {
    // Check if SimpleFBConnect settings is login only.
    if ($this->moduleConfig->get('login_only')) {
      return TRUE;
    }

    // Check if Drupal account registration settings is Administrators only.
    if ($this->userConfig->get('register') == 'admin_only') {
      return TRUE;
    }

    // If we didnt' return TRUE already, registration is not blocked.
    return FALSE;
  }

  /**
   * Checks if the user has one of the "FB login disabled" roles.
   *
   * @param \Drupal\user\Entity\User $drupal_user
   *   User object.
   *
   * @return bool
   *   True if login is disabled for one of this user's role
   *   False if login is not disabled for this user's roles
   */
  protected function loginDisabledByRole(User $drupal_user) {
    // Read roles that are blocked from module settings.
    $disabled_roles = $this->moduleConfig->get('disabled_roles');

    // Loop through all roles the user has.
    foreach ($drupal_user->getRoles() as $role) {
      // Check if FB login is disabled for this role.
      // Disabled roles have value > 0.
      if (array_key_exists($role, $disabled_roles) && ($disabled_roles[$role] !== 0)) {
        $this->loggerFactory->warning('Facebook login for user @user prevented. Facebook login for role @role is disabled in module settings.', array(
          '@user' => $drupal_user->getUsername(),
          '@role' => $role,
        ));
        return TRUE;
      }
    }

    // FB login is not disabled for any of the user's roles.
    return FALSE;
  }

  /**
   * Returns the status for new users.
   *
   * @return int
   *   Value 0 means that new accounts require adminstaror approval.
   *   Value 1 means that visitors can register new accounts without approval.
   */
  protected function getNewUserStatus() {
    if ($this->userConfig->get('register') == 'visitors') {
      return 1;
    }
    return 0;
  }

}
