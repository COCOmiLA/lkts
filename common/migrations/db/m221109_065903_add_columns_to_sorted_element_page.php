<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\modules\student\interfaces\RoutableComponentInterface;
use common\modules\student\Module;
use yii\db\Query;
use yii\helpers\Console;




class m221109_065903_add_columns_to_sorted_element_page extends MigrationWithDefaultOptions
{
    private const TN = '{{%sorted_element_page}}';
    private const LINK_TN = '{{%student_side_links}}';

    


    public function up()
    {
        
        if (!$this->db->getTableSchema(self::TN)->getColumn('class')) {
            $this->addColumn(self::TN, 'class', $this->string()->defaultValue(null));
        }
        if (!$this->db->getTableSchema(self::TN)->getColumn('linkId')) {
            $this->addColumn(self::TN, 'linkId', $this->integer()->defaultValue(null));
        }

        $this->db->schema->refresh();
        $this->convertUrlsToClass();

        $this->dropColumn(self::TN, 'url');
        $this->dropColumn(self::TN, 'description');

        $this->db->schema->refresh();
    }

    


    private function convertUrlsToClass(): void
    {
        echo Console::ansiFormat(
            "\nКонвертирование виджетов главной страницы\n",
            [Console::FG_BLUE]
        );

        
        $module = Yii::$app->getModule('student');
        $components = $module->getComponents();
        foreach ($components as $name => $component) {
            if (!($module->$name instanceof RoutableComponentInterface)) {
                continue;
            }
            $url = $module->$name->getBaseRoute();
            $this->update(
                self::TN,
                ['class' => $component['class']],
                ['url' => $url]
            );
        }

        echo Console::ansiFormat(
            "\nКонвертирование ссылок главной страницы\n",
            [Console::FG_BLUE]
        );

        $links = (new Query())->from(self::LINK_TN)->all();
        $linkClass = 'common\models\settings\StudentSideLinks';
        foreach ($links as $link) {
            $this->update(
                self::TN,
                [
                    'class' => $linkClass,
                    'linkId' => $link['id'],
                ],
                ['url' => $link['url']]
            );
        }
        echo "\n";
    }

    


    public function down()
    {
        $this->addColumn(self::TN, 'url', $this->string(1024)->defaultValue(null));
        $this->addColumn(self::TN, 'description', $this->string(1024)->defaultValue(null));

        $this->db->schema->refresh();
        $this->convertClassToUrls();

        $this->dropColumn(self::TN, 'class');
        $this->dropColumn(self::TN, 'linkId');

        $this->db->schema->refresh();
    }

    


    private function convertClassToUrls(): void
    {
        echo Console::ansiFormat(
            "\nКонвертирование виджетов главной страницы\n",
            [Console::FG_CYAN]
        );

        
        $module = Yii::$app->getModule('student');
        $components = $module->getComponents();
        foreach ($components as $name => $component) {
            $this->update(
                self::TN,
                [
                    'url' => $module->$name->getBaseRoute(),
                    'description' => $module->$name->getComponentName(),
                ],
                ['class' => $component['class']]
            );
        }

        echo Console::ansiFormat(
            "\nКонвертирование ссылок главной страницы\n",
            [Console::FG_CYAN]
        );

        $links = (new Query())->from(self::LINK_TN)->all();
        foreach ($links as $link) {
            $this->update(
                self::TN,
                [
                    'url' => $link['url'],
                    'description' => $link['description'],
                ],
                ['linkId' => $link['id']]
            );
        }
        echo "\n";
    }
}
