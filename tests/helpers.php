<?php

use App\User;


/**
 * @param $what
 * @param array $overrides
 * @param int $qty
 * @return User
 */
function create($what, $overrides = [], $qty = 1)
{
    if (is_numeric($overrides)) {
        $qty = $overrides;
        $overrides = [];
    }
    return ($qty > 1) ?
        factory($what, $qty)->create($overrides)->map->fresh() :
        factory($what)->create($overrides)->fresh();
}

/**
 * @param $what
 * @param $state
 * @param array $overrides
 * @param int $qty
 * @return User
 */
function create_state($what, $state, $overrides = [], $qty = 1)
{
    if (is_numeric($overrides)) {
        $qty = $overrides;
        $overrides = [];
    }
    return ($qty > 1) ?
        factory($what, $qty)->states($state)->create($overrides)->map->fresh() :
        factory($what)->states($state)->create($overrides)->fresh();
}

function create_array($what, $overrides = [], $qty = 1)
{
    if (is_numeric($overrides)) {
        $qty = $overrides;
        $overrides = [];
    }
    return ($qty > 1) ?
        factory($what, $qty)->create($overrides)->map->fresh()->map->toArray()->all() :
        factory($what)->create($overrides)->fresh()->toArray();
}


function make($what, $overrides = [], $qty = 1)
{
    if (is_numeric($overrides)) {
        $qty = $overrides;
        $overrides = null;
    }
    return ($qty > 1) ?
        factory($what, $qty)->make($overrides) :
        factory($what)->make($overrides);
}


function make_array($what, $overrides = [], $qty = 1)
{
    if (is_numeric($overrides)) {
        $qty = $overrides;
        $overrides = null;
    }
    return ($qty > 1) ?
        factory($what, $qty)->make($overrides)->toArray() :
        factory($what)->make($overrides)->toArray();
}
