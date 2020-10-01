<?php
declare(strict_types=1);

namespace DealerInspireLaravelCodingStandard\Sniffs\Providers;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * DeferredProvidersSniff is a class that detects missing provides in deferred Laravel providers
 *
 * DeferredProvidersSniff is a class that checks for any classes bound in a deferred Laravel service provider's register method,
 * that isn't included in that service provider's provides method.
 *
 * This class assumes some things about the code it's sniffing:
 * - The provides method simply returns an array of classes that are provided (no method calls or other overly clever shenanigans)
 * - The register method and provides method refer to the class in the same way i.e. both either use string literals or ::class magic constants
 * - All of your service providers extend a class called ServiceProvider (like in the default Laravel setup)
 * - The $defer property is set to true or the DeferrableProvider interface is implemented. It won't check in parent classes.
 *
 * @package DealerInspireLaravel\Sniffs\Providers
 */
class DeferredProvidersSniff implements Sniff
{
    /**
     * @var bool A variable indicating whether the token being looped over in the process method is within a bind() or singleton() call
     */
    protected $inBindOrSingleton = false;

    /**
     * @var bool A variable indicating whether the token being looped over in process is within the provides method
     */
    protected $inProvides = false;

    /**
     * @var bool Indicates whether the token being looped over in process is within the array being returned by the provides method
     */
    protected $inProvidesReturnArray = false;

    /**
     * @var array Method names from Illuminate/Contracts/Container/Container.php that bind classes to the container
     */
    protected static $bindingMethods = ['bind', 'bindIf', 'singleton', 'instance', 'extend', 'bindMethod', 'refresh', 'rebinding'];

    /**
     * @var array Classes that have been bound in the Service Providers register method
     */
    protected $boundClasses = [];

    /**
     * @var array Classes that have been returned from the Service Providers provides method
     */
    protected $providesClasses = [];

    /**
     * @var int Indicates how many curly brackets we've encountered in the provides method that have not yet been closed
     */
    protected $providesOpenCurlyBrackets = 0;

    /**
     * @var bool Indicates if we've encountered the extends keyword
     */
    protected $checkingForServiceProvider = false;

    /**
     * @var bool Indicates if we've encountered the $defer variable
     */
    protected $checkingForDeferredValue = false;

    /**
     * @var bool Indicates if we've encountered the implements keyword
     */
    protected $checkingForDeferrableProvider = false;

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return int[]
     */
    public function register(): array
    {
        // T_OPEN_TAG is the opening PHP Tag for a file, and should cause our sniff to be hit once for every file
        return [T_OPEN_TAG];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param  File $phpcsFile The current file being checked.
     * @param  mixed $stackPtr  The position of the current token in the stack passed in $tokens. Type of mixed due to the interface not enforcing int.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $isServiceProvider = null;
        $isDeferred = null;
        foreach ($tokens as $index => $token) {
            if (is_null($isServiceProvider)) {
                $isServiceProvider = $this->isServiceProvider($index, $tokens);
            }

            if (is_null($isDeferred)) {
                $isDeferred = $this->isDeferred($index, $tokens);
            }

            if ($isServiceProvider === false || $isDeferred === false) {
                break;
            }

            $this->handleBind($index, $tokens);
            $this->handleBindingsProperty($index, $tokens);
            $this->handleProvides($index, $tokens);
        }

        if ($isServiceProvider && $isDeferred) {
            $this->handleErrors($phpcsFile);
        }
        $this->reset();
    }

    /**
     * Returns true if the class is a Service Provider, false if it's not, and null if the given $index doesn't determine provider status
     *
     * @param int $index
     * @param array $tokens
     * @return bool|null
     */
    protected function isServiceProvider(int $index, array $tokens): ?bool
    {
        if ($tokens[$index]['code'] === T_EXTENDS) {
            $this->checkingForServiceProvider = true;
            return null;
        }

        if ($this->checkingForServiceProvider) {
            if ($tokens[$index]['code'] === T_OPEN_CURLY_BRACKET || $tokens[$index]['code'] === T_IMPLEMENTS) {
                $this->checkingForServiceProvider = false;
                return false;
            }

            if ($tokens[$index]['code'] === T_STRING && $tokens[$index]['content'] === 'ServiceProvider') {
                $this->checkingForServiceProvider = false;
                return true;
            }
        }

        return null;
    }

