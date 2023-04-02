<?php

namespace Techbeansjp\LaravelDatabaseConfiguration\App\Services;

use Illuminate\Support\Facades\Schema;

class DbConfigService
{
    /**
     * データベース内の全てのテーブル名をMarkdown形式で出力します。
     */
    public static function getMarkdown(): string
    {
        // $markdown を初期化
        $markdown = '';

        // データベース名を取得
        $database = Schema::getConnection()->getDatabaseName();

        // データベース名
        $markdown .= sprintf("# DB: %s\n\n", $database);

        // テーブル一覧を取得
        $tables = self::getTables();

        // テーブル一覧
        foreach ($tables as $table) {
            // テーブル
            $markdown .= sprintf("## TBL: %s\n\n", $table);

            // コメントを取得
            $comment = self::getTableComment($table);

            // コメント
            if ($comment) {
                $markdown .= sprintf("%s\n\n", $comment);
            }

            // カラム
            $markdown .= sprintf("### カラム\n\n");

            // カラムヘッダー
            $markdown .= sprintf("| カラム名 | 型 | NULL | 主キー | デフォルト値 | その他 | コメント |\n");
            $markdown .= sprintf("| --- | --- | --- | --- | --- | --- | --- |\n");

            // カラム一覧を取得
            $columns = self::getColumns($table);

            // カラム一覧
            foreach ($columns as $column) {
                // カラム名
                $name = $column->getName();

                // 型
                $type = $column->getType()->getName();

                // 長さ
                $length = $column->getLength();

                if ($length) {
                    $type .= sprintf(" ( %s )", $length);
                }

                // NULL
                $isnull = $column->getNotnull() ? "" : "✓";

                // 主キー
                $primary = "";
                if ($column->getAutoincrement()) {
                    // 列が主キーである場合の処理
                    $primary = "✓";
                }

                // デフォルト値
                $default = $column->getDefault();

                // その他 ( auto_increment, on update CURRENT_TIMESTAMP, ... )
                $extra = [];
                if ($column->getAutoincrement()) {
                    $extra[] = "auto_increment";
                }
                $options = $column->getPlatformOptions();
                if (isset($options["update"])) {
                    $extra[] = "on update CURRENT_TIMESTAMP";
                }
                $extra = implode("<br>", $extra);

                // コメント
                $comment = $column->getComment();

                // カラムを追加
                $markdown .= sprintf("| %s | %s | %s | %s | %s |  %s | %s |\n", $name, $type, $isnull, $primary, $default, $extra, $comment);
            }

            // 改行
            $markdown .= sprintf("\n");

            // インデックス
            $markdown .= sprintf("### インデックス\n\n");

            // インデックスヘッダー
            $markdown .= sprintf("| インデックス名 | カラム名 | 非ユニーク | タイプ |\n");
            $markdown .= sprintf("| --- | --- | --- | --- |\n");

            // インデックス一覧を取得
            $indexes = self::getIndexes($table);

            // インデックス一覧
            foreach ($indexes as $index) {
                // インデックス名
                $name = $index->getName();

                // カラム名
                $column = $index->getColumns()[0];

                // 非ユニーク
                $nonunique = $index->isUnique() ? "" : "✓";

                // タイプ
                $type = $index->isPrimary() ? "PRIMARY" : "INDEX";

                // インデックスを追加
                $markdown .= sprintf("| %s | %s | %s | %s |\n", $name, $column, $nonunique, $type);
            }

            // 改行
            $markdown .= sprintf("\n");

            // 外部キー一覧を取得
            $foreignKeys = self::getForeignKeys($table);

            if ($foreignKeys) {

                // 外部キー
                $markdown .= sprintf("### 外部キー\n");

                // 外部キーヘッダー
                $markdown .= sprintf("| 外部キー名 | カラム名 | 参照テーブル | 参照カラム | ON DELETE | ON UPDATE |\n");
                $markdown .= sprintf("| --- | --- | --- | --- | --- | --- |\n");

                // 外部キー一覧
                foreach ($foreignKeys as $foreignKey) {
                    // オプション
                    $options = $foreignKey->getOptions();

                    // 外部キー名
                    $name = $foreignKey->getName();

                    // カラム名
                    $column = $foreignKey->getLocalColumns()[0];

                    // 参照テーブル
                    $referencedTable = $foreignKey->getForeignTableName();

                    // 参照カラム
                    $referencedColumn = $foreignKey->getForeignColumns()[0];

                    // ON DELETE
                    $onDelete = $options && isset($options["onDelete"]) ? $options["onDelete"] : "";

                    // ON UPDATE
                    $onUpdate = $options && isset($options["onUpdate"]) ? $options["onUpdate"] : "";

                    // 外部キーを追加
                    $markdown .= sprintf("| %s | %s | %s | %s | %s | %s |\n", $name, $column, $referencedTable, $referencedColumn, $onDelete, $onUpdate);
                }

                // 改行
                $markdown .= sprintf("\n");
            }
        }

        return $markdown;
    }

