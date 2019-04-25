<?php 
namespace Model;

use \App\Database;

/**
 * Cart model class
 * Performs CRUD operations related to the cart
 */
class Cart extends Database
{
  /**
   * This method is called right after a new user is registered
   * Creates associated cart to the new user
   * @param  object   User ID
   * @return boolean  True if everything ok, false if not
   */
  public function createNewCart($user) : bool
  {
    $db = static::getDB();
    $stmt = $db->prepare("INSERT INTO cart (`user_id`) VALUES(:id)");
    $stmt->bindValue(':id', $user->id, \PDO::PARAM_STR);
    return $stmt->execute() ? true : false;
  }

  /**
   * Get data from table 'cart'. Mostly and utility function
   * @param  int    User's id
   * @return mixed  Array if records found, false if not
   */
  public function getUserCartId($user)
  {
    $db = static::getDB();
    $stmt = $db->prepare("SELECT * FROM cart WHERE user_id = :user");
    $stmt->bindValue(':user', $user, \PDO::PARAM_STR);
    return $stmt->execute() ? $stmt->fetch(\PDO::FETCH_OBJ) : false;
  }


  /**
   * Get cart items for the current user
   * @return mixed  Array if records found, false if not
   */
  public function cartItems()
  {
    $db = static::getDB();
    $stmt = $db->prepare("SELECT cart_items.* FROM cart_items INNER JOIN cart on cart_items.cart_id = cart.id WHERE cart.user_id = :user");
    $stmt->bindValue(':user', intval($_SESSION['user_id']), \PDO::PARAM_INT);
    return $stmt->execute() ? $stmt->fetchAll(\PDO::FETCH_OBJ) : false;
  }

  /**
   * Add item to cart, if the item is already there then updates de information
   * @param object $item      Gotten item data from Product::getItem()
   * @param int    $quantity  Quantity the user wants to add
   * @return boolean          True if everything ok, false if not
   */
  public function addItem($item, $quantity) : bool
  {
    if (preg_match('/\d+/', $quantity))
    {
      $userCart = self::getUserCartId($_SESSION['user_id']);
      $db = static::getDB();

      // First check if the item is in the cart already, if so, update quantity and subtotal
      if (self::itemIsAlreadyInCart($item->product_id, $userCart->id))
        $stmt = $db->prepare("UPDATE cart_items SET quantity = :quantity, subtotal = :subtotal WHERE product_id = :item AND cart_id = :cart");
      else
        $stmt = $db->prepare("INSERT INTO cart_items VALUES(:cart, :item, :quantity, :subtotal)");

      $stmt->bindValue(':cart', intval($userCart->id), \PDO::PARAM_INT);
      $stmt->bindValue(':item', intval($item->product_id), \PDO::PARAM_INT);
      $stmt->bindValue(':quantity', intval($quantity), \PDO::PARAM_INT);
      $stmt->bindValue(':subtotal', ($item->price * intval($quantity)), \PDO::PARAM_STR);
      return $stmt->execute() ? true : false;
    }
    else
      return false;
  }

  /**
   * Remove an item from the cart
   * @param  int      ID of item to remove
   * @return boolean  True if execution was succesful, false if not
   */
  public function removeItem($item) : bool
  {
    $db = static::getDB();

    $stmt = $db->prepare("DELETE cart_items.* FROM cart_items INNER JOIN cart on cart_items.cart_id = cart.id WHERE cart.user_id = :user AND cart_items.product_id = :product");
    $stmt->bindValue(':user', intval($_SESSION['user_id']), \PDO::PARAM_INT);
    $stmt->bindValue(':product', intval($item), \PDO::PARAM_INT);
    return $stmt->execute() ? true : false;
  }

  /**
   * Check if an item is already in the user's cart
   * @param  int  $item     ID of item to check
   * @param  int  $cart_id  ID of corresponding cart
   * @return mixed          Array if item was found, false if not.
   */
  private function itemIsAlreadyInCart($item, $cart_id)
  {
    $db = static::getDB();

    $stmt = $db->prepare("SELECT * FROM cart_items WHERE product_id = :product AND cart_id = :cart");
    $stmt->bindValue(':product', intval($item), \PDO::PARAM_INT);
    $stmt->bindValue(':cart', intval($cart_id), \PDO::PARAM_INT);
    return $stmt->execute() ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
  }
}