    /**
     * Returns true if the Service Provider is deferred, false if it's not, and null if the given $index doesn't determine deferred status
     *
     * @param int $index
     * @param array $tokens
     * @return bool|null
     */
    protected function isDeferred(int $index, array $tokens): ?bool
    {
        if ($tokens[$index]['code'] === T_IMPLEMENTS) {
            $this->checkingForDeferrableProvider = true;
            return null;
        }

        if ($tokens[$index]['code'] === T_VARIABLE && $tokens[$index]['content'] === '$defer') {
            $this->checkingForDeferredValue = true;
            return null;
        }

        if ($this->checkingForDeferrableProvider) {
            if ($tokens[$index]['code'] === T_OPEN_CURLY_BRACKET) {
                $this->checkingForDeferrableProvider = false;
                return null;
            }
            if ($tokens[$index]['code'] === T_STRING && $tokens[$index]['content'] === 'DeferrableProvider') {
                $this->checkingForDeferrableProvider = false;
                return true;
            }
        }

        if ($this->checkingForDeferredValue) {
            // The first T_TRUE or T_FALSE after '$defer' will be the value
            if ($tokens[$index]['code'] === T_TRUE) {
                $this->checkingForDeferredValue = false;
                return true;
            } else if ($tokens[$index]['code'] === T_FALSE) {
                $this->checkingForDeferredValue = false;
                return false;
            }
        }

        return null;
    }

    protected function handleBindingsProperty(int $index, array $tokens): void
    {
        if ($tokens[$index]['code'] !== T_PUBLIC) {
            return;
        }

        $bindingsIndex = $index + 2;

        if (empty($tokens[$bindingsIndex])) {
            return;
        }

        if ($tokens[$bindingsIndex]['code'] !== T_VARIABLE) {
            return;
        }

        if ($tokens[$bindingsIndex]['content'] !== '$bindings') {
            return;
        }


        $this->extractServicesFromBindingsProperty($index, $tokens);
    }

    protected function extractServicesFromBindingsProperty(int $index, array $tokens): void
    {
        while ($tokens[$index]['code'] !== T_CLOSE_SHORT_ARRAY) {
            $className = $this->getClassNameForIndex($index, $tokens);

            if ($className && $this->isClassNameArrayIndex($index, $tokens)) {
                $this->boundClasses[$index] = trim($className, "'");
            }

            $index++;
        }
    }

    protected function isClassNameArrayIndex(int $index, array $tokens): bool
    {
        return T_DOUBLE_ARROW === ($tokens[$index + 4]['code'] ?? null);
    }

    /**
     * Handles determining if the current index is within a bind(), singleton(), etc call, and then detects bound classes as appropriate
     *
     * @param  int   $index
     * @param  array $tokens
     * @return void
     */
    protected function handleBind(int $index, array $tokens): void
    {
        if (in_array($tokens[$index]['content'], self::$bindingMethods)) {
            // We want to start checking for a class name right after we hit a string (assumed to be a function name) that's in our list of binding methods
            $this->inBindOrSingleton = true;
        }

        if ($this->inBindOrSingleton === false) {
            return;
        }

        if ($tokens[$index]['code'] === T_COMMA) {
            // If we haven't encountered the class name by the first comma, it's no longer what we're looking for
            $this->inBindOrSingleton = false;
        } else if ($classNameForIndex = $this->getClassNameForIndex($index, $tokens)) {
            $this->boundClasses[$index] = trim($classNameForIndex, '\'');
            $this->inBindOrSingleton = false;
        }
    }

