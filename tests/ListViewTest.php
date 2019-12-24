<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiisoft\Yii\DataView\Tests;

use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Yii\DataView\ListView;

/**
 * @group widgets
 */
class ListViewTest extends BaseListViewTestCase
{
    public function testEmptyListShown()
    {
        $dataReader = $this->createDataReader([]);
        $listView = $this->getListView($dataReader, new OffsetPaginator($dataReader));
        $listView->setEmptyText('Nothing at all');
        $out = $listView->run();
        $this->assertEquals('<div id="w0" class="list-view"><div class="empty">Nothing at all</div></div>', $out);
    }

    public function testEmpty()
    {
        $listView = $this->getListView($this->createDataReader([]), false);
        $listView->disableEmptyText();
        $out = $listView->run();

        $this->assertEquals('<div id="w0" class="list-view"></div>', $out);
    }

    public function testEmptyListNotShown()
    {
        $listView = $this->getListView($this->createDataReader([]), false);
        $out = $listView->run();

        $this->assertEquals(
            <<<'HTML'
<div id="w0" class="list-view"><div class="empty">No results found.</div></div>
HTML
            ,
            $out
        );
    }

    public function testSimplyListView()
    {
        $dataReader = $this->createDataReader([0, 1, 2]);
        $listView = $this->getListView($dataReader, false);

        $out = $listView->run();

        $this->assertEquals(
            <<<'HTML'
<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>
HTML
            ,
            $out
        );
    }

    public function testWidgetOptions()
    {
        $dataReader = $this->createDataReader([0, 1, 2]);
        $listView = $this->getListView($dataReader, false);
        $listView->separator = '';
        $listView->setOptions(['class' => 'test-passed']);
        $out = $listView->run();

        $this->assertEquals(
            <<<'HTML'
<div id="w0" class="test-passed"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<div data-key="0">0</div><div data-key="1">1</div><div data-key="2">2</div>
</div>
HTML
            ,
            $out
        );
    }

    public function itemViewOptions()
    {
        return [
            [
                null,
                '<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>',
            ],
            [
                function ($model, $key, $index, $widget) {
                    return "Item #{$index}: {$model['login']} - Widget: " . get_class($widget);
                },
                '<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<div data-key="0">Item #0: silverfire - Widget: Yiisoft\Yii\DataView\ListView</div>
<div data-key="1">Item #1: samdark - Widget: Yiisoft\Yii\DataView\ListView</div>
<div data-key="2">Item #2: cebe - Widget: Yiisoft\Yii\DataView\ListView</div>
</div>',
            ],
            [
                '@yii/tests/data/views/widgets/ListView/item',
                '<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<div data-key="0">Item #0: silverfire - Widget: Yiisoft\Yii\DataView\ListView</div>
<div data-key="1">Item #1: samdark - Widget: Yiisoft\Yii\DataView\ListView</div>
<div data-key="2">Item #2: cebe - Widget: Yiisoft\Yii\DataView\ListView</div>
</div>',
            ],
        ];
    }

    /**
     * @dataProvider itemViewOptions
     * @param mixed $itemView
     * @param string $expected
     */
    public function testItemViewOptions($itemView, $expected)
    {
        $dataReader = $this->createDataReader([0, 1, 2]);
        $listView = $this->getListView($dataReader, false);
        $listView->itemView = $itemView;
        $out = $listView->run();
        $this->assertEquals($expected, $out);
    }

    public function itemOptions()
    {
        return [
            [
                null,
                '<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<div data-key="0">0</div>
<div data-key="1">1</div>
<div data-key="2">2</div>
</div>',
            ],
            [
                function ($model, $key, $index, $widget) {
                    return [
                        'tag' => 'span',
                        'data' => [
                            'test' => 'passed',
                            'key' => $key,
                            'index' => $index,
                            'id' => $model['id'],
                        ],
                    ];
                },
                '<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<span data-test="passed" data-key="0" data-index="0" data-id="1" data-key="0">0</span>
<span data-test="passed" data-key="1" data-index="1" data-id="2" data-key="1">1</span>
<span data-test="passed" data-key="2" data-index="2" data-id="3" data-key="2">2</span>
</div>',
            ],
        ];
    }

    /**
     * @dataProvider itemOptions
     * @param mixed $itemOptions
     * @param string $expected
     */
    public function testItemOptions($itemOptions, $expected)
    {
        $dataReader = $this->createDataReader(
            [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ]
        );
        $listView = $this->getListView($dataReader, false);
        $listView->itemOptions = $itemOptions;
        $out = $listView->run();

        $this->assertEquals($expected, $out);
    }

    public function testBeforeAndAfterItem()
    {
        $before = function ($model, $key, $index, $widget) {
            $widget = get_class($widget);

            return "<!-- before: {$model['id']}, key: $key, index: $index, widget: $widget -->";
        };
        $after = function ($model, $key, $index, $widget) {
            if ($model['id'] === 1) {
                return;
            }
            $widget = get_class($widget);

            return "<!-- after: {$model['id']}, key: $key, index: $index, widget: $widget -->";
        };

        $dataReader = $this->createDataReader(
            [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
            ]
        );
        $listView = $this->getListView($dataReader, false);
        $listView->beforeItem = $before;
        $listView->afterItem = $after;

        $out = $listView->run();

        $this->assertEquals(
            <<<HTML
<div id="w0" class="list-view"><div class="summary">Total <b>{count, number}</b> {count, plural, one{item} other{items}}.</div>
<!-- before: 1, key: 0, index: 0, widget: Yiisoft\Yii\DataView\ListView -->
<div data-key="0">0</div>
<!-- before: 2, key: 1, index: 1, widget: Yiisoft\Yii\DataView\ListView -->
<div data-key="1">1</div>
<!-- after: 2, key: 1, index: 1, widget: Yiisoft\Yii\DataView\ListView -->
<!-- before: 3, key: 2, index: 2, widget: Yiisoft\Yii\DataView\ListView -->
<div data-key="2">2</div>
<!-- after: 3, key: 2, index: 2, widget: Yiisoft\Yii\DataView\ListView -->
</div>
HTML
            ,
            $out
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/pull/14596
     */
    public function testShouldTriggerInitEvent()
    {
        $this->markTestIncomplete();
        $dataReader = $this->createDataReader([0, 1, 2]);
        $initTriggered = false;

        $this->getListView(
            [
                'dataReader' => $dataReader,
                'on widget.init' => function () use (&$initTriggered) {
                    $initTriggered = true;
                },
            ]
        );
        $this->assertTrue($initTriggered);
    }

    private function createDataReader(array $models)
    {
        return new IterableDataReader($models);
    }

    /**
     * @param $dataReader
     * @param $paginator
     * @return ListView
     */
    private function getListView($dataReader, $paginator)
    {
        $listView = $this->container->get(ListView::class);
        $listView->setOptions(['id' => 'w0', 'class' => 'list-view']);
        $listView->dataReader = $dataReader;
        $listView->paginator = $paginator;

        return $listView;
    }
}
