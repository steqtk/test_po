<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $apples common\models\Apple[] */

$this->title = 'Управление яблоками';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="apple-index">
    <h1><?= Html::encode($this->title) ?> 🍎🍏</h1>

    <div class="mb-4">
        <?= Html::beginForm(['generate'], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('🎲 Сгенерировать яблоки', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::endForm() ?>
        
        <span class="ms-3 text-muted">Всего яблок: <strong><?= count($apples) ?></strong></span>
    </div>

    <?php if (empty($apples)): ?>
        <div class="alert alert-info">
            <strong>🌳 Яблок пока нет!</strong> Нажмите кнопку "Сгенерировать яблоки" чтобы создать их.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($apples as $apple): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <span style="font-size: 2rem;"><?= $apple->getEmoji() ?></span>
                                    <span class="badge bg-<?= $apple->getColorClass() ?>">
                                        <?= Html::encode($apple->color) ?>
                                    </span>
                                </h5>
                                <span class="badge bg-secondary">ID: <?= $apple->id ?></span>
                            </div>

                            <div class="mb-3">
                                <div class="mb-2">
                                    <strong>Статус:</strong>
                                    <?php
                                    $statusClass = [
                                        'on_tree' => 'success',
                                        'on_ground' => 'warning',
                                        'rotten' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusClass[$apple->status] ?>">
                                        <?= $apple->getStatusName() ?>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <strong>Размер:</strong> 
                                    <span class="text-primary"><?= $apple->getSize() ?></span>
                                    (съедено: <?= $apple->eaten_percent ?>%)
                                </div>

                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= 100 - $apple->eaten_percent ?>%" 
                                         aria-valuenow="<?= 100 - $apple->eaten_percent ?>" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        <?= 100 - $apple->eaten_percent ?>%
                                    </div>
                                </div>

                                <div class="small text-muted">
                                    <div>📅 Появилось: <?= Yii::$app->formatter->asDatetime($apple->created_at) ?></div>
                                    <?php if ($apple->fell_at): ?>
                                        <div>🍂 Упало: <?= Yii::$app->formatter->asDatetime($apple->fell_at) ?></div>
                                        <div>⏱️ На земле: <?= Yii::$app->formatter->asRelativeTime($apple->fell_at) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-text">
                                <!-- Кнопка "Упасть" -->
                                <?php if ($apple->status === 'on_tree'): ?>
                                    <?= Html::beginForm(['fall', 'id' => $apple->id], 'post', ['class' => 'd-inline']) ?>
                                        <?= Html::submitButton('🍂 Упасть', [
                                            'class' => 'btn btn-warning btn-sm',
                                            'data' => [
                                                'confirm' => 'Яблоко упадет с дерева. Продолжить?',
                                            ],
                                        ]) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>

                                <!-- Форма "Съесть" -->
                                <?php if ($apple->status === 'on_ground' && $apple->eaten_percent < 100): ?>
                                    <div class="input-group input-group-sm mt-2">
                                        <?= Html::beginForm(['eat', 'id' => $apple->id], 'post', ['class' => 'd-flex gap-2 w-100']) ?>
                                            <?= Html::input('number', 'percent', 25, [
                                                'class' => 'form-control',
                                                'min' => 1,
                                                'max' => 100,
                                                'placeholder' => '%',
                                                'style' => 'max-width: 80px;'
                                            ]) ?>
                                            <?= Html::submitButton('🍴 Съесть %', [
                                                'class' => 'btn btn-primary btn-sm',
                                            ]) ?>
                                        <?= Html::endForm() ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Сообщения о недоступности -->
                                <?php if ($apple->status === 'rotten'): ?>
                                    <div class="alert alert-danger mb-0 mt-2 py-1 px-2 small">
                                        ☠️ Яблоко испорчено, есть нельзя
                                    </div>
                                <?php endif; ?>

                                <!-- Кнопка удаления -->
                                <div class="mt-2">
                                    <?= Html::beginForm(['delete', 'id' => $apple->id], 'post', ['class' => 'd-inline']) ?>
                                        <?= Html::submitButton('🗑️ Удалить', [
                                            'class' => 'btn btn-danger btn-sm',
                                            'data' => [
                                                'confirm' => 'Вы уверены, что хотите удалить яблоко?',
                                            ],
                                        ]) ?>
                                    <?= Html::endForm() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
</style>

