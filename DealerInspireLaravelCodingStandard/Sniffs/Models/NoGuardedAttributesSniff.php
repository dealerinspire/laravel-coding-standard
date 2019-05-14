<?php
declare(strict_types=1);

namespace DealerInspireLaravelCodingStandard\Sniffs\Models;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * NoGuardedAttributesSniff is a class that detects when a class has a protected $guarded attribute.
 *
 * @package DealerInspireLaravel\Sniffs\Models
 */
class NoGuardedAttributesSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return int[]
     */
    public function register(): array
    {
        return [T_PROTECTED];
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

        $variablePointer = $phpcsFile->findNext([T_VARIABLE], $stackPtr);
        if ($tokens[$variablePointer]['content'] === '$guarded') {
            $phpcsFile->addError('Uses $guarded attributes', $variablePointer, 'GuardedAttributes');
        }
    }
}
