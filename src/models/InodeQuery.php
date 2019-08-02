<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use app\helpers\ArrayHelper;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use paulzi\adjacencyList\AdjacencyListQueryTrait;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class InodeQuery
 * @package eseperio\filescatalog\models
 * @method $this roots() gets the roots nodes
 */
class InodeQuery extends ActiveQuery
{
    use ModuleAwareTrait;
    use AdjacencyListQueryTrait;

    /**
     * Filters only root files
     * @return InodeQuery
     */
    public function onlyRoot()
    {
        return $this->roots()->limit(1);
    }

    public function excludeVersions()
    {
        return $this->andWhere(['!=', 'type', InodeTypes::TYPE_VERSION]);
    }

    /**
     * Filter results to only those which user have permission to read
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function onlyReadable()
    {
        return $this->onlyAllowed([7, 6, 5, 4]);
    }

    /**
     * Joins results with acl table, so it can be filtered by crud_mask values
     * @throws \yii\base\InvalidConfigException
     * @internal
     */
    private function onlyAllowed($crudMaskValues): InodeQuery
    {
        if (!$this->module->isAdmin()) {

            $authManager = Yii::$app->authManager;
            $userId = $this->module->getUserId();
            $userRoles = ArrayHelper::getColumn($authManager->getRolesByUser($userId), 'name');

            $allRoles = [];
            foreach ($userRoles as $role) {
                $allRoles = $allRoles + ArrayHelper::getColumn($authManager->getChildRoles($role), 'name');
            }
            $allRoles[] = AccessControl::WILDCARD_ROLE;

            $this->joinWith('accessControlList acl');

            $condition = ['or',
                ['acl.role' => $allRoles],
                ['acl.user_id' => $userId],
            ];

            if (!$this->module->getUser()->getIsGuest())
                $condition[] = ['acl.role' => AccessControl::LOGGED_IN_USERS];

            $this->andWhere($condition);
            $this->andWhere(['acl.crud_mask' => $crudMaskValues]);
            $this->groupBy('id');

        }

        return $this;
    }

    /**
     * Filter results to only those which user have permission to write
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function onlyWriteable()
    {
        return $this->onlyAllowed([7, 6, 3, 2]);
    }

    /**
     * Filter results to only those which user have permission to delete
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function onlyDeletable()
    {
        return $this->onlyAllowed([1, 3, 5, 7]);
    }

    /**
     * @param $uuid
     * @return InodeQuery
     */
    public function uuid($uuid)
    {
        return $this->andWhere(['uuid' => $uuid]);
    }

    public function orderByExtension($order = SORT_DESC)
    {
        return $this->addOrderBy([
            'extension' => $order
        ]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderByType($order = SORT_DESC)
    {
        return $this->addOrderBy([
            'type' => $order,
        ]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderAZ($order = SORT_ASC)
    {
        return $this->addOrderBy([
            'name' => $order
        ]);
    }

    /**
     * @return InodeQuery
     */
    public function onlySymlinks()
    {
        return $this->andWhere([
            'type' => InodeTypes::TYPE_SYMLINK
        ]);
    }

    public function byName(string $name, $like = false)
    {
        if ($like)
            return $this->andWhere(['like', '%name%' => $name]);

        return $this->andWhere(['name' => $name]);
    }

    /**
     * @return InodeQuery
     */
    public function onlyFiles()
    {
        return $this->andWhere([
            'type' => InodeTypes::TYPE_FILE
        ]);
    }

    /**
     * @return InodeQuery
     */
    public function onlyDirs()
    {
        return $this->andWhere([
            'type' => InodeTypes::TYPE_DIR
        ]);
    }
}
