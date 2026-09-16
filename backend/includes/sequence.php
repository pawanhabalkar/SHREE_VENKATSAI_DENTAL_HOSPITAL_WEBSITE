<?php

/*
    CONCURRENCY-SAFE SEQUENTIAL CODE GENERATION
    -----------------------------------------------------
    Used for both patient MRNs ("SVMSDH-000123") and invoice
    numbers ("INV-000123"). Must be called from inside an
    already-open transaction (mysqli_begin_transaction), with
    the INSERT that uses the returned code happening in the
    SAME transaction, right afterward.

    "SELECT ... FOR UPDATE" on the most recent row takes an
    InnoDB row/gap lock, so a second concurrent transaction
    calling this function blocks until the first one commits
    or rolls back — they can no longer both compute the same
    "next" number. The column also has a UNIQUE key in the
    schema as a last-resort safety net: if two numbers ever
    did collide, the INSERT fails cleanly (caller should catch
    that and can retry) instead of silently creating a duplicate.
*/

function generate_sequential_code($conn, $table, $column, $prefix, $numberStartOffset, $padLength = 6) {

    // Row-lock the latest record so a concurrent caller has to wait
    // for this transaction to finish before it can read the max.
    mysqli_query($conn, "SELECT id FROM `$table` ORDER BY id DESC LIMIT 1 FOR UPDATE");

    $sql = "SELECT MAX(CAST(SUBSTRING($column, $numberStartOffset) AS UNSIGNED)) AS max_num FROM `$table`";
    $result = mysqli_query($conn, $sql);
    $maxNum = $result ? (mysqli_fetch_assoc($result)["max_num"] ?? 0) : 0;

    return $prefix . str_pad((string) ($maxNum + 1), $padLength, "0", STR_PAD_LEFT);
}

?>
