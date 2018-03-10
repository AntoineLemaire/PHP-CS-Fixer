<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\Import;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * @author Antoine Lemaire <lemaireantoine@hotmail.com>
 */
final class GroupedImportsFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'Group imports in the same location (same place as the first `use` occurence)',
            [
                new CodeSample(
                    '<?php use Foo;\nuse Bar;\n\nuse function Vendor\Package\Baz;\nuse function Vendor\Package\Qux;\n\nuse const Vendor\Package\QUUX;\nuse const Another\Vendor\CORGE;'
                ),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // should be run after the OrderedImportsFixer
        return -40;
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_USE);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
        $tokensAnalyzer = new TokensAnalyzer($tokens);

        $uses = [];

        $importUseIndexes = $tokensAnalyzer->getImportUseIndexes();
        $firstUseIndex = $importUseIndexes[0];


        foreach ($importUseIndexes as $importUseIndex) {
            $use = [];

            $newLineIndex = $this->getNextNewLine($tokens, $importUseIndex);

            for ($i = $importUseIndex; $i <= $newLineIndex; ++$i) {
                $use[] = $tokens[$i];
            }

            $uses[] = $use;
            $tokens->clearRange($importUseIndex, $newLineIndex);
        }

        $index = $firstUseIndex;
        foreach ($uses as $useTokens) {
            $tokens->insertAt($index, $useTokens);
            $index += count($useTokens);
        }

        $tokens->clearEmptyTokens();
        $tokens->clearChanged();
    }

    /**
     * @param Tokens $tokens
     * @param int    $indexStart
     *
     * @return mixed
     */
    private function getNextNewLine(Tokens $tokens, $indexStart)
    {
        $lastIndex = $tokens->count() - 1;

        $index = $indexStart;

        while (false === strpos($tokens[$index]->getContent(), "\n") && $index <= $lastIndex) {
            ++$index;
        }

        return $index;
    }
}
