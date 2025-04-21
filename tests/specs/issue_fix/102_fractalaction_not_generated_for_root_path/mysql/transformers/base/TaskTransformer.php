<?php
namespace app\transformers\base;

use League\Fractal\TransformerAbstract;
use app\models\Task;

class TaskTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [];
    protected array $defaultIncludes = [];

    public function transform(Task $model)
    {
        return $model->getAttributes();
    }
}
