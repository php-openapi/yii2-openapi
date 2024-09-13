<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\helpers;

class FormatHelper
{
    /**
     * @param string $description
     * @param int $spacing
     * @return string
     */
    public static function getFormattedDescription(string $description, int $spacing = 1): string
    {
        $descriptionArr = explode("\n", trim($description));
        $descriptionArr = array_map(function ($item) {
            return $item === '' ? '' : ' ' . $item;
        }, $descriptionArr);
        return implode("\n".str_repeat(" ", $spacing)."*", $descriptionArr);
    }
}
