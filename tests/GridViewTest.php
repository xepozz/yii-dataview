<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\DataView\Tests;

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\View\View;
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
            [null, 'No results found.'],
            ['Empty', 'Empty'],
            // https://github.com/yiisoft/yii2/issues/13352
            [false, ''],
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
        $html = GridView::widget(
            [
                'id' => 'grid',
                'dataProvider' => $dataReader,
                'showHeader' => false,
                'emptyText' => $emptyText,
                'options' => [],
                'tableOptions' => [],
                'view' => $this->getView(),
                'filterUrl' => '/',
            ]
        );
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

        $dataReader = $this->createDataReader([$row]);
        $grid = $this->createGridView($dataReader);

        $columns = $grid->columns;
        $this->assertCount(count($row), $columns);

        foreach ($columns as $index => $column) {
            $this->assertInstanceOf(DataColumn::class, $column);
            $this->assertArrayHasKey($column->attribute, $row);
        }

        $row = array_merge($row, ['relation' => ['id' => 1, 'name' => 'RelationName']]);
        $row = array_merge($row, ['otherRelation' => (object)$row['relation']]);

        $dataReader = $this->createDataReader([]);
        $grid = $this->createGridView($dataReader);

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
            'id' => 'grid',
            'dataProvider' => $this->createDataReader([]),
            'showHeader' => false,
            'showFooter' => true,
            'options' => [],
            'tableOptions' => [],
            'view' => $this->getView(),
            'filterUrl' => '/',
        ];

        $dataReader = $this->createDataReader([]);
        $gridView = $this->createGridView($dataReader);
        $gridView->setOptions($config['options']);
        $html = $gridView->run();
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertTrue(preg_match("/<\/tfoot><tbody>/", $html) === 1);

        // Place footer after body
        $config['placeFooterAfterBody'] = true;

        $html = GridView::widget($config);
        $html = preg_replace("/\r|\n/", '', $html);

        $this->assertTrue(preg_match("/<\/tbody><tfoot>/", $html) === 1);
    }

    private function createDataReader(array $models)
    {
        return new IterableDataReader($models);
    }

    private function getView()
    {
        return $this->container->get(View::class);
    }

    private function createGridView(DataReaderInterface $dataReader)
    {
        $gridView = $this->container->get(GridView::class);
        $gridView->dataReader = $dataReader;

        return $gridView;
    }
}
