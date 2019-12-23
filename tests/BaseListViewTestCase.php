<?php

namespace Yiisoft\Yii\DataView\Tests;

use hiqdev\composer\config\Builder;
use Yiisoft\Di\Container;
use Yiisoft\Tests\TestCase;

abstract class BaseListViewTestCase extends TestCase
{
    protected $baseListView;

    protected function setUp(): void
    {
        parent::setUp();

        $config = require Builder::path('tests');

        $this->container = new Container($config);
        $this->listView = $this->container->get(\Yiisoft\Yii\DataView\ListView::class);
    }
}
