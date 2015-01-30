<?php

namespace Intersvyaz\ExtendedGrid\Tests\Unit;

use Intersvyaz\ExtendedGrid\ExtGridView;
use Intersvyaz\ExtendedGrid\Tests\Fakes\FakeController;

class ExtGridViewTest extends \PHPUnit_Framework_TestCase
{
    public function settingsProvider()
    {
        $data = [
            ['id' => 1, 'column1' => 'v1c1', 'column2' => 'v1c2', 'column3' => 'v1c3', 'column4' => 'v1c4'],
            ['id' => 2, 'column1' => 'v2c1', 'column2' => 'v2c2', 'column3' => 'v2c3', 'column4' => 'v2c4'],
            ['id' => 3, 'column1' => 'v3c1', 'column2' => 'v3c2', 'column3' => 'v3c3', 'column4' => 'v3c4'],
        ];

        $defaultSettings = ['id' => 't'];

        return [
            // пустой дата провайдер
            [[], $defaultSettings, __DIR__ . '/../data/empty_table.html'],
            // колонки беруться из датапровайдера
            [$data, $defaultSettings, __DIR__ . '/../data/default_behavior.html'],
            // прописываем колонки сами, именами из датапровайдера
            [
                $data,
                array_merge($defaultSettings, ['columns' => ['id', 'column1', 'column2', 'column3', 'column4']]),
                __DIR__ . '/../data/default_behavior.html'
            ],
            // прописываем колонки с кастомными заголовками
            [
                $data,
                array_merge($defaultSettings, [
                    'columns' => [
                        ['name' => 'id'],
                        ['name' => 'column1', 'header' => 'renamed column'],
                        ['name' => 'column2'],
                        ['name' => 'column3'],
                        ['name' => 'column4', 'visible' => false],
                    ]
                ]),
                __DIR__ . '/../data/renamed_column.html'
            ],
            // прописываем колонки, с групировкой
            [
                $data,
                array_merge($defaultSettings, [
                    'columns' => [
                        'id',
                        [
                            'header' => 'group1',
                            'columns' => ['column1', 'column2'],
                        ],
                        [
                            'header' => 'group2',
                            'columns' => ['column3', 'column4']
                        ]
                    ]
                ]),
                __DIR__ . '/../data/grouping_columns.html'
            ],
        ];
    }

    /**
     * @param $data
     * @param $properties
     * @param $resultFile
     * @dataProvider settingsProvider
     */
    public function testRenderWidget($data, $properties, $resultFile)
    {
        $this->assertEquals(
            trim(file_get_contents($resultFile)),
            $this->getWidgetContent($data, $properties)
        );
    }

    private function getWidgetContent(array $data, array $properties)
    {
        $controller = new FakeController('fake');
        $dataProvider = new \CArrayDataProvider($data);
        $properties = array_merge($properties, ['dataProvider' => $dataProvider]);

        return $controller->widget(ExtGridView::class, $properties, true);

    }
}
