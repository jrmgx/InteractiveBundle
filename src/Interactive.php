<?php

/*
 * This file is part of the InteractiveBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Jrmgx\InteractiveBundle;

use Psy\Output\ShellOutput;

class Interactive
{
    public const MESSAGE_WELCOME = <<<MESSAGE
<fg=black;bg=green;options=bold>This is an enhanced interactive PHP prompt for your Symfony project!</>

You can use those commands to get class instances and services easily:
 - <fg=black;bg=cyan>instance \$i = YourClass</> will give you an instance of the class
 - <fg=black;bg=cyan>service \$s = ServiceName</> will give you the service

If any of those fail, you will have a prompt with a list of entries that could match,
pass *null* to be prompted for all.
MESSAGE;

    public static function outputError(string $message, string $label = 'Error'): void
    {
        $label = mb_strtoupper($label);
        (new ShellOutput())->writeln("\n  <error> $label </error> $message\n");
    }

    public static function outputWarning(string $message, string $label = 'Warning'): void
    {
        $label = mb_strtoupper($label);
        (new ShellOutput())->writeln("\n  <fg=black;bg=yellow> $label </> $message\n");
    }

    public static function outputInfo(string $message, string $label = 'Info'): void
    {
        $label = mb_strtoupper($label);
        (new ShellOutput())->writeln("\n  <info> $label </info> $message\n");
    }

    public static function outputSuccess(string $message, string $label = 'Success'): void
    {
        $label = mb_strtoupper($label);
        (new ShellOutput())->writeln("\n  <fg=black;bg=green> $label </> $message\n");
    }

    /**
     * Given a list of class-string candidates: find identifiers that match.
     * It uses a partial string comparison plus the $match callable.
     * If multiple identifiers match, return all, if none return null.
     * Note: it can also be used for non class-string without much problem.
     *
     * @param array<string> $candidateValues
     *
     * @return ?array<string>
     */
    public static function find(array $candidateValues, ?string $identifier, callable $match): ?array
    {
        $candidateKeys = array_map(fn (string $c) => self::normalize($c), $candidateValues);
        $candidates = array_combine($candidateKeys, $candidateValues);

        if ($identifier) {
            $identifier = self::normalize($identifier);
            $currentCandidates = [];
            $betterCandidates = [];

            foreach ($candidates as $key => $candidate) {
                if (str_contains($key, $identifier)) {
                    $currentCandidates[] = $candidate;
                }

                // Better match
                if (str_ends_with($key, '\\' . $identifier) && $match($candidate)) {
                    $betterCandidates[] = $candidate;
                }
            }

            if (\count($betterCandidates) > 0) {
                $currentCandidates = $betterCandidates;
            }

            if (0 === \count($currentCandidates)) {
                return null;
            }

            if (1 === \count($currentCandidates)) {
                if ($match($currentCandidates[0])) {
                    return [$currentCandidates[0]];
                }

                return null;
            }
        } else {
            $currentCandidates = $candidateValues;
        }

        return $currentCandidates;
    }

    private static function normalize(string $text): string
    {
        return mb_strtolower($text);
    }
}
