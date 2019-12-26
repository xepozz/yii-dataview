<?php

use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\I18n\MessageFormatterInterface;
use Yiisoft\View\View;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\Yii\DataView\BaseListView;
use Yiisoft\Yii\DataView\Columns\Column;
use Yiisoft\Yii\DataView\DetailView;

return [
    MessageFormatterInterface::class => static function () {
        return new class implements MessageFormatterInterface {
            /**
             * @inheritDoc
             */
            public function format(string $message, array $parameters, string $language): string
            {
                return $message;
            }
        };
    },
    ViewContextInterface::class => View::class,
    BaseListView::class => [
        '__class' => BaseListView::class,
        '__construct()' => [
            Reference::to(MessageFormatterInterface::class),
            Reference::to(ViewContextInterface::class),
        ],
    ],
    DetailView::class => [
        '__class' => DetailView::class,
        '__construct()' => [
            Reference::to(MessageFormatterInterface::class),
        ],
    ],
    Column::class => [
        '__class' => Column::class,
        '__construct()' => [
            Reference::to(MessageFormatterInterface::class),
        ],
    ],
];
