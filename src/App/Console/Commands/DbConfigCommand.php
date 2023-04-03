<?php

namespace Techbeansjp\LaravelDbConfig\App\Console\Commands;

use Illuminate\Console\Command;
use Techbeansjp\LaravelDbConfig\App\Facades\DbConfigFacade;

class DbConfigCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'techbeansjp:get-tables';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'DBテーブル構造をMarkdown形式で出力します。';

  /**
   * Execute the console command.
   */
  public function handle(): void
  {
    // 出力するMarkdown形式のテキスト
    $markdown = '';

    // DbConfigService を使用して、データベース構造の markdown を取得
    $markdown .= DbConfigFacade::getMarkdown();

    // $output を 表示
    $this->info($markdown);
  }
}
