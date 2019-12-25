<?php

namespace Yiisoft\Yii\DataView\Tests;

use hiqdev\composer\config\Builder;
use Yiisoft\Di\Container;
use Yiisoft\Tests\TestCase;

abstract class BaseListViewTestCase extends TestCase
{
    /**
     * @throws \Yiisoft\Factory\Exceptions\CircularReferenceException
     * @throws \Yiisoft\Factory\Exceptions\InvalidConfigException
     * @throws \Yiisoft\Factory\Exceptions\NotFoundException
     * @throws \Yiisoft\Factory\Exceptions\NotInstantiableException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container(require Builder::path('tests'));
        $container->get(\Yiisoft\Yii\DataView\ListView::class);
        $container->get(\Yiisoft\Yii\DataView\Columns\ActionColumn::class);
    }
}
