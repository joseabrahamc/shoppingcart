<?php 
//------------------------
//  Utility functions
//------------------------

/**
  * Get current URI removing the first part
  * Example: by default it would look like this "shoppingcart/controller/action"
  * Removing the first part it'll be "controller/action"
  * @return string $uri  processed URI
  */
function getURI() : string
{
  $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
  $uri = explode('/', $uri);
  array_shift($uri);
  $uri = implode('/', $uri);

  return $uri;
}

/**
* Get HTTP Method
* @return string   GET or POST
*/
function getMethod() : string
{
  return $_SERVER['REQUEST_METHOD'];
}

/**
* Load a view
* @param string $file  Path to file
* @param array  $data  Optional data that the view may require
*/
function renderView(string $file, array $data = []) : void
{
  // Specify our Twig templates location
  $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
  // Instantiate Twig
  $twig = new \Twig_Environment($loader, ['debug' => true]);
  $twig->addExtension(new \Twig\Extension\DebugExtension());
  // If there's a session running, get user data
  if (isset($_SESSION['user']))
  {
    $twig->addGlobal('current_user', ['userSession' => \Model\Session::getUser(), 'userCart' => \Model\Cart::getCartItems()]);
  }
  // Get flash messages if there are any
  $twig->addGlobal('flash_message', getFlashNotification());

  echo $twig->render($file, ['data' => $data]);
}

/**
 * Redirect method
 * @param  string $file Path to desired page
 */
function redirect(string $file) : void
{
  header('Location: ' . URLROOT . $file, true, 303);
  exit;
}

/**
 * Set flash message
 * @param  string $message Message sent to the user
 * @param  string $type    Success message, warning, error, etc.
 */
function flash(string $message, string $type = SUCCESS) : void
{
  if (!isset($_SESSION['flash_notification'])) 
  {
    $_SESSION['flash_notification'] = ['message' => $message, 'type' => $type];
  }
}

/**
 * Get flash message
 * @return  array Array containing message body and type
 */
function getFlashNotification()
{
  if (isset($_SESSION['flash_notification'])) 
  {
    $message = $_SESSION['flash_notification'];
    unset($_SESSION['flash_notification']);

    return $message;
  }
}