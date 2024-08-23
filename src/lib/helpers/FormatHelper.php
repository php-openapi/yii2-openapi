<?php

namespace cebe\yii2openapi\lib\helpers;

class FormatHelper
{
    /**
     * @param $description
     * @return string
     */
    public static function getFormattedDescription($description): string
    {
        $descriptionArr = explode("\n", trim($description));
        $descriptionArr = array_map(function($item) {
            return $item !== '' ? ' ' . $item : $item;
        }, $descriptionArr);
        return implode("\n *", $descriptionArr);
    }
}