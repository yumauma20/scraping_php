<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScrapeMynavi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mynavi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Mynavi';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $url = "https://tenshoku.mynavi.jp/list/pg3/";
      $crawler = \Goutte::request('GET', $url);
      $urls = $crawler->filter('.cassetteRecruit__copy > a')->each(function ($node) {

        $href = $node->attr("href");
        return [
          "url" => substr($href, 0, strpos($href, "/", 1) + 1),
          "created_at" => Carbon::now(),
          "updated_at" => Carbon::now(),
        ];

      });

      DB::table('mynavi_urls')->insert($urls);
    }

  /*   private function saveUrls() {

    } */
}
