<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\BacktickToShellExecFixer;
use PhpCsFixer\Fixer\Alias\NoAliasLanguageConstructCallFixer;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer;
use PhpCsFixer\Fixer\Casing\IntegerLiteralCaseFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentSpacingFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ControlStructure\EmptyLoopBodyFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\SwitchContinueToBreakFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoUnneededImportAliasFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareParenthesesFixer;
use PhpCsFixer\Fixer\NamespaceNotation\CleanNamespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\IncrementStyleFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\StringNotation\NoBinaryStringFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use PhpCsFixerCustomFixers\Fixer\CommentSurroundedBySpacesFixer;
use PhpCsFixerCustomFixers\Fixer\IssetToArrayKeyExistsFixer;
use PhpCsFixerCustomFixers\Fixer\MultilineCommentOpeningClosingAloneFixer;
use PhpCsFixerCustomFixers\Fixer\NoDoctrineMigrationsGeneratedCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoDuplicatedImportsFixer;
use PhpCsFixerCustomFixers\Fixer\NoLeadingSlashInGlobalNamespaceFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypesCommaSpacesFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypesTrimFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceAfterStatementFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceBeforeStatementFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/../src',
        __DIR__ . '/../fixer',
    ]);

    $ecsConfig->sets([
        SetList::COMMON,
        SetList::PSR_12,
    ]);

    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, [
        'elements' => ['const' => 'none'],
    ]);

    $ecsConfig->ruleWithConfiguration(BlankLineBeforeStatementFixer::class, [
        'statements' => ['return', 'case'],
    ]);

    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, [
        'elements' => ['method' => 'one'],
    ]);

    $ecsConfig->ruleWithConfiguration(ClassDefinitionFixer::class, [
        'single_line' => true,
    ]);

    $ecsConfig->ruleWithConfiguration(ConcatSpaceFixer::class, [
        'spacing' => 'one',
    ]);

    $ecsConfig->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
        'import_functions' => true,
    ]);

    $ecsConfig->ruleWithConfiguration(IncrementStyleFixer::class, [
        'style' => 'post',
    ]);

    $ecsConfig->ruleWithConfiguration(NoUnneededCurlyBracesFixer::class, [
        'namespaces' => true,
    ]);

    $ecsConfig->ruleWithConfiguration(NoUnneededControlParenthesesFixer::class, [
        'statements' => [
            'break',
            'clone',
            'continue',
            'echo_print',
            'others',
            'return',
            'switch_case',
            'yield',
            'yield_from',
        ],
    ]);

    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'imports_order' => ['class', 'function', 'const'],
        'sort_algorithm' => 'alpha',
    ]);

    $ecsConfig->ruleWithConfiguration(SingleLineCommentStyleFixer::class, [
        'comment_types' => ['hash'],
    ]);

    $ecsConfig->rules([
        CommentSurroundedBySpacesFixer::class,
        IssetToArrayKeyExistsFixer::class,
        MultilineCommentOpeningClosingAloneFixer::class,
        NoDoctrineMigrationsGeneratedCommentFixer::class,
        NoDuplicatedImportsFixer::class,
        NoLeadingSlashInGlobalNamespaceFixer::class,
        PhpdocSingleLineVarFixer::class,
        PhpdocTypesCommaSpacesFixer::class,
        PhpdocTypesTrimFixer::class,
        SingleSpaceAfterStatementFixer::class,
        SingleSpaceBeforeStatementFixer::class,
        AlignMultilineCommentFixer::class,
        ArraySyntaxFixer::class,
        BacktickToShellExecFixer::class,
        BinaryOperatorSpacesFixer::class,
        CastSpacesFixer::class,
        ClassReferenceNameCasingFixer::class,
        CleanNamespaceFixer::class,
        DeclareParenthesesFixer::class,
        EmptyLoopBodyFixer::class,
        FullyQualifiedStrictTypesFixer::class,
        IncludeFixer::class,
        IntegerLiteralCaseFixer::class,
        MagicConstantCasingFixer::class,
        MagicMethodCasingFixer::class,
        LinebreakAfterOpeningTagFixer::class,
        NativeFunctionCasingFixer::class,
        NativeFunctionTypeDeclarationCasingFixer::class,
        NoAliasLanguageConstructCallFixer::class,
        NoBinaryStringFixer::class,
        NoBlankLinesAfterPhpdocFixer::class,
        NoEmptyCommentFixer::class,
        NoEmptyPhpdocFixer::class,
        NoEmptyStatementFixer::class,
        NoLeadingNamespaceWhitespaceFixer::class,
        NoMixedEchoPrintFixer::class,
        NoShortBoolCastFixer::class,
        NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
        NoSpacesAroundOffsetFixer::class,
        NoTrailingCommaInSinglelineFixer::class,
        NoUnneededImportAliasFixer::class,
        NoUnusedImportsFixer::class,
        NoWhitespaceBeforeCommaInArrayFixer::class,
        NormalizeIndexBraceFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        PhpdocAnnotationWithoutDotFixer::class,
        PhpdocVarWithoutNameFixer::class,
        SingleLineCommentSpacingFixer::class,
        SingleQuoteFixer::class,
        StandardizeNotEqualsFixer::class,
        SwitchContinueToBreakFixer::class,
        TrimArraySpacesFixer::class,
        TypeDeclarationSpacesFixer::class,
        TypesSpacesFixer::class,
        UnaryOperatorSpacesFixer::class,

        Kaa\Fixer\AttributeFixer::class,
        Kaa\Fixer\ThrowsTagFixer::class,
    ]);

    $ecsConfig->skip([
        NotOperatorWithSuccessorSpaceFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        MethodArgumentSpaceFixer::class,
        OrderedClassElementsFixer::class,
        TrailingCommaInMultilineFixer::class,
    ]);
};
