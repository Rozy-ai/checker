<?php

namespace common\models;

use yii\db\{ActiveQuery, ActiveRecord};

/**
 * This is the model class for table "{{%billing}}".
 *
 * @property int $profile_type Тип профиля
 * @property int $source_id Источник
 * @property float $price1 Цена 1
 * @property float $price2 Цена 2
 * @property int $max_views_count Максимальное количество просмотра одного товара
 * @property int $cancel_show_count Ограничения отмены открытия просмотра товара
 * @property bool $inner_page Доступ к внутренней странице товара
 *
 * @property Source $source
 * @property string $profileTypeLabel
 */
class ProfileTypeSetting extends ActiveRecord
{
    public const INDIVIDUAL = 1;
    public const GENERAL = 2;
    public const FREE = 3;

    public const TYPES = [
        self::INDIVIDUAL,
        self::GENERAL,
        self::FREE,
    ];

    public const TYPE_WITH_LABELS = [
        self::INDIVIDUAL => 'Individual',
        self::GENERAL => 'General',
        self::FREE => 'Free',
    ];

    public static function initWithDefaultValues(int $profileType, int $sourceId): self
    {
        $new = new self();
        $new->profile_type = $profileType;
        $new->source_id = $sourceId;
        $new->price1 = 0.0;
        $new->price2 = 0.0;
        $new->max_views_count = 0;
        $new->cancel_show_count = 0;
        $new->inner_page = false;

        $new->save();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%profile_types_settings}}';
    }

    /**
     * @return self[]
     */
    public static function allWithUniqKeys(): array
    {
        $result = [];

        /**
         * @var self $setting
         */
        foreach (self::find()->joinWith('source')->all() as $setting) {
            $result[$setting->source_id . '_' . $setting->profile_type] = $setting;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'profile_type' => 'Тип профиля',
            'profileTypeLabel' => 'Тип профиля',
            'source_id' => 'Источник',
            'source.name' => 'Источник',
            'price1' => 'Цена 1',
            'price2' => 'Цена 2',
            'max_views_count' => 'Максимальное количество просмотра одного товара',
            'cancel_show_count' => 'Ограничения отмены открытия просмотра товара',
            'inner_page' => 'Доступ к внутренней странице товара',
        ];
    }

    /**
     * Gets query for [[Source]].
     *
     * @return ActiveQuery
     */
    public function getSource(): ActiveQuery
    {
        return $this->hasOne(Source::class, ['id' => 'source_id']);
    }

    public function getProfileTypeLabel(): string
    {
        return self::TYPE_WITH_LABELS[$this->profile_type] ?? '';
    }
}