    /**
     * データベース内の全てのテーブル名を取得します。
     */
    private static function getTables(): array
    {
        // EloquentのSchemaファサードを使用して、接続しているデータベースにアクセスします。
        $connection = Schema::getConnection()->getDoctrineConnection();

        // getConnectionメソッドを使用して、接続しているデータベースのインスタンスを取得します。
        $schemaManager = $connection->createSchemaManager();

        // getTableNamesメソッドを使用して、データベース内の全てのテーブル名を取得します。
        $tables = $schemaManager->listTableNames();

        return $tables;
    }

    /**
     * テーブルのカラム情報を取得します。
     */
    private static function getColumns(string $table): array
    {
        // EloquentのSchemaファサードを使用して、接続しているデータベースにアクセスします。
        $connection = Schema::getConnection()->getDoctrineConnection();

        // getConnectionメソッドを使用して、接続しているデータベースのインスタンスを取得します。
        $schemaManager = $connection->createSchemaManager();

        // getTableColumnsメソッドを使用して、テーブルのカラム情報を取得します。
        $columns = $schemaManager->listTableColumns($table);

        return $columns;
    }

    /**
     * テーブルのインデックス情報を取得します。
     */
    private static function getIndexes(string $table): array
    {
        // EloquentのSchemaファサードを使用して、接続しているデータベースにアクセスします。
        $connection = Schema::getConnection()->getDoctrineConnection();

        // getConnectionメソッドを使用して、接続しているデータベースのインスタンスを取得します。
        $schemaManager = $connection->createSchemaManager();

        // getTableIndexesメソッドを使用して、テーブルのインデックス情報を取得します。
        $indexes = $schemaManager->listTableIndexes($table);

        return $indexes;
    }

    /**
     * テーブルの外部キー情報を取得します。
     */
    private static function getForeignKeys(string $table): array
    {
        // EloquentのSchemaファサードを使用して、接続しているデータベースにアクセスします。
        $connection = Schema::getConnection()->getDoctrineConnection();

        // getConnectionメソッドを使用して、接続しているデータベースのインスタンスを取得します。
        $schemaManager = $connection->createSchemaManager();

        // getTableForeignKeysメソッドを使用して、テーブルの外部キー情報を取得します。
        $foreignKeys = $schemaManager->listTableForeignKeys($table);

        return $foreignKeys;
    }

    /**
     * テーブルのコメント情報を取得します。
     */
    private static function getTableComment(string $table): string
    {
        // EloquentのSchemaファサードを使用して、接続しているデータベースにアクセスします。
        $connection = Schema::getConnection()->getDoctrineConnection();

        // getConnectionメソッドを使用して、接続しているデータベースのインスタンスを取得します。
        $schemaManager = $connection->createSchemaManager();

        // getTableメソッドを使用して、テーブルのコメント情報を取得します。
        $table = $schemaManager->introspectTable($table);

        // getCommentメソッドを使用して、テーブルのコメント情報を取得します。
        $comment = $table->getComment();

        return $comment ?? '';
    }
}
