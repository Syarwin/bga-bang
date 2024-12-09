<?php

namespace BANG\Core;

use bang;

/*
 * Game: a wrapper over table object to allow more generic modules
 */

class Game
{
    public static function get()
    {
        return bang::get();
    }
}
