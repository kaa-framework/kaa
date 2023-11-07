<?php

declare(strict_types=1);

namespace Kaa\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

class ThrowsTagFixer implements FixerInterface
{
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                continue;
            }

            $comment = $token->getContent();

            if (!str_contains($comment, '@throws')) {
                continue;
            }

            // Если после @throws стоит описание ошибки
            if (preg_match('/@throws \w* \w*/', $comment) === 1) {
                continue;
            }

            preg_match_all('/@throws (?<exception_class>(\w|\|)*)/', $comment, $matches);

            $exceptions = '@throws ' . implode('|', $matches['exception_class']);
            $comment = preg_replace('/(@throws (\w|\|)*\s+\*\s)+@throws \w*/', $exceptions, $comment);

            $tokens[$index] = new Token([T_DOC_COMMENT, $comment]);
        }
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Соединяет несколько блоков @throws в один',
            [
                new CodeSample(
                    <<<'PHP'
/**
 * @thows AException
 * @thows BException
 */
private function a() {
}
)
PHP
                ),
            ]
        );
    }

    public function getPriority(): int
    {
        return -10;
    }

    public function getName(): string
    {
        return self::class;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }
}
