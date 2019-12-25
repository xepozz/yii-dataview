<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\DataView\Tests;

use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Yii\DataView\Columns\DataColumn;
use Yiisoft\Yii\DataView\GridView;

/**
 * @author Evgeniy Tkachenko <et.coder@gmail.com>
 * @group grid
 */
class GridViewTest extends BaseListViewTestCase
{
    /**
     * @return array
     */
    public function emptyDataProvider()
    {
        return [
            [null, '<tr><td colspan="0"><div class="empty">No results found.</div></td></tr>'],
            ['Empty', '<tr><td colspan="0"><div class="empty">Empty</div></td></tr>'],
            // https://github.com/yiisoft/yii2/issues/13352
            [false, '<tr><td colspan="0"><div class="empty"></div></td></tr>'],
        ];
    }

    /**
     * @dataProvider emptyDataProvider
     * @param mixed $emptyText
     * @param string $expectedText
     * @throws \Exception
     */
    public function testEmpty($emptyText, $expectedText)
    {
        $dataReader = $this->createDataReader([]);
        $html = GridView::widget()
            ->withDataReader($dataReader)
            ->emptyText($emptyText)
            ->showHeader(false)
            ->withTableOptions(
                [
                    'class' => false,
                ]
            )
            ->setOptions(
                [
                    'id' => 'grid',
                    'class' => false,
                ]
            )
            ->run();
        $html = preg_replace("/\r|\n/", '', $html);

        $expectedHtml = "<div id=\"grid\"><table><tbody>{$expectedText}</tbody></table></div>";

        $this->assertEquals($expectedHtml, $html);
    }

    public function testGuessColumns()
    {
        $this->markTestIncomplete('Depends on DataColumnTest');
        $row = ['id' => 1, 'name' => 'Name1', 'value' => 'Value1', 'description' => 'Description1'];

        $dataReader = $this->createDataReader([$row]);
        $grid = GridView::widget()
            ->withDataReader($dataReader);

        $grid->init();

        $columns = $grid->columns;
        $this->assertCount(count($row), $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::class, $column);
            $this->assertArrayHasKey($column->attribute, $row);
        }

        $row = array_merge($row, ['relation' => ['id' => 1, 'name' => 'RelationName']]);
        $row = array_merge($row, ['otherRelation' => (object)$row['relation']]);

        $dataReader = $this->createDataReader([]);
        $grid = GridView::widget()
            ->withDataReader($dataReader);

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
        $dataReader = $this->createDataReader([]);
        $widget = GridView::widget()
            ->withDataReader($dataReader)
            ->showFooter(true)
            ->setOptions(
                [
                    'id' => false,
                    'class' => false,
                ]
            );
        $html = $widget->run();
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertRegExp("/<\/tfoot><tbody>/", $html);

        $widget = (clone $widget)
            ->placeFooterAfterBody(true);

        $html = $widget->run();
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertRegExp("/<\/tbody><tfoot>/", $html);
    }

    private function createDataReader(array $models)
    {
        return new IterableDataReader($models);
    }
}
