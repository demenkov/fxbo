<?php

declare(strict_types=1);

namespace FXBO\Parser;

use Traversable;

interface ParserInterface
{

    /**
     * @return Traversable [
     *     'date' => (string) Quote date,
     *     'base' => (string) Base currency,
     *     'quote' => (string) Quote currency,
     *     'price' => (string) Price,
     *     'price' => (string) Provider,
     * ];
     */
    public function parse(): Traversable;
}
