<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\Store;

class StoreTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [];
    protected array $defaultIncludes = [];

    public function transform(Store $model)
    {
        return $model->getAttributes();
    }
}
