<?php

use yii\db\Schema;
use yii\db\Migration;

class m150426_151733_1stmigration extends Migration
{
    /**
     * up
     *
     * Миграция на шаг вперёд
     *
     * @return bool|void
     */
    public function up()
    {
        // таблица пользователи
        $this->createTable('{{%user}}', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING.' NOT NULL',
            'password' => Schema::TYPE_STRING.' NOT NULL',
            'token' => Schema::TYPE_STRING.' NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');

        // поле username будет уникальным
        $this->createIndex('idx_username', '{{%user}}', 'username', true);

        // таблица видео файлы
        $this->createTable('{{%video}}', [
            'id' => Schema::TYPE_PK,
            'originalName' => Schema::TYPE_STRING.' NOT NULL',
            'fileName' => Schema::TYPE_STRING.' NOT NULL',
            'newName' => Schema::TYPE_STRING,
            'isConverted' => Schema::TYPE_BOOLEAN.' NOT NULL DEFAULT 0',
            'createTime' => Schema::TYPE_INTEGER.' NOT NULL',
            'status' => Schema::TYPE_BOOLEAN.' NOT NULL DEFAULT 0',
            'userId' => Schema::TYPE_INTEGER.' NOT NULL',
        ], 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB');

        // добавим внешний ключ
        $this->createIndex('idx_user', '{{%video}}', 'userId', true);
        $this->addForeignKey("user_fk", "{{%user}}", "id", "{{%video}}", "userId");

        return true;
    }


    /**
     * down
     *
     * Миграция на шаг назад
     *
     * @return bool
     */
    public function down()
    {
        echo "m150426_151733_1stmigration cannot be reverted.\n";
        return false;
    }

}
