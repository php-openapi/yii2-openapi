<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\yii2openapi\lib\CodeFiles;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\items\FractalAction;
use cebe\yii2openapi\lib\items\RestAction;
use Yii;
use yii\base\InvalidConfigException;
use yii\gii\CodeFile;

class UrlRulesGenerator
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var array|RestAction[]|FractalAction[]
     */
    protected $actions;

    public function __construct(Config $config, array $actions = [])
    {
        $this->config = $config;
        $this->actions = $actions;
    }

    /**
     * @return CodeFiles
     * @throws InvalidConfigException
     */
    public function generate(): CodeFiles
    {
        if (!$this->config->generateUrls) {
            return new CodeFiles([]);
        }

        $urls = [];
        $optionsUrls = [];
        foreach ($this->actions as $action) {
            $urls["{$action->requestMethod} {$action->urlPattern}"] = $action->route;
            $optionsUrls[$action->urlPattern] = $action->getOptionsRoute();
        }
        $urls = array_merge($urls, $optionsUrls);
        $file = new CodeFile(
            Yii::getAlias($this->config->urlConfigFile),
            $this->config->render('urls.php', ['urls' => $urls])
        );
        return new CodeFiles([$file]);
    }
}
