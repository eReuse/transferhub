<?php

/**
 * @file
 * Contains \Drupal\simple_fb_connect\Controller\SimpleFbConnectController.
 */

namespace Drupal\simple_fb_connect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Facebook\FacebookRedirectLoginHelper;
use Drupal\simple_fb_connect\SimpleFbConnectFbManager;
use Drupal\simple_fb_connect\SimpleFbConnectUserManager;

/**
 * Returns responses for Simple FB Connect module routes.
 */
class SimpleFbConnectController extends ControllerBase {

  protected $fbManager;
  protected $userManager;

  /**
   * Constructor.
   *
   * The constructor parameters are passed from the create() method.
   */
  public function __construct(SimpleFbConnectFbManager $fb_manager, SimpleFbConnectUserManager $user_manager) {
    $this->fbManager = $fb_manager;
    $this->userManager = $user_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_fb_connect.fb_manager'),
      $container->get('simple_fb_connect.user_manager')
    );
  }

  /**
   * Response for path 'user/simple-fb-connect'.
   *
   * Redirects the user to FB for authentication.
   */
  public function redirectToFb() {
    // Validate configuration.
    if (!$this->fbManager->validateConfig()) {
      drupal_set_message(t('Simple FB Connect not configured properly. Contact site administrator.'), 'error');
      return $this->redirect('user.login');
    }

    // Save post login path to session if it has been set as query parameter.
    $this->userManager->setPostLoginPathFromRequest();

    // Redirect the user to FB for authentication.
    $fb_login_url = $this->fbManager->getFbLoginUrl();
    return new TrustedRedirectResponse($fb_login_url);
  }

  /**
   * Response for path 'user/simple-fb-connect/return'.
   *
   * Facebook returns the user here after user has authenticated in FB.
   */
  public function returnFromFb() {
    // Validate configuration.
    if (!$this->fbManager->validateConfig()) {
      drupal_set_message(t('Simple FB Connect not configured properly.'), 'error');
      return $this->redirect('user.login');
    }

    // SDK can start FacebookSession from the page where FB returned the user.
    $login_helper = new FacebookRedirectLoginHelper($this->fbManager->getReturnUrl());
    if (!$this->fbManager->startFbSession($login_helper)) {
      drupal_set_message(t("Facebook login failed."), 'error');
      return $this->redirect('user.login');
    }

    // Get a validated FacebookSession object.
    if (!$fb_session = $this->fbManager->getFbSession()) {
      drupal_set_message(t("Facebook login failed."), 'error');
      return $this->redirect('user.login');
    }

    // Get user's FB profile from Facebook API.
    if (!$fb_profile = $this->fbManager->getFbProfile($fb_session)) {
      drupal_set_message(t("Facebook login failed."), 'error');
      return $this->redirect('user.login');
    }

    // Get user's email from the FB profile.
    if (!$email = $this->fbManager->getEmail($fb_profile)) {
      drupal_set_message(t('Facebook login failed. This site requires permission to get your email address.'), 'error');
      return $this->redirect('user.login');
    }

    // If we have an existing user with the same email address, try to log in.
    if ($drupal_user = $this->userManager->getUserByEmail($email)) {
      if ($this->userManager->loginUser($drupal_user)) {
        return new RedirectResponse($this->userManager->getPostLoginPath());
      }
      else {
        return $this->redirect('user.login');
      }
    }

    // If there was no existing user, try to create a new user.
    $drupal_user = $this->userManager->createUser(
      $fb_profile->getProperty('name'),
      $email,
      $fb_profile->getProperty('id'),
      $this->fbManager->getFbProfilePicUrl($fb_session)
    );

    // Log the newly created user in.
    if ($drupal_user) {
      if ($this->userManager->loginUser($drupal_user)) {
        return new RedirectResponse($this->userManager->getPostLoginPath());
      }
      else {
        // New user was succesfully created but the account is pending approval.
        drupal_set_message(t('You will receive an email when site administrator activates your account.'), 'warning');
        return $this->redirect('user.login');
      }
    }
    else {
      // User could not be created.
      return $this->redirect('user.login');
    }

    // This should never be reached, user should have been redirected already.
    throw new AccessDeniedHttpException();
  }

}
