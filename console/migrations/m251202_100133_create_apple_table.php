<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple}}`.
 */
class m251202_100133_create_apple_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%apple}}', [
            'id' => $this->primaryKey(),
            'color' => $this->string(50)->notNull()->comment('Цвет яблока'),
            'created_at' => $this->integer()->notNull()->comment('Дата появления (unix timestamp)'),
            'fell_at' => $this->integer()->null()->comment('Дата падения (unix timestamp)'),
            'status' => "ENUM('on_tree', 'on_ground', 'rotten') NOT NULL DEFAULT 'on_tree' COMMENT 'Статус яблока'",
            'eaten_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0)->comment('Процент съеденной части'),
        ]);
        
        $this->createIndex('idx-apple-status', '{{%apple}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apple}}');
    }
}
