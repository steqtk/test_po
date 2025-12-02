<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Модель Apple (Яблоко)
 * 
 * @property int $id
 * @property string $color Цвет яблока
 * @property int $created_at Дата появления (unix timestamp)
 * @property int|null $fell_at Дата падения (unix timestamp)
 * @property string $status Статус яблока (on_tree, on_ground, rotten)
 * @property float $eaten_percent Процент съеденной части
 */
class Apple extends ActiveRecord
{
    // Константы статусов
    const STATUS_ON_TREE = 'on_tree';
    const STATUS_ON_GROUND = 'on_ground';
    const STATUS_ROTTEN = 'rotten';
    
    // Константы цветов
    const COLOR_RED = 'red';
    const COLOR_GREEN = 'green';
    const COLOR_YELLOW = 'yellow';
    
    // Время до гниения (5 часов в секундах)
    const ROTTEN_TIME = 5 * 60 * 60;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%apple}}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => function() {
                    // Случайный timestamp в прошлом (до 30 дней назад)
                    return mt_rand(time() - 30 * 24 * 60 * 60, time());
                },
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['color'], 'required'],
            [['created_at', 'fell_at'], 'integer'],
            [['eaten_percent'], 'number', 'min' => 0, 'max' => 100],
            [['color'], 'string', 'max' => 50],
            [['status'], 'in', 'range' => [self::STATUS_ON_TREE, self::STATUS_ON_GROUND, self::STATUS_ROTTEN]],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'color' => 'Цвет',
            'created_at' => 'Дата появления',
            'fell_at' => 'Дата падения',
            'status' => 'Статус',
            'eaten_percent' => 'Съедено (%)',
        ];
    }
    
    /**
     * Создать яблоко со случайным цветом
     * 
     * @return Apple
     */
    public static function createRandom()
    {
        $colors = [self::COLOR_RED, self::COLOR_GREEN, self::COLOR_YELLOW];
        $apple = new self();
        $apple->color = $colors[array_rand($colors)];
        $apple->status = self::STATUS_ON_TREE;
        $apple->eaten_percent = 0;
        return $apple;
    }
    
    /**
     * Получить размер яблока (1 - целое, 0 - полностью съедено)
     * 
     * @return float
     */
    public function getSize()
    {
        return round((100 - $this->eaten_percent) / 100, 2);
    }
    
    /**
     * Упасть с дерева на землю
     * 
     * @return bool
     * @throws \Exception
     */
    public function fallToGround()
    {
        if ($this->status !== self::STATUS_ON_TREE) {
            throw new \Exception('Яблоко уже не на дереве');
        }
        
        $this->status = self::STATUS_ON_GROUND;
        $this->fell_at = time();
        
        return $this->save(false);
    }
    
    /**
     * Съесть часть яблока
     * 
     * @param float $percent Процент от текущего размера
     * @return bool
     * @throws \Exception
     */
    public function eat($percent)
    {
        // Обновляем статус (проверка на гниение)
        $this->updateStatus();
        
        // Проверка: нельзя съесть яблоко на дереве
        if ($this->status === self::STATUS_ON_TREE) {
            throw new \Exception('Съесть нельзя, яблоко на дереве');
        }
        
        // Проверка: нельзя съесть гнилое яблоко
        if ($this->status === self::STATUS_ROTTEN) {
            throw new \Exception('Съесть нельзя, яблоко испорчено');
        }
        
        // Проверка валидности процента
        if ($percent < 0 || $percent > 100) {
            throw new \Exception('Процент должен быть от 0 до 100');
        }
        
        // Увеличиваем процент съеденного
        $this->eaten_percent += $percent;
        
        // Если яблоко полностью съедено - удаляем
        if ($this->eaten_percent >= 100) {
            $this->eaten_percent = 100;
            $this->save(false);
            $this->delete();
            return true;
        }
        
        return $this->save(false);
    }
    
    /**
     * Обновить статус яблока (проверка на гниение)
     * 
     * @return void
     */
    public function updateStatus()
    {
        // Если яблоко на земле и прошло более 5 часов - оно гнилое
        if ($this->status === self::STATUS_ON_GROUND && $this->fell_at !== null) {
            $timeOnGround = time() - $this->fell_at;
            if ($timeOnGround >= self::ROTTEN_TIME) {
                $this->status = self::STATUS_ROTTEN;
                $this->save(false);
            }
        }
    }
    
    /**
     * Получить название статуса на русском
     * 
     * @return string
     */
    public function getStatusName()
    {
        $statuses = [
            self::STATUS_ON_TREE => 'Висит на дереве',
            self::STATUS_ON_GROUND => 'Лежит на земле',
            self::STATUS_ROTTEN => 'Гнилое',
        ];
        
        return $statuses[$this->status] ?? 'Неизвестно';
    }
    
    /**
     * Получить CSS класс для цвета
     * 
     * @return string
     */
    public function getColorClass()
    {
        $classes = [
            self::COLOR_RED => 'danger',
            self::COLOR_GREEN => 'success',
            self::COLOR_YELLOW => 'warning',
        ];
        
        return $classes[$this->color] ?? 'secondary';
    }
    
    /**
     * Получить эмодзи яблока по цвету
     * 
     * @return string
     */
    public function getEmoji()
    {
        $emojis = [
            self::COLOR_RED => '🍎',
            self::COLOR_GREEN => '🍏',
            self::COLOR_YELLOW => '🍐',
        ];
        
        return $emojis[$this->color] ?? '🍎';
    }
    
    /**
     * Перед сохранением - обновить статус
     */
    public function beforeSave($insert)
    {
        if (!$insert) {
            $this->updateStatus();
        }
        return parent::beforeSave($insert);
    }
}

