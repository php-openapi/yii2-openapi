<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\Pet;
use app\transformers\StoreTransformer;

class PetTransformer extends TransformerAbstract
{
    protected array $availableIncludes = ['store'];
    protected array $defaultIncludes = [];

    public function transform(Pet $model)
    {
        return $model->getAttributes();
    }

    public function includeStore(Pet $model)
    {
        $relation = $model->store;
        if ($relation === null) {
            return $this->null();
        }
        $transformer = new StoreTransformer();
        return $this->item($relation, $transformer, 'stores');
    }
}
