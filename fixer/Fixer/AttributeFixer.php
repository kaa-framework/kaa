<?php

/**
 * @noinspection PhpInternalEntityUsedInspection
 * @noinspection UnknownInspectionInspection
 */

declare(strict_types=1);

namespace Kaa\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\Indentation;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;
use SplFileInfo;

class AttributeFixer implements FixerInterface, WhitespacesAwareFixerInterface
{
    use Indentation;

    private WhitespacesFixerConfig $whitespaceConfig;

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        $lastIndent = '';

        foreach ($tokens as $index => $token) {
            if ($this->isNewLineToken($tokens, $index)) {
                $lastIndent = $this->extractIndent($this->computeNewLineContent($tokens, $index));
            }

            if (!$token->isGivenKind(T_ATTRIBUTE)) {
                continue;
            }

            $nextAttributeStart = $index;
            $attributeEnd = $index;
            $attributeContents = [];

            while ($tokens[$nextAttributeStart]->isGivenKind(T_ATTRIBUTE)) {
                $attributeEnd = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $nextAttributeStart);
                $attributeContents[] = $this->spliceTokens($tokens, $nextAttributeStart, $attributeEnd);
                $nextAttributeStart = $tokens->getNextMeaningfulToken($attributeEnd);
            }

            if (count($attributeContents) < 2) {
                continue;
            }

            $attributeBlockStart = $this->createAttributeBlockStart($lastIndent);
            $attributeBlockBody = $this->createAttributeBlockBody($attributeContents, $lastIndent);
            $attributeBlockEnding = $this->createAttributeBlockEnding($lastIndent);

            $tokens->overrideRange(
                $index,
                $attributeEnd,
                [...$attributeBlockStart, ...$attributeBlockBody, ...$attributeBlockEnding],
            );
        }
    }

    /**
     * Возвращает массив токенов, находящихся между $from и $to,
     * и вставляет один отступ после каждого перевода строки
     *
     * @return Token[]
     */
    private function spliceTokens(Tokens $tokens, int $from, int $to): array
    {
        $res = [];
        for ($i = $from + 1; $i < $to; $i++) {
            $res[] = $tokens[$i];

            if ($this->isNewLineToken($tokens, $i)) {
                $res[] = new Token([T_WHITESPACE, $this->whitespaceConfig->getIndent()]);
            }
        }

        return $res;
    }

    /**
     * @return Token[]
     */
    private function createAttributeBlockStart(string $lastIndent): array
    {
        return [
            new Token('#['),
            new Token([T_WHITESPACE, $this->whitespaceConfig->getLineEnding()]),
            new Token([T_WHITESPACE, $lastIndent . $this->whitespaceConfig->getIndent()]),
        ];
    }

    /**
     * @param Token[][] $attributeContents
     * @return Token[]
     */
    private function createAttributeBlockBody(array $attributeContents, string $lastIndent): array
    {
        $body = [];

        foreach ($attributeContents as $attributeContent) {
            $body = [...$body, ...$attributeContent];

            $body[] = new Token(',');
            $body[] = new Token([T_WHITESPACE, $this->whitespaceConfig->getLineEnding()]);
            $body[] = new Token([T_WHITESPACE, $lastIndent . $this->whitespaceConfig->getIndent()]);
        }

        array_pop($body);

        return $body;
    }

    /**
     * @return Token[]
     */
    private function createAttributeBlockEnding(string $lastIndent): array
    {
        $ending = [];

        if ($lastIndent !== '') {
            $ending[] = new Token([T_WHITESPACE, $lastIndent]);
        }

        $ending[] = new Token(']');

        return $ending;
    }

    public function setWhitespacesConfig(WhitespacesFixerConfig $config): void
    {
        $this->whitespaceConfig = $config;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_ATTRIBUTE);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Несколько аттрибутов над одним объектом должны быть внутри одних скобок',
            [
                new CodeSample(
                    <<<'PHP'
#[Attribute]
#[NotSerializable]
private int $a;
)
PHP
                ),
            ],
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
