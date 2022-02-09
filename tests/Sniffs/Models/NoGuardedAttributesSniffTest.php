<?php
declare(strict_types = 1);

namespace DealerInspireLaravel\Sniffs\Models;

use DealerInspireLaravelCodingStandard\Sniffs\Models\NoGuardedAttributesSniff;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHPUnit\Framework\TestCase;

class NoGuardedAttributesSniffTest extends TestCase
{
    /**
     * @var Runner
     */
    protected $codeSniffer;

    public function setUp(): void
    {
        $this->codeSniffer = new Runner();
        $this->codeSniffer->config = new Config(['-s']);
        $this->codeSniffer->init();

        $this->codeSniffer->ruleset->sniffs = [NoGuardedAttributesSniff::class => new NoGuardedAttributesSniff()];
        $this->codeSniffer->ruleset->populateTokenListeners();
    }

    /**
     * @var string $fileName           The file that will be checked
     * @var array  $expectedMessages   The expected error messages where the key is the message and the value is null (for easier comparison)
     * @dataProvider dataProvider
     */
    public function test(string $fileName, array $expectedMessages): void
    {
        $file = $this->getFileForPath(__DIR__ . '/data/' . $fileName);

        foreach ($file->getErrors() as $error) {
            $error = reset($error)[0];
            $errorMessage = $error['message'];
            $errorCode = explode('.', $error['source'])[3];

            $this->assertEquals($expectedMessages[$errorCode], $errorMessage);
        }

        $this->assertSame(count($expectedMessages), $file->getErrorCount());
    }

    public function dataProvider(): array
    {
        return [
            'Test class with no guarded attributes passes' =>
                [
                    'NoGuardedAttributesModel.php',
                    [],
                ],
            'Test class with no guarded attributes that has a $guarded variable passes' =>
                [
                    'NoGuardedAttributesWithGuardedVariableModel.php',
                    [],
                ],
            'Test class with guarded attributes fails' =>
                [
                    'GuardedAttributesModel.php',
                    [
                        'GuardedAttributes' => 'Uses $guarded attributes',
                    ],
                ],
        ];
    }

    /**
     * @param string $filePath Get a File object for the given path using the ruleset under test
     * @return LocalFile
     */
    protected function getFileForPath(string $filePath): LocalFile
    {
        $file = new LocalFile($filePath, $this->codeSniffer->ruleset, $this->codeSniffer->config);

        $file->process();
        return $file;
    }
}
