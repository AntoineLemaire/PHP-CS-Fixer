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
        $lastIndex = $tokens->count() - 1;

        $importUseIndexes = $tokensAnalyzer->getImportUseIndexes();
        $firstUseIndex = $importUseIndexes[0];

        foreach ($importUseIndexes as $index) {
            $use = [];
            $i = $index - 1;

            do {
                ++$i;

                $use[] = $tokens[$i];
                $lastToken = $tokens[$i];

                if ($i <= $lastIndex) {
                    $tokens->clearAt($i);
                }
            } while ((bool) false === strpos($lastToken->getContent(), "\n") && $i < $lastIndex);
            $uses[] = $use;
        }

        $index = $firstUseIndex;
        foreach ($uses as $useTokens) {
            foreach ($useTokens as $token) {
                $tokens->insertAt($index, $token);
                ++$index;
            }
        }

        $tokens->clearEmptyTokens();
        $tokens->clearChanged();
    }
}
