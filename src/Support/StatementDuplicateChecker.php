<?php

namespace RonasIT\Larabuilder\Support;

use Illuminate\Support\Arr;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinter\Standard;
use RonasIT\Larabuilder\Enums\StatementAttributeEnum;

class StatementDuplicateChecker
{
    public function isDuplicated(array $existingStatements, array $statementsToCheck): bool
    {
        if (empty($existingStatements) || empty($statementsToCheck)) {
            return false;
        }

        $haystack = $this->normalizeStatements($existingStatements);
        $needle = $this->normalizeStatements($statementsToCheck);

        return $this->isSubsequence($haystack, $needle);
    }

    protected function normalizeStatements(array $statements): array
    {
        $printer = new Standard();

        return Arr::map($statements, function (Stmt $statement) use ($printer) {
            $stmtCopy = clone $statement;

            $stmtCopy->setAttribute(StatementAttributeEnum::Comments->value, []);

            return $printer->prettyPrint([$stmtCopy]);
        });
    }

    protected function isSubsequence(array $haystackStatements, array $needleStatements): bool
    {
        $needleCount = count($needleStatements);
        $haystackCount = count($haystackStatements);

        for ($i = 0; $i <= $haystackCount - $needleCount; $i++) {
            if (array_slice($haystackStatements, $i, $needleCount) === $needleStatements) {
                return true;
            }
        }

        return false;
    }
}
