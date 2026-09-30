<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Link lifetime
    |--------------------------------------------------------------------------
    |
    | Number of days a freshly issued access link stays valid for.
    |
    */
    'link_lifetime_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | History size
    |--------------------------------------------------------------------------
    |
    | How many of the most recent spins are shown to a player.
    |
    */
    'history_size' => 3,

    /*
    |--------------------------------------------------------------------------
    | Number range
    |--------------------------------------------------------------------------
    |
    | The upper bound (inclusive) of the random number rolled on each spin.
    | The lower bound is always 1.
    |
    */
    'number_max' => 1000,

];
