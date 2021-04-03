<?php
namespace BANG\Core;

/*
 * Globals
 */
class Globals extends \BANG\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'stack' => 'obj', // DO NOT MODIFY, USED BY STACK ENGINE
    'stackCtx' => 'obj', // DO NOT MODIFY, USED BY STACK ENGINE

    'pIdTurn' => 'int', // Player's id whose turn is
  ];

  protected static $table = 'global_variables';
  protected static $primary = 'name';
  protected static function cast($row)
  {
    $val = json_decode($row['value'], true);
    return self::$variables[$row['name']] == 'int' ? ((int) $val) : $val;
  }

  /*
   * Fetch all existings variables from DB
   */
  protected static $data = [];
  public static function fetch()
  {
    foreach (
      self::DB()
        ->select(['value', 'name'])
        ->get(false)
      as $name => $variable
    ) {
      if (\array_key_exists($name, self::$variables)) {
        self::$data[$name] = $variable;
      }
    }
    self::$initialized = true;
  }

  /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
  public static function create($name)
  {
    if (!\array_key_exists($name, self::$variables)) {
      return;
    }

    $val = self::$variables[$name] == 'int' ? 0 : [];
    self::DB()->insert([
      'name' => $name,
      'value' => \json_encode($val),
    ]);
    self::$data[$name] = $val;
  }

  public static function __callStatic($method, $args)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    if (preg_match('/^([gs]et|inc)([A-Z])(.*)$/', $method, $match)) {
      // Sanity check : does the name correspond to a declared variable ?
      $name = strtolower($match[2]) . $match[3];
      if (!\array_key_exists($name, self::$variables)) {
        throw new InvalidArgumentException("Property {$name} doesn't exist");
      }

      // Create in DB if don't exist yet
      if (!\array_key_exists($name, self::$data)) {
        self::create($name);
      }

      if ($match[1] == 'get') {
        // Basic getters
        return self::$data[$name];
      } elseif ($match[1] == 'set') {
        // Setters in DB and update cache
        $value = $args[0];
        self::$data[$name] = $value;
        self::DB()->update(
          [
            'value' => \json_encode($value),
          ],
          $name
        );
        return $value;
      } elseif ($match[1] == 'inc') {
        if (self::$variables[$name] != 'int') {
          throw new InvalidArgumentException("Trying to increase {$name} which is not an int");
        }

        $getter = 'get' . $match[2] . $match[3];
        $setter = 'set' . $match[2] . $match[3];
        return self::$setter(self::$getter() + (empty($args) ? 1 : $args[0]));
      }
    }
    return undefined;
  }
}
