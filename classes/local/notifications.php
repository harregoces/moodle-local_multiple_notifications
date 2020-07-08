<?php
/**
 * Created by PhpStorm.
 * User: hernan.arregoces
 * Date: 3/22/2019
 * Time: 6:46 PM
 */

namespace local_multiple_notifications\local;

use core\persistent;

class notifications extends persistent {

    const TABLE = "local_multiple_notifications";

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'course' => [
                'type' => PARAM_INT
            ],
            'salesforce' => [
                'type' => PARAM_TEXT
            ]
        ];
    }
}
