<?php
/**
 * @link http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\dataview\tests\unit;

use yii\data\ArrayDataProvider;
use yii\dataview\columns\DataColumn;
use yii\dataview\GridView;
use yii\helpers\Yii;
use yii\web\View;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 * @group grid
 */
class GridViewTest extends \yii\tests\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * @return array
     */
    public function emptyDataProvider()
    {
        return [
            [null, 'No results found.'],
            ['Empty', 'Empty'],
            // https://github.com/yiisoft/yii2/issues/13352
            [false, ''],
        ];
    }

    /**
     * @dataProvider emptyDataProvider
     *
     * @param mixed  $emptyText
     * @param string $expectedText
     *
     * @throws \Exception
     */
    public function testEmpty($emptyText, $expectedText)
    {
        $html = GridView::widget([
            'id'           => 'grid',
            'dataProvider' => Yii::createObject([
                '__class'   => ArrayDataProvider::class,
                'allModels' => [],
            ]),
            'showHeader'   => false,
            'emptyText'    => $emptyText,
            'options'      => [],
            'tableOptions' => [],
            'view'         => Yii::createObject([
                '__class' => View::class,
            ]),
            'filterUrl' => '/',
        ]);
        $html = preg_replace("/\r|\n/", '', $html);

        if ($expectedText) {
            $emptyRowHtml = "<tr><td colspan=\"0\"><div class=\"empty\">{$expectedText}</div></td></tr>";
        } else {
            $emptyRowHtml = '';
        }
        $expectedHtml = "<div id=\"grid\"><table><tbody>{$emptyRowHtml}</tbody></table></div>";

        $this->assertEquals($expectedHtml, $html);
    }

    public function testGuessColumns()
    {
        $row = ['id' => 1, 'name' => 'Name1', 'value' => 'Value1', 'description' => 'Description1'];

        $grid = Yii::createObject([
            '__class'      => GridView::class,
            'dataProvider' => Yii::createObject([
                '__class'   => ArrayDataProvider::class,
                'allModels' => [
                    $row,
                ],
            ]),
        ]);

        $columns = $grid->columns;
        $this->assertCount(count($row), $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::class, $column);
            $this->assertArrayHasKey($column->attribute, $row);
        }

        $row = array_merge($row, ['relation' => ['id' => 1, 'name' => 'RelationName']]);
        $row = array_merge($row, ['otherRelation' => (object) $row['relation']]);

        $grid = Yii::createObject([
            '__class'      => GridView::class,
            'dataProvider' => Yii::createObject([
                '__class'   => ArrayDataProvider::class,
                'allModels' => [
                    $row,
                ],
            ]),
        ]);

        $columns = $grid->columns;
        $this->assertCount(count($row) - 2, $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::class, $column);
            $this->assertArrayHasKey($column->attribute, $row);
            $this->assertNotEquals('relation', $column->attribute);
            $this->assertNotEquals('otherRelation', $column->attribute);
        }
    }

    /**
     * @throws \Exception
     */
    public function testFooter()
    {
        $config = [
            'id'           => 'grid',
            'dataProvider' => Yii::createObject([
                '__class'   => ArrayDataProvider::class,
                'allModels' => [],
            ]),
            'showHeader'   => false,
            'showFooter'   => true,
            'options'      => [],
            'tableOptions' => [],
            'view'         => Yii::createObject([
                '__class' => View::class,
            ]),
            'filterUrl'    => '/',
        ];

        $html = GridView::widget($config);
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertTrue(preg_match("/<\/tfoot><tbody>/", $html) === 1);

        // Place footer after body
        $config['placeFooterAfterBody'] = true;

        $html = GridView::widget($config);
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertTrue(preg_match("/<\/tbody><tfoot>/", $html) === 1);
    }
}
