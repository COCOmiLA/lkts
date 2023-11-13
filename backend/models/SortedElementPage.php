<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use common\models\settings\StudentSideLinks;
use common\models\User;
use common\modules\student\interfaces\RoutableComponentInterface;
use Throwable;
use UnexpectedValueException;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

















class SortedElementPage extends ActiveRecord
{
    private const LEFT_PLACE = 'left';
    private const RIGHT_PLACE = 'right';

    const TYPE_NEW = 'new_element';
    const TYPE_LEFT = 'left_element';
    const TYPE_RIGHT = 'right_element';
    const TYPE_REMOVED = 'removed_element';

    public $sortablePageElements;

    


    public static function tableName()
    {
        return '{{%sorted_element_page}}';
    }

    


    public function rules()
    {
        return [
            [
                [
                    'linkId',
                    'number',
                    'updated_at',
                    'created_at',
                ],
                'integer'
            ],
            [
                'is_removed',
                'boolean'
            ],
            [
                'place',
                'string',
                'max' => 6
            ],
            [
                'class',
                'string',
                'max' => 255
            ],
            [
                'sortablePageElements',
                'string',
            ],
            [
                'role',
                'string',
                'max' => 512
            ],
        ];
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role' => 'Роль',
            'url' => 'Ссылка',
            'number' => 'Номер',
            'place' => 'Сторона',
            'description' => 'Описание',
            'is_removed' => 'Скрытый элемент',
        ];
    }

    


    public function getUrl(): ?string
    {
        return $this->getElementValueByValueName('baseRoute');
    }

    


    public function getDescription(): ?string
    {
        return $this->getElementValueByValueName('componentName');
    }

    




    private function getElementValueByValueName(string $valueName): ?string
    {
        if (!$this->class) {
            return '';
        }

        $class = $this->class;
        $class = new $class();

        if ($class instanceof StudentSideLinks) {
            $link = StudentSideLinks::findOne(['id' => $this->linkId]);
            return ArrayHelper::getValue($link, $valueName, '');
        }

        if ($class instanceof RoutableComponentInterface) {
            return ArrayHelper::getValue($class, $valueName, '');
        }

        Yii::error(
            "Ошибка полевения значения «{$valueName}» элемента главной страницы:" .
                PHP_EOL .
                print_r([
                    'id' => $this->id,
                    'class' => $this->class,
                ], true),
            'SortedElementPage.getElementValueByValueName'
        );
        throw new UnexpectedValueException('Неизвестный класс элемента главной страницы ЛК преподавателя\студента');
    }

    





    public function load($data, $role = ''): bool
    {
        $loadResult = parent::load($data);

        if (empty($role)) {
            return $loadResult;
        }

        foreach (json_decode(base64_decode($this->sortablePageElements), true) as $type => $pageElements) {
            if (empty($pageElements)) {
                continue;
            }
            foreach ($pageElements as $key => $elementId) {
                if (!SortedElementPage::setElementPosition((int) $elementId, $type, (int) ($key + 1))) {
                    return false;
                }
            }
        }
        return true;
    }

    






    private static function setElementPosition($elementId, $type, $number): bool
    {
        $element = SortedElementPage::findOne(['id' => $elementId]);
        if (isset($elementId)) {
            $place = SortedElementPage::LEFT_PLACE;
            $isRemoved = false;
            switch ($type) {
                case SortedElementPage::TYPE_RIGHT:
                    $place = SortedElementPage::RIGHT_PLACE;
                    break;

                case SortedElementPage::TYPE_REMOVED:
                    $isRemoved = true;
                    break;

                case SortedElementPage::TYPE_NEW:
                    $number = 0;
                    break;
            }
            $element->place = $place;
            $element->number = $number;
            $element->is_removed = $isRemoved;

            if (!$element->save()) {
                throw new RecordNotValid($element);
            }

            return true;
        } else {
            return false;
        }
    }

    





    private static function enumerationAllRoles(): bool
    {
        $allUsersRole = User::getAllStudentSideRole();
        if (!empty($allUsersRole)) {
            foreach (array_keys($allUsersRole) as $role) {
                $result = SortedElementPage::updateElements($role);
                if (!$result) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    







    public static function updateElements(string $role = ''): bool
    {
        if (empty($role)) {
            return SortedElementPage::enumerationAllRoles();
        }

        $routes = [];
        $components = Yii::$app->getModule('student')->getComponentsFilteredByRole($role);
        foreach ($components as $className) {
            $routes[] = json_encode([
                'id' => null,
                'className' => $className,
            ]);
        }

        $links = StudentSideLinks::find()->all();
        if (!empty($links)) {
            $className = StudentSideLinks::class;
            foreach ($links as $link) {
                $routes[] = json_encode([
                    'id' => $link->id,
                    'className' => $className,
                ]);
            }
        }

        $pageElements = SortedElementPage::getAllElementsAsAssociativeArrayByRole($role);

        $newElements = array_diff($routes, $pageElements);
        $deleteElements = array_diff($pageElements, $routes);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!empty($newElements)) {
                SortedElementPage::addNewPageElements($newElements, $role);
            }
            if (!empty($deleteElements)) {
                SortedElementPage::deletePageElements($deleteElements, $role);
            }

            $transaction->commit();
        } catch (Throwable $th) {
            Yii::error(
                "Ошибка при обновления списков элементов для роли «{$role}», по причине: {$th->getMessage()}",
                'SortedElementPage.updateElements'
            );

            $transaction->rollBack();
            throw $th;
        }

        return true;
    }

    




    private static function getAllElementsAsAssociativeArrayByRole(string $role): array
    {
        $pageElements = [];
        $pageElementsQuery = SortedElementPage::findAll(['role' => $role]);
        if (!empty($pageElementsQuery)) {
            foreach ($pageElementsQuery as $pageElement) {
                $pageElements[] = json_encode([
                    'id' => $pageElement->linkId,
                    'className' => $pageElement->class,
                ]);
            }
        }

        return $pageElements;
    }

    





    private static function addNewPageElements(array $newElementsList, string $role): void
    {
        foreach ($newElementsList as $element) {
            [
                'id' => $linkId,
                'className' => $className,
            ] = json_decode($element, true);

            $newPageElement = new SortedElementPage();
            $newPageElement->role = $role;
            $newPageElement->class = $className;
            $newPageElement->linkId = $linkId;
            if (!$newPageElement->save()) {
                throw new RecordNotValid($newPageElement);
            }
        }
    }

    





    private static function deletePageElements(array $deleteElementsList, string $role): void
    {
        foreach ($deleteElementsList as $element) {
            [
                'id' => $_,
                'className' => $class,
            ] = json_decode($element, true);

            $oldPageElement = SortedElementPage::find()
                ->where([
                    'class' => $class,
                    'role' => $role,
                ])
                ->one();
            if (isset($oldPageElement)) {
                $oldPageElement->delete();
            }
        }
    }

    






    private static function getQueryRequest(string $role, string $type, int $sortDirection = SORT_ASC): array
    {
        $query = SortedElementPage::find()
            ->where(['role' => $role]);
        switch ($type) {
            case SortedElementPage::TYPE_NEW:
                $query = $query
                    ->andWhere(['is_removed' => false])
                    ->andWhere(['in', 'number', [0, null]]);
                break;

            case SortedElementPage::TYPE_LEFT:
                $query = $query->andWhere([
                    'is_removed' => false,
                    'place' => SortedElementPage::LEFT_PLACE,
                ])
                    ->andWhere(['>', 'number', 0]);
                break;

            case SortedElementPage::TYPE_RIGHT:
                $query = $query->andWhere([
                    'is_removed' => false,
                    'place' => SortedElementPage::RIGHT_PLACE,
                ])
                    ->andWhere(['>', 'number', 0]);
                break;

            case SortedElementPage::TYPE_REMOVED:
                $query = $query->andWhere(['is_removed' => true]);
                break;
        }
        return $query
            ->orderBy(['number' => $sortDirection, 'class' => $sortDirection])
            ->all();
    }

    






    public function buildItemsArray(string $role, string $type, int $sortDirection = SORT_ASC)
    {
        $items = [];
        if (empty($role)) {
            return $items;
        }

        $query = SortedElementPage::getQueryRequest($role, $type, $sortDirection);

        if (!empty($query)) {
            $items = array_map(
                function (SortedElementPage $element) {
                    return [
                        'content' => $element->description,
                        'options' => ['data-element_id' => $element->id]
                    ];
                },
                $query
            );
        }

        return $items;
    }

    





    public static function getAllSortedRoutes($role = '', $sortDirection = SORT_ASC)
    {
        $result = [];
        $elementTypes = [
            'newRoutes' => SortedElementPage::TYPE_NEW,
            'leftRoutes' => SortedElementPage::TYPE_LEFT,
            'rightRoutes' => SortedElementPage::TYPE_RIGHT,
        ];
        foreach ($elementTypes as $elementSide => $elementType) {
            $raw = SortedElementPage::getQueryRequest($role, $elementType, $sortDirection);
            $result[$elementSide] = ArrayHelper::map($raw, 'id', function (SortedElementPage $element) {
                return [
                    'url' => $element->url,
                    'description' => $element->description
                ];
            });
        }

        return $result;
    }

    




    public static function checkIfNeedUpdate(string $role): bool
    {
        return !SortedElementPage::find()
            ->andWhere(['role' => $role])
            ->exists();
    }
}
