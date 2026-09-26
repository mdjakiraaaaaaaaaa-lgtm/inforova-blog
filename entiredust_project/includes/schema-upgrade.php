<?php
declare(strict_types=1);

/*
 * Self-healing schema: adds columns that older installs do not have yet.
 * Runs once per request (cheap SHOW COLUMNS check), no manual SQL needed.
 */

function ensureInforovaSchema(PDO $pdo): void
{
    static $done = false;

    if ($done) {
        return;
    }

    try {
        if (!$pdo->query("SHOW COLUMNS FROM articles LIKE 'thumbnail_url'")->fetch()) {
            $pdo->exec("ALTER TABLE articles ADD COLUMN thumbnail_url VARCHAR(500) NOT NULL DEFAULT '' AFTER content_html");
        }
    } catch (Throwable $e) {
        error_log('ensureInforovaSchema (thumbnail_url) failed: ' . $e->getMessage());
    }

    try {
        if (!$pdo->query("SHOW COLUMNS FROM categories LIKE 'icon'")->fetch()) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN icon VARCHAR(10) NOT NULL DEFAULT '' AFTER slug");
        }
    } catch (Throwable $e) {
        error_log('ensureInforovaSchema (icon) failed: ' . $e->getMessage());
    }

    try {
        if (!$pdo->query("SHOW COLUMNS FROM categories LIKE 'description'")->fetch()) {
            $pdo->exec("ALTER TABLE categories ADD COLUMN description VARCHAR(500) NOT NULL DEFAULT ''");
        } else {
            // Older rows may have a NULL description even though the column exists.
            $pdo->exec("UPDATE categories SET description='' WHERE description IS NULL");
        }
    } catch (Throwable $e) {
        error_log('ensureInforovaSchema (category description) failed: ' . $e->getMessage());
    }

    $done = true;
}
