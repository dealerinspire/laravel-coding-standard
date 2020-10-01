<?php
declare(strict_types = 1);

namespace DealerInspireLaravel\Sniffs\Providers;

use DealerInspireLaravelCodingStandard\Sniffs\Providers\DeferredProvidersSniff;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHPUnit\Framework\TestCase;

class DeferredProvidersSniffTest extends TestCase
{
    /**
     * @var Runner
     */
    protected $codeSniffer;

    public function setUp()
    {
        $this->codeSniffer = new Runner();
        $this->codeSniffer->config = new Config(['-s']);
        $this->codeSniffer->init();

        $this->codeSniffer->ruleset->sniffs = [DeferredProvidersSniff::class => new DeferredProvidersSniff()];
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
            $errorMessage = reset($error)[0]['message'];
            $this->assertTrue(array_key_exists($errorMessage, $expectedMessages));
        }

        $this->assertSame(count($expectedMessages), $file->getErrorCount());
    }

    public function dataProvider(): array
    {
        return [
            'Test deferred not set defaults to false and no provides match passes' =>
                [
                    'DeferredNotSetAndProvidesMissingServiceProvider.php',
                    []
                ],
            'Test deferred class property references and callback passes' =>
                [
                    'DeferredWithProvidesBothClassPropertyAndCallbackDefinitionServiceProvider.php',
                    [],
                ],
            'Test deferred class property references and short definition passes' =>
                [
                    'DeferredWithProvidesBothClassPropertyAndShortDefinitionServiceProvider.php',
                    [],
                ],
            'Test deferred string references and callback passes' =>
                [
                    'DeferredWithProvidesBothStringAndCallbackDefinitionServiceProvider.php',
                    [],
                ],
            'Test deferred string references and short definition passes' =>
                [
                    'DeferredWithProvidesBothStringAndShortDefinitionServiceProvider.php',
                    [],
                ],
            'Test not deferred and no provides match passes' =>
                [
                    'NotDeferredServiceProvider.php',
                    [],
                ],
            'Test $defers fails if provider extends ServiceProvider but class name does not end with ServiceProvider' =>
                [
                    'DeferredWithDefersWithoutServiceProviderClassName.php',
                    [
                        'Found unbound class in provides "AnotherServiceContract::class"' => null,
                        'Found bound class not in provides "SomeServiceContract::class"' => null,
                    ]
                ],
            'Test DeferrableProvider fails if provider extends ServiceProvider but class name does not end with ServiceProvider' =>
                [
                    'DeferredWithDefersWithoutServiceProviderClassName.php',
                    [
                        'Found unbound class in provides "AnotherServiceContract::class"' => null,
                        'Found bound class not in provides "SomeServiceContract::class"' => null,
                    ]
                ],
            'Test deferred with class property define and string provides fails' =>
                [
                    'DeferredWithClassPropertyVsStringServiceProvider.php',
                    [
                        'Found unbound class in provides "App\Contracts\Services\SomeOtherNameSpace\SomeOtherServiceContract"' => null,
                        'Found bound class not in provides "SomeOtherServiceContract::class"' => null,
                    ],
                ],
            'Test deferred with string define and class property provides fails' =>
                [
                    'DeferredWithStringVsClassPropertyServiceProvider.php',
                    [
                        'Found unbound class in provides "SomeOtherServiceContract::class"' => null,
                        'Found bound class not in provides "App\Contracts\Services\SomeOtherNameSpace\SomeOtherServiceContract"' => null,
                    ],
                ],
            'Test deferred without define in provides array fails' =>
                [
                    'DeferredWithoutProvidesServiceProvider.php',
                    [
                        'Found bound class not in provides "SomeOtherServiceContract::class"' => null,
                    ],
                ],
            'Test deferrable without define in provides array fails' =>
                [
                    'DeferrableWithoutProvidesServiceProvider.php',
                    [
                        'Found bound class not in provides "SomeOtherServiceContract::class"' => null,
                    ],
                ],
            'Test deferrable with multiple interfaces without define in provides array fails' =>
                [
                    'DeferrableWithoutProvidesMultipleInterfacesServiceProvider.php',
                    [
                        'Found bound class not in provides "SomeOtherServiceContract::class"' => null,
                    ],
                ],
            'Test deferred with all bind methods correctly fails' =>
                [
                    'BindingMethodsServiceProvider.php',
                    [
                        'Found bound class not in provides "BindNameSpaceContract::class"' => null,
                        'Found bound class not in provides "BindIfNameSpaceContract::class"' => null,
                        'Found bound class not in provides "SingletonNameSpaceContract::class"' => null,
                        'Found bound class not in provides "InstanceNameSpaceContract::class"' => null,
                        'Found bound class not in provides "ExtendNameSpaceContract::class"' => null,
                        'Found bound class not in provides "BindMethodNameSpaceContract::class"' => null,
                        'Found bound class not in provides "RefreshNameSpaceContract::class"' => null,
                        'Found bound class not in provides "RebindingNameSpaceContract::class"' => null,
                    ],
                ],
            'Test deferrable bindings property passes' =>
                [
                    'DeferrableWithBindingsServiceProvider.php',
                    [],
                ],
            'Test deferrable bindings property with missing binding fails' =>
                [
                    'DeferrableWithoutBindingsServiceProvider.php',
                    [
                        'Found unbound class in provides "SomeOtherServiceContract::class"' => null,
                    ],
                ],
            'Test deferrable bindings property with extra binding fails' =>
                [
                    'DeferrableWithExtraBindingsServiceProvider.php',
                    [
                        'Found bound class not in provides "SomeOtherServiceContract::class"' => null,
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
