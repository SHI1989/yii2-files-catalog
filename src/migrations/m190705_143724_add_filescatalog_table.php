<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

use eseperio\filescatalog\dictionaries\InodeTypes;
use yii\db\Migration;

/**
 * Class m190705_143724_add_filescatalog_table
 */
class m190705_143724_add_filescatalog_table extends Migration
{

    private $inodeTableName = "fcatalog_inodes";
    private $inodePermissionTableName = "fcatalog_inodes_perm";
    private $inodeVersionsTableName = "fcatalog_inodes_version";

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->inodeTableName, [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(36),
            'name' => $this->string(255),
            'extension' => $this->string(16),
            'mime' => $this->string(128),
            'type' => $this->integer(1)->defaultValue(InodeTypes::TYPE_FILE)->notNull(),
            'parent_id' => $this->integer()->defaultValue(0)->notNull(),
            'md5hash' => $this->string(32),
            'depth' => $this->integer()->notNull(),
            'filesize' => $this->bigInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);
        $this->createIndex('idx_name_ext_inode', $this->inodeTableName, [
            'uuid',
            'type'
        ]);

        $this->createIndex('parent_id_index', $this->inodeTableName, [
            'parent_id'
        ]);

        $this->createTable($this->inodePermissionTableName, [
            'inode_id' => $this->integer()->comment('Inode id'),
            'user_id' => $this->integer(),
            'role' => $this->string(64)
        ]);
        $this->addPrimaryKey('inode_perms', $this->inodePermissionTableName, [
            'inode_id',
            'user_id',
            'role'
        ]);


        $this->createTable($this->inodeVersionsTableName, [
            'file_id' => $this->integer(),
            'version_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);

        $this->addPrimaryKey('inode_versions_pk', $this->inodeVersionsTableName, [
            'file_id',
            'version_id'
        ]);

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->inodePermissionTableName);
        $this->dropTable($this->inodeTableName);
    }

}
