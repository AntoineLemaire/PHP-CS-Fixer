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

namespace PhpCsFixer\Tests\Fixer\Import;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Your name <your@email.com>
 *
 * @internal
 * @coversNothing
 */
final class GroupedImportsFixerTest extends AbstractFixerTestCase
{
    /**
     * @param string      $expected
     * @param null|string $input
     *
     * @dataProvider provideFix
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideFix()
    {
        return [
            ['<?php

use Foo;
use Bar; // Comment
use function Vendor\Package\Baz;
use function Vendor\Package\Qux; /* Other comment */
use const Vendor\Package\QUUX;
use const Another\Vendor\CORGE;

class A { public function B(){ return "foo"; } };
'], ['<?php

use Foo;
use function Vendor\Package\Baz;
use Bar; // Comment
use const Another\Vendor\CORGE;
use function Vendor\Package\Qux; /* Other comment */
use const Vendor\Package\QUUX;

class A { public function B(){ return "foo"; } };
'], ['<?php

use Foo;
use function Vendor\Package\Baz;

use Bar; // Comment
use const Another\Vendor\CORGE;

use function Vendor\Package\Qux; /* Other comment */
use const Vendor\Package\QUUX;

class A { public function B(){ return "foo"; } };
'], ['<?php

use Foo;
use function Vendor\Package\Baz;
use Bar; // Comment
use const Another\Vendor\CORGE;
use function Vendor\Package\Qux; /* Other comment */
use const Vendor\Package\QUUX;

class A { use C; public function B(){ return "foo"; } };
'],
        ];
    }
}
