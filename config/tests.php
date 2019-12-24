<?php

use Yiisoft\Factory\Definitions\Reference;
use Yiisoft\I18n\MessageFormatterInterface;
use Yiisoft\View\View;
use Yiisoft\View\ViewContextInterface;
use Yiisoft\Yii\DataView\ListView;

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
    ListView::class => [
        '__class' => ListView::class,
        '__construct()' => [
            Reference::to(MessageFormatterInterface::class),
            Reference::to(ViewContextInterface::class),
        ],
    ],
];
