<?php

namespace Yiisoft\Yii\DataView\Tests;

use Yiisoft\Arrays\ArrayableInterface;
use Yiisoft\Arrays\ArrayableTrait;
use Yiisoft\Yii\DataView\DetailView;

/**
 * @group widgets
 */
class DetailViewTest extends TestCase
{
    public function testAttributeValue()
    {
        $model = new ModelMock();
        $model->id = 'id';

        $widget = PublicDetailView::widget()
            ->withModel($model)
            ->withTemplate('{label}:{value}')
            ->withAttributes(
                [
                    'id',
                    [
                        'attribute' => 'id',
                        'value' => 1,
                    ],
                    [
                        'attribute' => 'id',
                        'value' => '1',
                    ],
                    [
                        'attribute' => 'id',
                        'value' => $model->getDisplayedId(),
                    ],
                    [
                        'attribute' => 'id',
                        'value' => function (ModelMock $model) {
                            return $model->getDisplayedId();
                        },
                    ],
                ]
            );

        $this->assertEquals('Id:id', $widget->renderAttr($widget->attributes[0], 0));
        $this->assertEquals('Id:1', $widget->renderAttr($widget->attributes[1], 1));
        $this->assertEquals('Id:1', $widget->renderAttr($widget->attributes[2], 2));
        $this->assertEquals('Id:Displayed id', $widget->renderAttr($widget->attributes[3], 3));
        $this->assertEquals('Id:Displayed id', $widget->renderAttr($widget->attributes[4], 4));
        $this->assertEquals(2, $model->getDisplayedIdCallCount());
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/13243
     */
    public function testUnicodeAttributeNames()
    {
        $model = new UnicodeAttributesModelMock();
        $model->ИдентификаторТовара = 'A00001';
        $model->το_αναγνωριστικό_του = 'A00002';

        $widget = PublicDetailView::widget()
            ->withModel($model)
            ->withTemplate('{label}:{value}')
            ->withAttributes(
                [
                    'ИдентификаторТовара',
                    'το_αναγνωριστικό_του',
                ]
            );

        $this->assertEquals(
            'Идентификатор Товара:A00001',
            $widget->renderAttr($widget->attributes[0], 0)
        );
        $this->assertEquals(
            'Το Αναγνωριστικό Του:A00002',
            $widget->renderAttr($widget->attributes[1], 1)
        );
    }

    public function testAttributeVisible()
    {
        $model = new ModelMock();
        $model->id = 'id';

        $widget = PublicDetailView::widget()
            ->withModel($model)
            ->withTemplate('{label}:{value}')
            ->withAttributes(
                [
                    [
                        'attribute' => 'id',
                        'value' => $model->getDisplayedId(),
                    ],
                    [
                        'attribute' => 'id',
                        'value' => $model->getDisplayedId(),
                        'visible' => false,
                    ],
                    [
                        'attribute' => 'id',
                        'value' => $model->getDisplayedId(),
                        'visible' => true,
                    ],
                    [
                        'attribute' => 'id',
                        'value' => function (ModelMock $model) {
                            return $model->getDisplayedId();
                        },
                    ],
                    [
                        'attribute' => 'id',
                        'value' => function (ModelMock $model) {
                            return $model->getDisplayedId();
                        },
                        'visible' => false,
                    ],
                    [
                        'attribute' => 'id',
                        'value' => function (ModelMock $model) {
                            return $model->getDisplayedId();
                        },
                        'visible' => true,
                    ],
                ]
            );

        $this->assertEquals(
            [
                0 => [
                    'attribute' => 'id',
                    'format' => 'text',
                    'label' => 'Id',
                    'value' => 'Displayed id',
                ],
                2 => [
                    'attribute' => 'id',
                    'format' => 'text',
                    'label' => 'Id',
                    'value' => 'Displayed id',
                    'visible' => true,
                ],
                3 => [
                    'attribute' => 'id',
                    'format' => 'text',
                    'label' => 'Id',
                    'value' => 'Displayed id',
                ],
                5 => [
                    'attribute' => 'id',
                    'format' => 'text',
                    'label' => 'Id',
                    'value' => 'Displayed id',
                    'visible' => true,
                ],
            ],
            $widget->attributes
        );
        $this->assertEquals(5, $model->getDisplayedIdCallCount());
    }

    public function testRelationAttribute()
    {
        $model = new ModelMock();
        $model->id = 'model';
        $model->related = new ModelMock();
        $model->related->id = 'related';

        $widget = PublicDetailView::widget()
            ->withModel($model)
            ->withTemplate('{label}:{value}')
            ->withAttributes(
                [
                    'id',
                    'related.id',
                ]
            );

        $this->assertEquals('Id:model', $widget->renderAttr($widget->attributes[0], 0));
        $this->assertEquals(
            'Related Id:related',
            $widget->renderAttr($widget->attributes[1], 1)
        );

        // test null relation
        $model->related = null;

        $widget = PublicDetailView::widget()
            ->withModel($model)
            ->withTemplate('{label}:{value}')
            ->withAttributes(
                [
                    'id',
                    'related.id',
                ]
            );

        $this->assertEquals('Id:model', $widget->renderAttr($widget->attributes[0], 0));
        $this->markTestIncomplete('Needs to implement null-value');
        $this->assertEquals(
            'Related Id:<span class="not-set">(not set)</span>',
            $widget->renderAttr($widget->attributes[1], 1)
        );
    }

    public function testArrayableModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1,
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m arrayable',
            ],
        ];

        $model = new ArrayableInterfaceMock();
        $model->id = 1;
        $model->text = 'I`m arrayable';

        $widget = PublicDetailView::widget()
            ->withModel($model);
        $widget->init();

        $this->assertEquals($expectedValue, $widget->attributes);
    }

    public function testObjectModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1,
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an object',
            ],
        ];

        $model = new ModelMock();
        $model->id = 1;
        $model->text = 'I`m an object';

        $widget = PublicDetailView::widget()
            ->withModel($model);
        $widget->init();

        $this->assertEquals($expectedValue, $widget->attributes);
    }

    public function testArrayModel()
    {
        $expectedValue = [
            [
                'attribute' => 'id',
                'format' => 'text',
                'label' => 'Id',
                'value' => 1,
            ],
            [
                'attribute' => 'text',
                'format' => 'text',
                'label' => 'Text',
                'value' => 'I`m an array',
            ],
        ];

        $model = [
            'id' => 1,
            'text' => 'I`m an array',
        ];

        $widget = PublicDetailView::widget()
            ->withModel($model);
        $widget->init();

        $this->assertEquals($expectedValue, $widget->attributes);
    }

    public function testOptionsTags()
    {
        $expectedValue = '<tr><th tooltip="Tooltip">Text</th><td class="bg-red">I`m an array</td></tr>';

        $widget = PublicDetailView::widget()
            ->withModel(
                [
                    'text' => 'I`m an array',
                ]
            )
            ->withAttributes(
                [
                    [
                        'attribute' => 'text',
                        'label' => 'Text',
                        'contentOptions' => ['class' => 'bg-red'],
                        'captionOptions' => ['tooltip' => 'Tooltip'],
                    ],
                ]
            );

        foreach ($widget->attributes as $index => $attribute) {
            $a = $widget->renderAttr($attribute, $index);
            $this->assertEquals($expectedValue, $a);
        }
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15536
     */
    public function testShouldTriggerInitEvent()
    {
        $initTriggered = false;
        $model = new ModelMock();
        $model->id = 1;
        $model->text = 'I`m an object';

        $this->markTestIncomplete('Need to implement EventDispatcherListener');
        $widget = PublicDetailView::widget()
            ->withModel($model)
            ->on(
                'on widget.init',
                function () use (&$initTriggered) {
                    $initTriggered = true;
                },
                );

        $this->assertTrue($initTriggered);
    }
}

/**
 * Helper Class.
 */
class ArrayableInterfaceMock implements ArrayableInterface
{
    use ArrayableTrait;

    public $id;

    public $text;
}

/**
 * Helper Class.
 */
class ModelMock
{
    public $id;
    public $text;

    private $_related;
    private $_displayedIdCallCount = 0;

    public function getRelated()
    {
        return $this->_related;
    }

    public function setRelated($related)
    {
        $this->_related = $related;
    }

    public function getDisplayedId()
    {
        $this->_displayedIdCallCount++;

        return "Displayed $this->id";
    }

    public function getDisplayedIdCallCount()
    {
        return $this->_displayedIdCallCount;
    }
}

/**
 * Used for testing attributes containing non-English characters.
 */
class UnicodeAttributesModelMock
{
    /**
     * Product's ID (Russian).
     *
     * @var mixed
     */
    public $ИдентификаторТовара;
    /**
     * ID (Greek).
     *
     * @var mixed
     */
    public $το_αναγνωριστικό_του;
}

class PublicDetailView extends DetailView
{
    public function renderAttr($attribute, $index): string
    {
        return $this->renderAttribute($attribute, $index);
    }
}
