<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\FormDataParser;
use LogicException;
use PHPUnit\Framework\TestCase;

class FormDataParserTest extends TestCase
{
    private FormDataParser $formDataParser;

    public function setUp(): void
    {
        $this->formDataParser = new FormDataParser();
    }

    /**
     * @dataProvider dataProviderForTestParse
     */
    public function testParse(array $expectedResult, string $filename)
    {
        $formDataString = file_get_contents($this->getResourcePath($filename));
        if ($formDataString === false) {
            throw new LogicException('Cannot read the file ' . $filename);
        }

        $result = $this->formDataParser->parse($formDataString);

        $this->assertSame($expectedResult, $result);
    }

    public function dataProviderForTestParse(): array
    {
        return [
            'Simple text' => [
                [
                    'id' => '1',
                    'data' => 'simple text',
                ],
                'form_data_simple_text.txt',
            ],
            'Quotes' => [
                [
                    'id' => '1',
                    'data' => 'quotes "ohai" and other quotes \'ohai\'',
                ],
                'form_data_quotes.txt',
            ],
            "Quotes and newline characters" => [
                [
                    'id' => '1',
                    'data' => 'quotes "ohai" and other quotes \'ohai\' and \r\n special symbols',
                ],
                'form_data_quotes_r_n.txt',
            ],
        ];
    }

    private function getResourcePath(string $filename): string
    {
        return join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Resources', $filename]);
    }
}