    /**
     * Handles determining if the current index is within the provides method and then detects provided classes as appropriate
     *
     * @param  int   $index
     * @param  array $tokens
     * @return void
     */
    protected function handleProvides(int $index, array $tokens): void
    {
        if ($tokens[$index]['content'] === 'provides') {
            $this->inProvides = true;
        }

        if ($this->inProvides === false) {
            return;
        }

        if ($this->inProvidesReturnArray && $classNameForIndex = $this->getClassNameForIndex($index, $tokens)) {
            $this->providesClasses[$index] = trim($classNameForIndex, '\'');
        }

        switch ($tokens[$index]['code']) {
            case T_OPEN_CURLY_BRACKET:
                $this->providesOpenCurlyBrackets++;
                break;
            case T_CLOSE_CURLY_BRACKET:
                $this->providesOpenCurlyBrackets--;
                if ($this->providesOpenCurlyBrackets === 0) {
                    $this->inProvides = false;
                }
                break;
            case T_OPEN_SHORT_ARRAY:
            case T_ARRAY:
                $this->inProvidesReturnArray = true;
                break;
            case T_CLOSE_SHORT_ARRAY:
            case T_CLOSE_PARENTHESIS:
                $this->inProvidesReturnArray = false;
                break;
        }
    }

    /**
     * Finds a classname (whether it's a string or a magic ::class constant), if one exists, at the given index, or returns null if one isn't found
     *
     * @param  int   $index
     * @param  array $tokens
     * @return string|null
     */
    protected function getClassNameForIndex(int $index, array $tokens): ?string
    {
        if ($tokens[$index]['code'] === T_STRING && $tokens[$index + 1]['code'] === T_DOUBLE_COLON) {
            // This if will get hit when using the ::class magic constant Ex) bind(ClassContractName::class, ClassName::class)
            // Current index is i.e ClassContractName, next index is ::, index after that is class = ClassContractName::class
            return $tokens[$index]['content'] . $tokens[$index + 1]['content'] . $tokens[$index + 2]['content'];
        } else if ($tokens[$index]['code'] === T_CONSTANT_ENCAPSED_STRING) {
            // This if will get hit when using a regular string Ex) bind('App\Contracts\Services\ClassContractName', 'App\Services\Name')
            // Current index is i.e. 'App\Contracts\Services\ClassContractName'
            return $tokens[$index]['content'];
        }
        return null;
    }

    /**
     * Handles adding any errors to the output of PHP CS
     *
     * @param  File $phpcsFile
     * @return void
     */
    protected function handleErrors(File $phpcsFile): void
    {
        $inProvidesOnly = array_diff($this->providesClasses, $this->boundClasses);
        $boundOnly = array_diff($this->boundClasses, $this->providesClasses);
        if (!empty($inProvidesOnly)) {
            foreach ($inProvidesOnly as $location => $missingBind) {
                $phpcsFile->addError('Found unbound class in provides "' . $missingBind . '"', $location, 'Found', $inProvidesOnly);
            }
        }
        if (!empty($boundOnly)) {
            foreach ($boundOnly as $location => $missingProvide) {
                $phpcsFile->addError('Found bound class not in provides "' . $missingProvide . '"', $location, 'Found', $boundOnly);
            }
        }
    }

    /**
     * PHP CS instantiates the rule once and then calls it multiple times.
     * This method resets all of our variables to the default so classes from different providers don't end up getting mixed together.
     * Most importantly, this prevents errors in one provider from carrying over to another provider.

     * @return void
     */
    protected function reset(): void
    {
        $this->inBindOrSingleton = false;
        $this->inProvides = false;
        $this->inProvidesReturnArray = false;
        $this->boundClasses = [];
        $this->providesClasses = [];
        $this->providesOpenCurlyBrackets = 0;
        $this->checkingForDeferredValue = false;
        $this->isDeferred = false;
    }
}
