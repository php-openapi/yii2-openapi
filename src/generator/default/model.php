<?php
/**
 * @var \cebe\yii2openapi\lib\items\DbModel $model
 * @var string $namespace
 * @var string $relationNamespace
 **/
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

?>
<?= '<?php' ?>

namespace <?= $namespace ?>;

use yii\base\Model;

/**
 * <?= empty($model->description) ? '' : str_replace("\n", "\n * ", ' ' . trim($model->description)) ?>

 *
 */
class <?= $model->getClassName() ?> extends Model
{
<?php foreach ($model->attributes as $attribute): ?>
<?php if (!$attribute->isReference()):?>
    /**
    * @var <?=$attribute->phpType.' '.$attribute->description.PHP_EOL?>
    */
    public $<?= $attribute->propertyName ?>;

<?php endif; ?>
<?php endforeach; ?>
<?php foreach ($model->relations as $relationName => $relation): ?>
    /**
    * @var <?=$relation->isHasOne()? $relation->getClassName(): 'array|'.$relation->getClassName().'[]'.PHP_EOL?>
    */
    public $<?= $relationName ?>;

<?php endforeach; ?>
<?php foreach ($model->nonDbRelations as $relationName => $relation): ?>
    /**
    * @var <?=$relation->isHasOne()? $relation->getClassName(): 'array|'.$relation->getClassName().'[]'.PHP_EOL?>
    */
    public $<?= $relationName ?>;

<?php endforeach; ?>

    public function rules()
    {
        return <?=$model->getValidationRules()?>;
    }
}
