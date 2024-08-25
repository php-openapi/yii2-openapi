<?php

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