<?php

namespace BANG\Core;

use banghighnoon;

/*
 * Game: a wrapper over table object to allow more generic modules
 */

class Game
{
    public static function get()
    {
        return banghighnoon::get();
    }
}
